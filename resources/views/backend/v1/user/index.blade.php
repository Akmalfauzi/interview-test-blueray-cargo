@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Manajemen User',
        'breadcrumbs' => [['title' => 'Home', 'url' => route('dashboard')], ['title' => 'User', 'active' => true]],
    ])
    @endcomponent
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Daftar User</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="addUserButton">
                            <i class="bi bi-plus-lg"></i> Tambah User
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Filter dan Search --}}
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
                                <label for="searchQuery">Cari User:</label>
                                <input type="text" id="searchQuery" class="form-control form-control-sm" placeholder="Masukkan nama atau email user...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="searchButton" class="btn btn-info btn-sm">Cari</button>
                            </div>
                        </div>
                    </div>
                    <hr class="m-0">

                    <div id="usersTableContainer">
                        {{-- Tabel akan di-render oleh JavaScript di sini --}}
                        <div class="p-5 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Memuat data...</span>
                            </div>
                            <p class="mt-2">Memuat data user...</p>
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

    {{-- Include Modal Form User --}}
    @include('backend.v1.user.modal_user_form')
    
    {{-- Include Modal Form Role --}}
    @include('backend.v1.user.modal_role_form')

    {{-- Modal untuk Konfirmasi Hapus --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Konfirmasi Hapus User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus user <strong id="userNameToDelete"></strong>? Tindakan ini tidak dapat diurungkan.
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
<link rel="stylesheet" href="{{ asset('css/modules/preloader.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
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
    #userFormErrors li {
        list-style-type: disc;
        margin-left: 20px;
    }
    /* Style untuk tombol aksi di tabel */
    .table .btn-group {
        display: flex;
        gap: 0.25rem;
    }
    .table .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    .table .btn-group .btn i {
        font-size: 0.875rem;
    }
    /* Style untuk role list di modal */
    .role-item {
        padding: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }
    .role-item:last-child {
        border-bottom: none;
    }
    .role-description {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script type="module">
    import UserManagement from '{{ asset('js/modules/user-management.js') }}';
    
    // Initialize SweetAlert2 defaults
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Make Toast available globally
    window.Toast = Toast;
    
    document.addEventListener('DOMContentLoaded', function() {
        UserManagement.init();
    });
</script>
@endpush
