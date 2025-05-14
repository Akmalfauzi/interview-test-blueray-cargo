// User Management Module
const UserManagement = {
    // DOM Elements
    elements: {
        tableContainer: null,
        paginationLinks: null,
        paginationInfo: null,
        perPageSelect: null,
        searchQuery: null,
        searchButton: null,
        addUserButton: null,
        userFormModal: null,
        userForm: null,
        userFormModalLabel: null,
        userIdInput: null,
        userDisplayNameInput: null,
        userEmailInput: null,
        userPasswordInput: null,
        userPasswordConfirmationInput: null,
        userRolesInput: null,
        saveUserButton: null,
        userFormAlertContainer: null,
        userFormErrorsList: null,
        deleteUserModal: null,
        confirmDeleteButton: null,
        userDisplayNameToDelete: null,
        preloader: null
    },

    // State
    state: {
        currentPage: 1,
        itemsPerPage: 10,
        currentSearchQuery: '',
        userToDeleteId: null,
        currentEditUserId: null,
        availableRoles: []
    },

    // Initialize
    init() {
        this.initializeElements();
        this.attachEventListeners();
        this.createPreloader();
        this.fetchAvailableRoles();
        this.fetchAndDisplayUsers();

        // Remove duplicate role form handler since it's now handled in the module
        const roleForm = document.getElementById('roleForm');
        if (roleForm) {
            const newRoleForm = roleForm.cloneNode(true);
            roleForm.parentNode.replaceChild(newRoleForm, roleForm);
            newRoleForm.addEventListener('submit', (e) => this.handleRoleSubmit(e));
        }
    },

    // Create preloader element
    createPreloader() {
        const preloader = document.createElement('div');
        preloader.id = 'globalPreloader';
        preloader.className = 'global-preloader';
        preloader.innerHTML = `
            <div class="preloader-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data...</p>
            </div>
        `;
        document.body.appendChild(preloader);
        this.elements.preloader = preloader;
    },

    // Show/hide preloader
    togglePreloader(show) {
        if (this.elements.preloader) {
            this.elements.preloader.style.display = show ? 'flex' : 'none';
        }
    },

    // Initialize DOM elements
    initializeElements() {
        this.elements.tableContainer = document.getElementById('usersTableContainer');
        this.elements.paginationLinks = document.getElementById('paginationLinks');
        this.elements.paginationInfo = document.getElementById('paginationInfo');
        this.elements.perPageSelect = document.getElementById('perPage');
        this.elements.searchQuery = document.getElementById('searchQuery');
        this.elements.searchButton = document.getElementById('searchButton');
        this.elements.addUserButton = document.getElementById('addUserButton');
        this.elements.userFormModal = new bootstrap.Modal(document.getElementById('userFormModal'));
        this.elements.userForm = document.getElementById('userForm');
        this.elements.userFormModalLabel = document.getElementById('userFormModalLabel');
        this.elements.userIdInput = document.getElementById('userId');
        this.elements.userDisplayNameInput = document.getElementById('userName');
        this.elements.userEmailInput = document.getElementById('userEmail');
        this.elements.userPasswordInput = document.getElementById('userPassword');
        this.elements.userPasswordConfirmationInput = document.getElementById('userPasswordConfirmation');
        this.elements.userRolesInput = document.getElementById('userRoles');
        this.elements.saveUserButton = document.getElementById('saveUserButton');
        this.elements.userFormAlertContainer = document.getElementById('userFormAlertContainer');
        this.elements.userFormErrorsList = document.getElementById('userFormErrors');
        this.elements.deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        this.elements.confirmDeleteButton = document.getElementById('confirmDeleteButton');
        this.elements.userDisplayNameToDelete = document.getElementById('userNameToDelete');
    },

    // Attach event listeners
    attachEventListeners() {
        // Pagination and search
        this.elements.perPageSelect.addEventListener('change', () => {
            this.state.itemsPerPage = parseInt(this.elements.perPageSelect.value);
            this.fetchAndDisplayUsers(1);
        });

        this.elements.searchButton.addEventListener('click', () => {
            this.state.currentSearchQuery = this.elements.searchQuery.value.trim();
            this.fetchAndDisplayUsers(1);
        });

        this.elements.searchQuery.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.elements.searchButton.click();
            }
        });

        // Add user button
        this.elements.addUserButton.addEventListener('click', () => this.openUserFormModal('add'));

        // Form submission
        this.elements.userForm.addEventListener('submit', (event) => this.handleFormSubmit(event));

        // Delete confirmation
        this.elements.confirmDeleteButton?.addEventListener('click', () => this.handleDeleteUser());
    },

    // Fetch available roles
    async fetchAvailableRoles() {
        try {
            const response = await fetch('/api/v1/roles', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Gagal mengambil data role');
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.state.availableRoles = result.data.data;
                this.updateRolesSelect();
            }
        } catch (error) {
            console.error('Error fetching roles:', error);
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal memuat data role: ' + error.message
            });
        }
    },

    // Update roles select element
    updateRolesSelect() {
        const select = this.elements.userRolesInput;
        if (!select) return;

        // Clear existing options
        select.innerHTML = '<option value="">Pilih Role...</option>';

        // Add role options
        this.state.availableRoles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.name;
            select.appendChild(option);
        });
    },

    // Fetch and display users
    async fetchAndDisplayUsers(page = 1) {
        this.state.currentPage = page;
        this.togglePreloader(true);

        try {
            const response = await fetch(
                `/api/v1/users?page=${page}&per_page=${this.state.itemsPerPage}&search=${encodeURIComponent(this.state.currentSearchQuery)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                this.renderTable(result.data.data);
                this.renderPagination(result.data);
                this.renderPaginationInfo(result.data);
            } else {
                this.showError(result.message || 'Data user tidak ditemukan');
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            this.showError('Terjadi kesalahan saat mengambil data user: ' + error.message);
        } finally {
            this.togglePreloader(false);
        }
    },

    // Render table
    renderTable(users) {
        if (!users || users.length === 0) {
            this.elements.tableContainer.innerHTML = '<div class="p-3 text-center"><p>Belum ada data user.</p></div>';
            return;
        }

        const tableHtml = this.generateTableHtml(users);
        this.elements.tableContainer.innerHTML = tableHtml;
        this.attachTableActionListeners();
    },

    // Generate table HTML
    generateTableHtml(users) {
        let html = `
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat Pada</th>
                        <th style="width: 220px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>`;

        users.forEach((user, index) => {
            const itemNumber = (this.state.currentPage - 1) * this.state.itemsPerPage + index + 1;
            const roles = user.roles?.map(role => role.name).join(', ') || '-';
            
            html += `
                <tr>
                    <td>${itemNumber}.</td>
                    <td>${this.escapeHtml(user.name)}</td>
                    <td>${this.escapeHtml(user.email)}</td>
                    <td>${this.escapeHtml(roles)}</td>
                    <td>${this.formatDate(user.created_at)}</td>
                    <td style="text-align:center;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info editUserButton" title="Edit"
                                    data-user-id="${user.id}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning setRoleButton" title="Atur Role"
                                    data-user-id="${user.id}" data-user-name="${this.escapeHtml(user.name)}">
                                <i class="bi bi-person-gear"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger deleteUserButton" title="Hapus"
                                    data-user-id="${user.id}" data-user-name="${this.escapeHtml(user.name)}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });

        html += `</tbody></table>`;
        return html;
    },

    // Attach table action listeners
    attachTableActionListeners() {
        document.querySelectorAll('.editUserButton').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                this.openUserFormModal('edit', userId);
            });
        });

        document.querySelectorAll('.setRoleButton').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                const displayName = button.getAttribute('data-user-name');
                this.openRoleModal(userId, displayName);
            });
        });

        document.querySelectorAll('.deleteUserButton').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                const displayName = button.getAttribute('data-user-name');
                this.confirmDeleteUser(userId, displayName);
            });
        });
    },

    // Confirm delete user with SweetAlert2
    confirmDeleteUser(userId, displayName) {
        Swal.fire({
            title: '<div class="text-center"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Konfirmasi Hapus User</div>',
            html: `
                <div class="text-start">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">Peringatan!</h5>
                                <p class="mb-0">Anda akan menghapus user <strong class="text-danger">${this.escapeHtml(displayName)}</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <p class="mb-0">Tindakan ini tidak dapat diurungkan. Semua data yang terkait dengan user ini akan terpengaruh.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i>Ya, Hapus User',
            cancelButtonText: '<i class="bi bi-x-lg"></i>Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            customClass: {
                container: 'user-delete-confirm-modal',
                popup: 'user-delete-confirm-popup',
                title: 'user-delete-confirm-title mb-4',
                htmlContainer: 'user-delete-confirm-content mb-4',
                confirmButton: 'btn btn-danger px-4 py-2',
                cancelButton: 'btn btn-secondary px-4 py-2 mx-2',
                actions: 'swal2-actions mt-4',
                footer: 'swal2-footer'
            },
            buttonsStyling: false,
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            },
            width: '32em',
            padding: '1.5em',
            preConfirm: () => {
                this.state.userToDeleteId = userId;
                return this.handleDeleteUser();
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isDismissed) {
                this.state.userToDeleteId = null;
            }
        });
    },

    // Handle delete user
    async handleDeleteUser() {
        if (!this.state.userToDeleteId) return false;

        try {
            const response = await fetch(`/api/v1/users/${this.state.userToDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const currentItemCount = this.elements.tableContainer.querySelectorAll('tbody tr').length;
                
                if (currentItemCount === 1 && this.state.currentPage > 1) {
                    await this.fetchAndDisplayUsers(this.state.currentPage - 1);
                } else {
                    await this.fetchAndDisplayUsers(this.state.currentPage);
                }
                
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'User berhasil dihapus!'
                });

                return true;
            } else {
                throw new Error(result.message || `Gagal menghapus user. Status: ${response.status}`);
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            Swal.showValidationMessage(
                `Gagal menghapus user: ${error.message}`
            );
            return false;
        } finally {
            this.state.userToDeleteId = null;
        }
    },

    // Open user form modal
    async openUserFormModal(mode, userId = null) {
        this.resetForm();
        
        if (mode === 'add') {
            this.elements.userFormModalLabel.textContent = 'Tambah User Baru';
            this.state.currentEditUserId = null;
            this.elements.userFormModal.show();
        } else if (mode === 'edit') {
            this.elements.userFormModalLabel.textContent = 'Edit User';
            this.state.currentEditUserId = userId;
            this.togglePreloader(true);

            try {
                const response = await fetch(`/api/v1/users/${userId}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Gagal mengambil data user untuk diedit.');
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    const user = result.data;
                    this.elements.userDisplayNameInput.value = user.name;
                    this.elements.userEmailInput.value = user.email;
                    this.elements.userIdInput.value = user.id;
                    
                    // Set roles if any
                    if (user.roles && user.roles.length > 0) {
                        this.elements.userRolesInput.value = user.roles[0].id;
                    }
                    
                    this.elements.userFormModal.show();
                } else {
                    window.Toast.fire({
                        icon: 'error',
                        title: result.message || 'User tidak ditemukan.'
                    });
                }
            } catch (error) {
                console.error('Error fetching user for edit:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Gagal memuat data user: ' + error.message
                });
            } finally {
                this.togglePreloader(false);
            }
        }
    },

    // Reset form
    resetForm() {
        this.elements.userForm.reset();
        this.elements.userIdInput.value = '';
        this.state.currentEditUserId = null;
        this.resetFormErrors();
        this.elements.userForm.classList.remove('was-validated');
    },

    // Reset form errors
    resetFormErrors() {
        const alertContainer = this.elements.userFormAlertContainer;
        const errorsList = this.elements.userFormErrorsList;
        
        // Reset alert container
        alertContainer.style.display = 'none';
        alertContainer.classList.remove('show');
        
        // Clear error messages
        if (errorsList) {
            errorsList.innerHTML = '';
        }

        // Reset input states
        const inputs = this.elements.userForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        });
    },

    // Show form errors
    showFormErrors(errors) {
        const alertContainer = this.elements.userFormAlertContainer;
        const errorsList = this.elements.userFormErrorsList;
        
        // Reset previous errors
        this.resetFormErrors();
        
        // Show alert container
        alertContainer.style.display = 'block';
        alertContainer.classList.add('show');
        
        // Add error messages to list
        if (typeof errors === 'object') {
            Object.keys(errors).forEach(key => {
                const errorMessages = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
                errorMessages.forEach(message => {
                    const li = document.createElement('li');
                    li.textContent = message;
                    errorsList.appendChild(li);
                });

                // Add invalid state to corresponding input
                const input = this.elements.userForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = errorMessages[0];
                    }
                }
            });
        } else if (typeof errors === 'string') {
            const li = document.createElement('li');
            li.textContent = errors;
            errorsList.appendChild(li);
        }

        // Scroll to alert container
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    // Handle form submission
    async handleFormSubmit(event) {
        event.preventDefault();
        
        // Reset previous errors
        this.resetFormErrors();
        
        // Validate form
        if (!this.elements.userForm.checkValidity()) {
            this.elements.userForm.classList.add('was-validated');
            return;
        }

        // Get button elements
        const saveButton = this.elements.saveUserButton;
        const spinner = saveButton.querySelector('.spinner-border');
        const buttonText = saveButton.querySelector('.button-text');
        
        // Show loading state
        saveButton.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.classList.add('d-none');

        try {
            const formData = new FormData(this.elements.userForm);
            const userId = this.state.currentEditUserId;
            const url = userId ? `/api/v1/users/${userId}` : '/api/v1/users';
            const method = userId ? 'PUT' : 'POST';

            // Convert FormData to JSON for better handling
            const jsonData = {};
            formData.forEach((value, key) => {
                // Handle roles array
                if (key === 'roles[]') {
                    if (!jsonData.roles) {
                        jsonData.roles = [];
                    }
                    jsonData.roles.push(value);
                } else {
                    // Only include password if it's not empty
                    if (key === 'password' && !value) {
                        return;
                    }
                    jsonData[key] = value;
                }
            });

            // If no password provided in edit mode, remove password fields
            if (userId && !jsonData.password) {
                delete jsonData.password;
                delete jsonData.password_confirmation;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(jsonData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Close modal and refresh table
                this.elements.userFormModal.hide();
                await this.fetchAndDisplayUsers(this.state.currentPage);
                
                // Show success message
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'User berhasil disimpan!'
                });
            } else {
                // Show error messages
                if (result.errors) {
                    this.showFormErrors(result.errors);
                } else {
                    this.showFormErrors(result.message || 'Terjadi kesalahan saat menyimpan user');
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            this.showFormErrors('Terjadi kesalahan saat menyimpan user: ' + error.message);
        } finally {
            // Reset button state
            saveButton.disabled = false;
            spinner.classList.add('d-none');
            buttonText.classList.remove('d-none');
        }
    },

    // Show error message
    showError(message) {
        this.elements.tableContainer.innerHTML = `
            <div class="alert alert-danger m-3">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                ${message}
            </div>`;
        
        window.Toast.fire({
            icon: 'error',
            title: message
        });
    },

    // Render pagination
    renderPagination(paginationData) {
        if (!paginationData?.links?.length) {
            this.elements.paginationLinks.innerHTML = '';
            return;
        }

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm m-0';

        paginationData.links.forEach(link => {
            const li = document.createElement('li');
            li.className = `page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`;
            
            const a = document.createElement('a');
            a.className = 'page-link';
            a.innerHTML = link.label;
            
            if (link.url) {
                a.href = '#';
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    const urlParams = new URL(link.url).searchParams;
                    const page = urlParams.get('page');
                    if (page) this.fetchAndDisplayUsers(parseInt(page));
                });
            }
            
            li.appendChild(a);
            ul.appendChild(li);
        });

        this.elements.paginationLinks.innerHTML = '';
        this.elements.paginationLinks.appendChild(ul);
    },

    // Render pagination info
    renderPaginationInfo(paginationData) {
        if (!paginationData?.total) {
            this.elements.paginationInfo.innerHTML = 'Tidak ada data.';
            return;
        }

        const from = paginationData.from || 0;
        const to = paginationData.to || 0;
        const total = paginationData.total || 0;
        this.elements.paginationInfo.innerHTML = `Menampilkan ${from} sampai ${to} dari ${total} data.`;
    },

    // Utility functions
    escapeHtml(unsafe) {
        if (unsafe == null) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        try {
            const options = { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        } catch (e) { 
            return dateString; 
        }
    },

    // Open role modal
    async openRoleModal(userId, displayName) {
        if (!userId || !displayName) {
            console.error('Invalid user data for role modal');
            return;
        }

        // Reset form and state
        const form = document.getElementById('roleForm');
        if (!form) {
            console.error('Role form not found');
            return;
        }

        form.reset();
        form.classList.remove('was-validated');
        
        // Reset all states
        const elements = {
            alertContainer: document.getElementById('roleFormAlertContainer'),
            roleList: document.getElementById('roleList'),
            roleLoading: document.getElementById('roleLoading'),
            noRoleResults: document.getElementById('noRoleResults'),
            roleUserName: document.getElementById('roleUserName'),
            roleUserId: document.getElementById('roleUserId')
        };

        // Validate required elements
        for (const [key, element] of Object.entries(elements)) {
            if (!element) {
                console.error(`Required element not found: ${key}`);
                return;
            }
        }

        // Reset UI states
        elements.alertContainer.classList.add('d-none');
        elements.roleList.classList.add('d-none');
        elements.roleLoading.classList.remove('d-none');
        elements.noRoleResults.classList.add('d-none');
        
        // Set user info
        elements.roleUserId.value = userId;
        elements.roleUserName.textContent = displayName;

        // Show modal
        const roleModal = new bootstrap.Modal(document.getElementById('roleFormModal'));
        roleModal.show();

        try {
            // Fetch available roles and user's current roles
            const [rolesResponse, userRolesResponse] = await Promise.all([
                fetch('/api/v1/roles', {
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                }),
                fetch(`/api/v1/users/${userId}/roles`, {
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
            ]);

            if (!rolesResponse.ok || !userRolesResponse.ok) {
                throw new Error('Gagal mengambil data role');
            }

            const [rolesResult, userRolesResult] = await Promise.all([
                rolesResponse.json(),
                userRolesResponse.json()
            ]);

            if (!rolesResult.success || !userRolesResult.success) {
                throw new Error('Data role tidak valid');
            }

            const roles = rolesResult.data.data || [];
            const userRoles = userRolesResult.data.roles || [];

            // Generate role list HTML
            const roleListHtml = this.generateRoleListHtml(roles, userRoles);
            elements.roleList.innerHTML = roleListHtml;
            
            // Show appropriate content
            if (roles.length === 0) {
                elements.noRoleResults.classList.remove('d-none');
            } else {
                elements.roleList.classList.remove('d-none');
                
                // Add event listeners to radio buttons for validation
                const radioButtons = elements.roleList.querySelectorAll('input[type="radio"]');
                const roleContainer = form.querySelector('.mb-3');
                
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', () => {
                        const anySelected = Array.from(radioButtons).some(rb => rb.checked);
                        roleContainer.classList.toggle('is-invalid', !anySelected);
                        
                        // Update save button state
                        const saveButton = form.querySelector('#saveRoleButton');
                        if (saveButton) {
                            saveButton.disabled = !anySelected;
                        }
                    });
                });

                // Initial validation
                const anySelected = Array.from(radioButtons).some(rb => rb.checked);
                roleContainer.classList.toggle('is-invalid', !anySelected);
            }
        } catch (error) {
            console.error('Error in openRoleModal:', error);
            elements.alertContainer.classList.remove('d-none');
            const errorsList = document.getElementById('roleFormErrors');
            if (errorsList) {
                errorsList.innerHTML = `<li>${error.message}</li>`;
            }
            
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal memuat data role: ' + error.message
            });
        } finally {
            elements.roleLoading.classList.add('d-none');
        }
    },

    // Generate role list HTML
    generateRoleListHtml(roles, userRoles) {
        if (!Array.isArray(roles) || !Array.isArray(userRoles)) {
            console.error('Invalid roles data');
            return '';
        }

        if (roles.length === 0) {
            document.getElementById('noRoleResults')?.classList.remove('d-none');
            return '';
        }

        return roles.map(role => {
            const isChecked = userRoles.some(userRole => userRole.id === role.id);
            return `
                <div class="role-item">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="radio" 
                               name="role" 
                               value="${this.escapeHtml(role.id)}" 
                               id="role_${this.escapeHtml(role.id)}"
                               ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="role_${this.escapeHtml(role.id)}">
                            <div class="role-name">${this.escapeHtml(role.name)}</div>
                            ${role.description ? 
                                `<div class="role-description">${this.escapeHtml(role.description)}</div>` : 
                                ''}
                        </label>
                    </div>
                </div>`;
        }).join('');
    },

    // Handle role form submission
    async handleRoleSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        if (!form) {
            console.error('Role form not found');
            return;
        }

        const elements = {
            userId: form.querySelector('#roleUserId'),
            saveButton: form.querySelector('#saveRoleButton'),
            spinner: form.querySelector('#saveRoleButton .spinner-border'),
            buttonText: form.querySelector('#saveRoleButton .button-text'),
            alertContainer: document.getElementById('roleFormAlertContainer'),
            errorsList: document.getElementById('roleFormErrors'),
            roleContainer: form.querySelector('.mb-3')
        };

        // Validate required elements
        for (const [key, element] of Object.entries(elements)) {
            if (!element) {
                console.error(`Required element not found: ${key}`);
                return;
            }
        }

        // Reset previous errors
        form.classList.remove('was-validated');
        elements.alertContainer.classList.add('d-none');
        elements.errorsList.innerHTML = '';
        
        // Get selected role
        const selectedRole = form.querySelector('input[name="role"]:checked')?.value;
        
        // Validate role is selected
        if (!selectedRole) {
            form.classList.add('was-validated');
            elements.roleContainer.classList.add('is-invalid');
            return;
        }
        
        // Show loading state
        elements.saveButton.disabled = true;
        elements.spinner.classList.remove('d-none');
        elements.buttonText.classList.add('d-none');
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            const response = await fetch(`/api/v1/users/${elements.userId.value}/roles`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ roles: [selectedRole] }) // Send as array with single role
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('roleFormModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Refresh user table
                await this.fetchAndDisplayUsers(this.state.currentPage);
                
                // Show success message
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'Role berhasil disimpan!'
                });
            } else {
                // Show error messages
                if (result.errors) {
                    elements.alertContainer.classList.remove('d-none');
                    Object.entries(result.errors).forEach(([field, messages]) => {
                        const errorMessages = Array.isArray(messages) ? messages : [messages];
                        errorMessages.forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            elements.errorsList.appendChild(li);
                        });
                    });
                } else {
                    throw new Error(result.message || 'Gagal menyimpan role');
                }
            }
        } catch (error) {
            console.error('Error in handleRoleSubmit:', error);
            elements.alertContainer.classList.remove('d-none');
            elements.errorsList.innerHTML = `<li>${error.message}</li>`;
            
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal menyimpan role: ' + error.message
            });
        } finally {
            // Reset button state
            elements.saveButton.disabled = false;
            elements.spinner.classList.add('d-none');
            elements.buttonText.classList.remove('d-none');
        }
    }
};

// Export the module
export default UserManagement; 