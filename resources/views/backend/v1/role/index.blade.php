@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Manajemen Role',
        'breadcrumbs' => [['title' => 'Home', 'url' => route('dashboard')], ['title' => 'Role', 'active' => true]],
    ])
    @endcomponent
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Daftar Role</h3>
                    <div class="card-tools">
                        {{-- Tombol Tambah Role sekarang memicu modal --}}
                        <button type="button" class="btn btn-primary btn-sm" id="addRoleButton">
                            <i class="fas fa-plus"></i> Tambah Role
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Filter dan Search (Opsional) --}}
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="perPage">Item per Halaman:</label>
                                <select id="perPage" class="form-control form-control-sm">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="searchQuery">Cari Role:</label>
                                <input type="text" id="searchQuery" class="form-control form-control-sm" placeholder="Masukkan nama role...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                 <button id="searchButton" class="btn btn-info btn-sm">Cari</button>
                            </div>
                        </div>
                    </div>
                    <hr class="m-0">

                    <div id="rolesTableContainer">
                        {{-- Tabel akan di-render oleh JavaScript di sini --}}
                        <div class="p-5 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Memuat data...</span>
                            </div>
                            <p class="mt-2">Memuat data role...</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div id="paginationLinks" class="float-end">
                        {{-- Link pagination akan di-render oleh JavaScript di sini --}}
                    </div>
                    <div id="paginationInfo" class="float-start pt-1">
                        {{-- Info pagination (Showing X to Y of Z entries) akan di-render di sini --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modal Form Role --}}
    @include('backend.v1.role.modal_role_form')

    {{-- Modal untuk Konfirmasi Hapus --}}
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRoleModalLabel">Konfirmasi Hapus Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus role <strong id="roleNameToDelete"></strong>? Tindakan ini tidak dapat diurungkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    .pagination .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    /* Style untuk error di modal */
    #roleFormErrors li {
        list-style-type: disc;
        margin-left: 20px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Elemen DOM Utama ---
    const rolesTableContainer = document.getElementById('rolesTableContainer');
    const paginationLinksContainer = document.getElementById('paginationLinks');
    const paginationInfoContainer = document.getElementById('paginationInfo');
    const perPageSelect = document.getElementById('perPage');
    const searchQueryInput = document.getElementById('searchQuery');
    const searchButton = document.getElementById('searchButton');
    const addRoleButton = document.getElementById('addRoleButton');

    // --- Elemen DOM Modal Form Role ---
    const roleFormModalElement = document.getElementById('roleFormModal'); // Elemen modal dari komponen
    const roleFormModal = new bootstrap.Modal(roleFormModalElement); // Inisialisasi Bootstrap Modal
    const roleForm = document.getElementById('roleForm'); // Form di dalam modal
    const roleFormModalLabel = document.getElementById('roleFormModalLabel'); // Judul modal
    const roleIdInput = document.getElementById('roleId'); // Hidden input untuk ID role (saat edit)
    const roleNameInput = document.getElementById('roleName'); // Input nama role
    const saveRoleButton = document.getElementById('saveRoleButton'); // Tombol simpan di modal
    const roleFormAlertContainer = document.getElementById('roleFormAlertContainer'); // Kontainer alert di modal
    const roleFormErrorsList = document.getElementById('roleFormErrors'); // List untuk error di modal

    // --- Elemen DOM Modal Delete ---
    const deleteRoleModalElement = document.getElementById('deleteRoleModal');
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    const roleNameToDeleteElement = document.getElementById('roleNameToDelete');

    // --- Variabel State ---
    let currentPage = 1;
    let itemsPerPage = parseInt(perPageSelect.value);
    let currentSearchQuery = '';
    let roleToDeleteId = null; // Untuk menyimpan ID role yang akan dihapus
    let currentEditRoleId = null; // Untuk menandai apakah mode edit atau tambah di modal form

    // --- Fungsi Utama untuk Mengambil dan Menampilkan Role ---
    async function fetchAndDisplayRoles(page = 1, perPage = 10, search = '') {
        currentPage = page;
        itemsPerPage = perPage;
        currentSearchQuery = search;

        showLoading(); // Tampilkan indikator loading

        // Build URL API with pagination and search parameters
        const apiUrl = `/api/v1/roles?page=${currentPage}&per_page=${itemsPerPage}&search=${encodeURIComponent(currentSearchQuery)}`;
        // const apiToken = 'YOUR_API_TOKEN_HERE'; // Ganti dengan token API Anda jika diperlukan

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    // 'Authorization': `Bearer ${apiToken}`, // Uncomment jika API memerlukan token
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') // Untuk proteksi CSRF jika API dipanggil dari domain sama
                }
            });

            if (!response.ok) { // Jika respons tidak OK (misal 404, 500)
                const errorData = await response.json().catch(() => ({ message: 'Gagal mengambil data role. Status: ' + response.status }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const result = await response.json(); // Parse respons JSON

            if (result.success && result.data) {
                renderTable(result.data.data); // Render tabel dengan data role (result.data.data adalah array of roles)
                renderPagination(result.data); // Render link pagination (result.data adalah objek pagination Laravel)
                renderPaginationInfo(result.data); // Render info pagination
            } else {
                showError(result.message || 'Data role tidak ditemukan atau format respons tidak sesuai.');
            }
        } catch (error) {
            console.error('Error fetching roles:', error);
            showError('Terjadi kesalahan saat mengambil data role: ' + error.message);
        }
    }

    // --- Fungsi UI Helper ---
    function showLoading() {
        rolesTableContainer.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Memuat...</span>
                </div>
                <p class="mt-2">Memuat data role...</p>
            </div>`;
        paginationLinksContainer.innerHTML = '';
        paginationInfoContainer.innerHTML = '';
    }

    function showError(message, container = rolesTableContainer) {
        container.innerHTML = `<div class="alert alert-danger m-3">${message}</div>`;
    }

    // --- Fungsi Render Tabel ---
    function renderTable(roles) {
        if (!roles || roles.length === 0) {
            rolesTableContainer.innerHTML = '<div class="p-3 text-center"><p>Belum ada data role.</p></div>';
            return;
        }

        let tableHtml = `
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama Role</th>
                        <th>Dibuat Pada</th>
                        <th style="width: 180px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>`;

        roles.forEach((role, index) => {
            const itemNumber = (currentPage - 1) * itemsPerPage + index + 1; // Hitung nomor urut
            tableHtml += `
                <tr>
                    <td>${itemNumber}.</td>
                    <td>${escapeHtml(role.name)}</td>
                    <td>${formatDate(role.created_at)}</td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-warning me-1 editRoleButton" title="Edit"
                                data-role-id="${role.id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger deleteRoleButton" title="Hapus"
                                data-role-id="${role.id}" data-role-name="${escapeHtml(role.name)}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>`;
        });

        tableHtml += `</tbody></table>`;
        rolesTableContainer.innerHTML = tableHtml;
        addTableActionListeners(); // Tambahkan event listener ke tombol aksi di tabel yang baru di-render
    }

    // --- Fungsi untuk Menambahkan Event Listener ke Tombol Aksi di Tabel ---
    function addTableActionListeners() {
        // Event listener untuk tombol Edit
        document.querySelectorAll('.editRoleButton').forEach(button => {
            button.addEventListener('click', function() {
                const roleId = this.getAttribute('data-role-id');
                openRoleFormModal('edit', roleId);
            });
        });

        // Event listener untuk tombol Hapus
        document.querySelectorAll('.deleteRoleButton').forEach(button => {
            button.addEventListener('click', function() {
                roleToDeleteId = this.getAttribute('data-role-id'); // Simpan ID role yang akan dihapus
                const roleName = this.getAttribute('data-role-name');
                roleNameToDeleteElement.textContent = roleName; // Tampilkan nama role di modal konfirmasi
                const deleteModal = new bootstrap.Modal(deleteRoleModalElement); // Inisialisasi & tampilkan modal delete
                deleteModal.show();
            });
        });
    }

    // --- Fungsi Render Pagination ---
    function renderPagination(paginationData) {
        paginationLinksContainer.innerHTML = '';
        if (!paginationData || !paginationData.links || paginationData.links.length === 0) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm m-0';

        paginationData.links.forEach(link => {
            const li = document.createElement('li');
            li.className = `page-item ${link.active ? 'active' : ''} ${link.url === null ? 'disabled' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.innerHTML = link.label; // Label dari Laravel sudah di-escape (e.g., &laquo; Previous)
            if (link.url) {
                a.href = '#'; // Cegah navigasi default
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (link.url) {
                        const urlParams = new URL(link.url).searchParams; // Dapatkan parameter dari URL link pagination
                        const page = urlParams.get('page');
                        if (page) fetchAndDisplayRoles(parseInt(page), itemsPerPage, currentSearchQuery);
                    }
                });
            }
            li.appendChild(a);
            ul.appendChild(li);
        });
        paginationLinksContainer.appendChild(ul);
    }

    // --- Fungsi Render Info Pagination ---
    function renderPaginationInfo(paginationData) {
        if (!paginationData || paginationData.total === 0) {
            paginationInfoContainer.innerHTML = 'Tidak ada data.';
            return;
        }
        const from = paginationData.from || 0;
        const to = paginationData.to || 0;
        const total = paginationData.total || 0;
        paginationInfoContainer.innerHTML = `Menampilkan ${from} sampai ${to} dari ${total} data.`;
    }

    // --- Fungsi Utilitas ---
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        try {
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        } catch (e) { return dateString; }
    }

    // --- Event Listener untuk Kontrol Filter & Search ---
    perPageSelect.addEventListener('change', function() { fetchAndDisplayRoles(1, parseInt(this.value), currentSearchQuery); });
    searchButton.addEventListener('click', function() { fetchAndDisplayRoles(1, itemsPerPage, searchQueryInput.value.trim()); });
    searchQueryInput.addEventListener('keypress', function(event) { if (event.key === 'Enter') { event.preventDefault(); searchButton.click(); } });

    // --- Logika untuk Modal Form Role (Tambah/Edit) ---
    addRoleButton.addEventListener('click', function() {
        openRoleFormModal('add'); // Buka modal dalam mode 'tambah'
    });

    function resetRoleForm() {
        roleForm.reset(); // Reset nilai input form
        roleIdInput.value = ''; // Kosongkan hidden input ID
        currentEditRoleId = null; // Reset status mode edit
        roleFormAlertContainer.style.display = 'none'; // Sembunyikan alert error
        roleFormErrorsList.innerHTML = ''; // Kosongkan list error
        saveRoleButton.disabled = false; // Aktifkan tombol simpan
        saveRoleButton.querySelector('.spinner-border').style.display = 'none'; // Sembunyikan spinner
    }

    async function openRoleFormModal(mode, roleId = null) {
        resetRoleForm(); // Selalu reset form saat modal dibuka
        if (mode === 'add') {
            roleFormModalLabel.textContent = 'Tambah Role Baru';
            currentEditRoleId = null;
            roleFormModal.show(); // Tampilkan modal
        } else if (mode === 'edit') {
            roleFormModalLabel.textContent = 'Edit Role';
            currentEditRoleId = roleId;
            // Ambil data role dari API untuk diisi ke form
            // const apiToken = 'YOUR_API_TOKEN_HERE';
            try {
                showSpinnerOnSaveButton(true); // Tampilkan spinner sementara data dimuat
                const response = await fetch(`/api/v1/roles/${roleId}`, {
                    headers: {
                        'Accept': 'application/json',
                        /* 'Authorization': `Bearer ${apiToken}` */
                    }
                });
                if (!response.ok) throw new Error('Gagal mengambil data role untuk diedit.');
                const result = await response.json();
                if (result.success && result.data) {
                    roleNameInput.value = result.data.name;
                    roleIdInput.value = result.data.id; // Set hidden ID
                    roleFormModal.show();
                } else {
                    alert(result.message || 'Role tidak ditemukan.');
                }
            } catch (error) {
                console.error('Error fetching role for edit:', error);
                alert('Gagal memuat data role: ' + error.message);
            } finally {
                showSpinnerOnSaveButton(false);
            }
        }
    }
    function showSpinnerOnSaveButton(show) {
        if (saveRoleButton) { // Pastikan tombol ada
            saveRoleButton.disabled = show;
            const spinner = saveRoleButton.querySelector('.spinner-border');
            if (spinner) {
                spinner.style.display = show ? 'inline-block' : 'none';
            }
        }
    }

    // Event listener untuk submit form role (Tambah/Edit)
    roleForm.addEventListener('submit', async function(event) {
        event.preventDefault(); // Cegah submit form tradisional
        showSpinnerOnSaveButton(true);
        roleFormAlertContainer.style.display = 'none';
        roleFormErrorsList.innerHTML = '';

        const formData = new FormData(roleForm);
        const data = Object.fromEntries(formData.entries()); // Ubah FormData menjadi objek biasa

        let url = '/api/v1/roles';
        let method = 'POST'; // Default untuk tambah baru

        if (currentEditRoleId) { // Jika mode edit
            url = `/api/v1/roles/${currentEditRoleId}`;
            // Method tetap POST, tapi kirim _method: 'PUT' agar Laravel tahu ini update
            data._method = 'PUT';
        }

        // const apiToken = 'YOUR_API_TOKEN_HERE';

        try {
            const response = await fetch(url, {
                method: 'POST', // Laravel akan menghandle _method: 'PUT'
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json', // Kirim data sebagai JSON
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    // 'Authorization': `Bearer ${apiToken}`,
                },
                body: JSON.stringify(data) // Ubah objek data menjadi string JSON
            });

            const result = await response.json();

            if (response.ok && result.success) { // Jika request sukses dan API mengembalikan success: true
                roleFormModal.hide(); // Tutup modal
                // Refresh tabel. Ke halaman 1 jika tambah baru, tetap di halaman saat ini jika edit.
                fetchAndDisplayRoles( (method === 'POST' && !currentEditRoleId ? 1 : currentPage) , itemsPerPage, currentSearchQuery);
                alert(result.message || 'Role berhasil disimpan!'); // Notifikasi sukses
            } else { // Jika ada error dari API (termasuk validasi)
                if (response.status === 422 && result.errors) { // Error validasi (status 422)
                    roleFormAlertContainer.style.display = 'block';
                    Object.values(result.errors).forEach(errorArray => {
                        errorArray.forEach(errorMessage => {
                            const li = document.createElement('li');
                            li.textContent = errorMessage;
                            roleFormErrorsList.appendChild(li);
                        });
                    });
                } else { // Error lain
                    roleFormAlertContainer.style.display = 'block';
                    const li = document.createElement('li');
                    li.textContent = result.message || 'Terjadi kesalahan yang tidak diketahui.';
                    roleFormErrorsList.appendChild(li);
                }
            }
        } catch (error) { // Error jaringan atau lainnya
            console.error('Error submitting role form:', error);
            roleFormAlertContainer.style.display = 'block';
            const li = document.createElement('li');
            li.textContent = 'Tidak dapat terhubung ke server atau terjadi kesalahan jaringan.';
            roleFormErrorsList.appendChild(li);
        } finally {
            showSpinnerOnSaveButton(false);
        }
    });


    // --- Logika untuk Modal Delete ---
    confirmDeleteButton.addEventListener('click', async function() {
        if (!roleToDeleteId) return; // Pastikan ada ID role yang akan dihapus

        // const apiToken = 'YOUR_API_TOKEN_HERE';
        const deleteUrl = `/api/v1/roles/${roleToDeleteId}`;

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    // 'Authorization': `Bearer ${apiToken}`,
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const modal = bootstrap.Modal.getInstance(deleteRoleModalElement); // Dapatkan instance modal Bootstrap
                modal.hide(); // Tutup modal

                // Refresh data tabel. Cek apakah halaman saat ini akan menjadi kosong setelah delete.
                const currentItemCountOnPage = rolesTableContainer.querySelectorAll('tbody tr').length;
                if (currentItemCountOnPage === 1 && currentPage > 1) { // Jika item terakhir di halaman > 1 dihapus
                    fetchAndDisplayRoles(currentPage - 1, itemsPerPage, currentSearchQuery); // Mundur satu halaman
                } else {
                    fetchAndDisplayRoles(currentPage, itemsPerPage, currentSearchQuery); // Tetap di halaman saat ini
                }
                alert(result.message || 'Role berhasil dihapus.'); // Notifikasi sukses
            } else {
                alert(result.message || `Gagal menghapus role. Status: ${response.status}`);
            }
        } catch (error) {
            console.error('Error deleting role:', error);
            alert('Terjadi kesalahan saat mencoba menghapus role: ' + error.message);
        }
    });

    // --- Panggil fungsi untuk pertama kali memuat data saat halaman dimuat ---
    fetchAndDisplayRoles(currentPage, itemsPerPage, currentSearchQuery);
});
</script>
@endpush
