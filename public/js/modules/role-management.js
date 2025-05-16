// Role Management Module
const RoleManagement = {
    // DOM Elements
    elements: {
        tableContainer: null,
        paginationLinks: null,
        paginationInfo: null,
        perPageSelect: null,
        searchQuery: null,
        searchButton: null,
        addRoleButton: null,
        roleFormModal: null,
        roleForm: null,
        roleFormModalLabel: null,
        roleIdInput: null,
        roleNameInput: null,
        saveRoleButton: null,
        roleFormAlertContainer: null,
        roleFormErrorsList: null,
        deleteRoleModal: null,
        confirmDeleteButton: null,
        roleNameToDelete: null,
        preloader: null
    },

    // State
    state: {
        currentPage: 1,
        itemsPerPage: 10,
        currentSearchQuery: '',
        roleToDeleteId: null,
        currentEditRoleId: null
    },

    // Initialize
    init() {
        this.initializeElements();
        this.attachEventListeners();
        this.createPreloader();
        this.fetchAndDisplayRoles();

        // Add permission form submit handler
        document.getElementById('permissionForm')?.addEventListener('submit', (e) => this.handlePermissionSubmit(e));
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
        this.elements.tableContainer = document.getElementById('rolesTableContainer');
        this.elements.paginationLinks = document.getElementById('paginationLinks');
        this.elements.paginationInfo = document.getElementById('paginationInfo');
        this.elements.perPageSelect = document.getElementById('perPage');
        this.elements.searchQuery = document.getElementById('searchQuery');
        this.elements.searchButton = document.getElementById('searchButton');
        this.elements.addRoleButton = document.getElementById('addRoleButton');
        this.elements.roleFormModal = new bootstrap.Modal(document.getElementById('roleFormModal'));
        this.elements.roleForm = document.getElementById('roleForm');
        this.elements.roleFormModalLabel = document.getElementById('roleFormModalLabel');
        this.elements.roleIdInput = document.getElementById('roleId');
        this.elements.roleNameInput = document.getElementById('roleName');
        this.elements.saveRoleButton = document.getElementById('saveRoleButton');
        this.elements.roleFormAlertContainer = document.getElementById('roleFormAlertContainer');
        this.elements.roleFormErrorsList = document.getElementById('roleFormErrors');
        this.elements.deleteRoleModal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
        this.elements.confirmDeleteButton = document.getElementById('confirmDeleteButton');
        this.elements.roleNameToDelete = document.getElementById('roleNameToDelete');
    },

    // Attach event listeners
    attachEventListeners() {
        // Pagination and search
        this.elements.perPageSelect.addEventListener('change', () => {
            this.state.itemsPerPage = parseInt(this.elements.perPageSelect.value);
            this.fetchAndDisplayRoles(1);
        });

        this.elements.searchButton.addEventListener('click', () => {
            this.state.currentSearchQuery = this.elements.searchQuery.value.trim();
            this.fetchAndDisplayRoles(1);
        });

        this.elements.searchQuery.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.elements.searchButton.click();
            }
        });

        // Add role button
        this.elements.addRoleButton.addEventListener('click', () => this.openRoleFormModal('add'));

        // Form submission
        this.elements.roleForm.addEventListener('submit', (event) => this.handleFormSubmit(event));

        // Delete confirmation
        this.elements.confirmDeleteButton.addEventListener('click', () => this.handleDeleteRole());
    },

    // Fetch and display roles
    async fetchAndDisplayRoles(page = 1) {
        this.state.currentPage = page;
        this.togglePreloader(true);

        try {
            const response = await fetch(
                `/api/v1/roles?page=${page}&per_page=${this.state.itemsPerPage}&search=${encodeURIComponent(this.state.currentSearchQuery)}`,
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
                this.showError(result.message || 'Data role tidak ditemukan');
            }
        } catch (error) {
            console.error('Error fetching roles:', error);
            this.showError('Terjadi kesalahan saat mengambil data role: ' + error.message);
        } finally {
            this.togglePreloader(false);
        }
    },

    // Render table
    renderTable(roles) {
        if (!roles || roles.length === 0) {
            this.elements.tableContainer.innerHTML = '<div class="p-3 text-center"><p>Belum ada data role.</p></div>';
            return;
        }

        const tableHtml = this.generateTableHtml(roles);
        this.elements.tableContainer.innerHTML = tableHtml;
        this.attachTableActionListeners();
    },

    // Generate table HTML
    generateTableHtml(roles) {
        let html = `
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama Role</th>
                        <th>Dibuat Pada</th>
                        <th style="width: 220px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>`;

        roles.forEach((role, index) => {
            const itemNumber = (this.state.currentPage - 1) * this.state.itemsPerPage + index + 1;
            const isSystemRole = role.id <= 2; // Check if role is system role (ID 1 or 2)
            
            html += `
                <tr>
                    <td>${itemNumber}.</td>
                    <td>${this.escapeHtml(role.name)}</td>
                    <td>${this.formatDate(role.created_at)}</td>
                    <td style="text-align:center;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info editRoleButton" title="Edit"
                                    data-role-id="${role.id}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning setPermissionButton" title="Atur Permission"
                                    data-role-id="${role.id}" data-role-name="${this.escapeHtml(role.name)}">
                                <i class="bi bi-shield-lock"></i>
                            </button>
                            ${!isSystemRole ? `
                                <button type="button" class="btn btn-sm btn-danger deleteRoleButton" title="Hapus"
                                        data-role-id="${role.id}" data-role-name="${this.escapeHtml(role.name)}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>`;
        });

        html += `</tbody></table>`;
        return html;
    },

    // Attach table action listeners
    attachTableActionListeners() {
        document.querySelectorAll('.editRoleButton').forEach(button => {
            button.addEventListener('click', () => {
                const roleId = button.getAttribute('data-role-id');
                this.openRoleFormModal('edit', roleId);
            });
        });

        document.querySelectorAll('.setPermissionButton').forEach(button => {
            button.addEventListener('click', () => {
                const roleId = button.getAttribute('data-role-id');
                const roleName = button.getAttribute('data-role-name');
                this.openPermissionModal(roleId, roleName);
            });
        });

        document.querySelectorAll('.deleteRoleButton').forEach(button => {
            button.addEventListener('click', () => {
                const roleId = button.getAttribute('data-role-id');
                const roleName = button.getAttribute('data-role-name');
                this.confirmDeleteRole(roleId, roleName);
            });
        });
    },

    // Confirm delete role with SweetAlert2
    confirmDeleteRole(roleId, roleName) {
        Swal.fire({
            title: '<div class="text-center"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Konfirmasi Hapus Role</div>',
            html: `
                <div class="text-start">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">Peringatan!</h5>
                                <p class="mb-0">Anda akan menghapus role <strong class="text-danger">${this.escapeHtml(roleName)}</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <p class="mb-0">Tindakan ini tidak dapat diurungkan. Semua data yang terkait dengan role ini akan terpengaruh.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i>Ya, Hapus Role',
            cancelButtonText: '<i class="bi bi-x-lg"></i>Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            customClass: {
                container: 'role-delete-confirm-modal',
                popup: 'role-delete-confirm-popup',
                title: 'role-delete-confirm-title mb-4',
                htmlContainer: 'role-delete-confirm-content mb-4',
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
                this.state.roleToDeleteId = roleId;
                return this.handleDeleteRole();
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isDismissed) {
                this.state.roleToDeleteId = null;
            }
        });
    },

    // Handle delete role
    async handleDeleteRole() {
        if (!this.state.roleToDeleteId) return false;

        try {
            const response = await fetch(`/api/v1/roles/${this.state.roleToDeleteId}`, {
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
                    await this.fetchAndDisplayRoles(this.state.currentPage - 1);
                } else {
                    await this.fetchAndDisplayRoles(this.state.currentPage);
                }
                
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'Role berhasil dihapus!'
                });

                return true;
            } else {
                throw new Error(result.message || `Gagal menghapus role. Status: ${response.status}`);
            }
        } catch (error) {
            console.error('Error deleting role:', error);
            Swal.showValidationMessage(
                `Gagal menghapus role: ${error.message}`
            );
            return false;
        } finally {
            this.state.roleToDeleteId = null;
        }
    },

    // Open role form modal
    async openRoleFormModal(mode, roleId = null) {
        this.resetForm();
        
        if (mode === 'add') {
            this.elements.roleFormModalLabel.textContent = 'Tambah Role Baru';
            this.state.currentEditRoleId = null;
            this.elements.roleFormModal.show();
        } else if (mode === 'edit') {
            this.elements.roleFormModalLabel.textContent = 'Edit Role';
            this.state.currentEditRoleId = roleId;
            this.togglePreloader(true);

            try {
                const response = await fetch(`/api/v1/roles/${roleId}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Gagal mengambil data role untuk diedit.');
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    this.elements.roleNameInput.value = result.data.name;
                    this.elements.roleIdInput.value = result.data.id;
                    this.elements.roleFormModal.show();
                } else {
                    window.Toast.fire({
                        icon: 'error',
                        title: result.message || 'Role tidak ditemukan.'
                    });
                }
            } catch (error) {
                console.error('Error fetching role for edit:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Gagal memuat data role: ' + error.message
                });
            } finally {
                this.togglePreloader(false);
            }
        }
    },

    // Reset form
    resetForm() {
        this.elements.roleForm.reset();
        this.elements.roleIdInput.value = '';
        this.state.currentEditRoleId = null;
        this.resetFormErrors();
        this.elements.roleForm.classList.remove('was-validated');
    },

    // Reset form errors
    resetFormErrors() {
        const alertContainer = this.elements.roleFormAlertContainer;
        const errorsList = this.elements.roleFormErrorsList;
        
        // Reset alert container
        alertContainer.style.display = 'none';
        alertContainer.classList.remove('show');
        
        // Clear error messages
        if (errorsList) {
            errorsList.innerHTML = '';
        }

        // Reset input states
        const inputs = this.elements.roleForm.querySelectorAll('input, select, textarea');
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
        const alertContainer = this.elements.roleFormAlertContainer;
        const errorsList = this.elements.roleFormErrorsList;
        
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
                const input = this.elements.roleForm.querySelector(`[name="${key}"]`);
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
        if (!this.elements.roleForm.checkValidity()) {
            this.elements.roleForm.classList.add('was-validated');
            return;
        }

        // Get button elements
        const saveButton = this.elements.saveRoleButton;
        const spinner = saveButton.querySelector('.spinner-border');
        const buttonText = saveButton.querySelector('.button-text');
        
        // Show loading state
        saveButton.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.classList.add('d-none');

        try {
            const formData = new FormData(this.elements.roleForm);
            const roleId = this.state.currentEditRoleId;
            const url = roleId ? `/api/v1/roles/${roleId}` : '/api/v1/roles';
            const method = roleId ? 'PUT' : 'POST';

            // Convert FormData to JSON for better handling
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

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
                this.elements.roleFormModal.hide();
                await this.fetchAndDisplayRoles(this.state.currentPage);
                
                // Show success message
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'Role berhasil disimpan!'
                });
            } else {
                // Show error messages
                if (result.errors) {
                    this.showFormErrors(result.errors);
                } else {
                    this.showFormErrors(result.message || 'Terjadi kesalahan saat menyimpan role');
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            this.showFormErrors('Terjadi kesalahan saat menyimpan role: ' + error.message);
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
                <i class="fas fa-exclamation-circle me-2"></i>
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
                    if (page) this.fetchAndDisplayRoles(parseInt(page));
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

    // Open permission modal
    async openPermissionModal(roleId, roleName) {
        // Set role info
        document.getElementById('permissionRoleId').value = roleId;
        document.getElementById('permissionRoleName').textContent = roleName;

        // Show loading state
        document.getElementById('permissionLoading').classList.remove('d-none');
        document.getElementById('permissionList').classList.add('d-none');
        document.getElementById('noPermissionResults').classList.add('d-none');

        // Show modal
        const permissionModal = new bootstrap.Modal(document.getElementById('permissionFormModal'));
        permissionModal.show();

        try {
            // Fetch permissions
            const response = await fetch(`/api/v1/roles/${roleId}/permissions`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Gagal mengambil data permission');
            
            const result = await response.json();
            
            if (result.success) {
                this.renderPermissionList(result.data);
            } else {
                throw new Error(result.message || 'Gagal memuat permission');
            }
        } catch (error) {
            console.error('Error fetching permissions:', error);
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal memuat permission: ' + error.message
            });
        } finally {
            document.getElementById('permissionLoading').classList.add('d-none');
        }
    },

    // Render permission list
    renderPermissionList(data) {
        const container = document.getElementById('permissionList');
        const searchInput = document.getElementById('searchPermission');
        
        // Clear previous content
        container.innerHTML = '';

        // Add global check all
        const globalCheckAllHtml = `
            <div class="global-check-all mb-3">
                <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="check_all_permissions">
                    <label class="form-check-label fw-bold" for="check_all_permissions">
                        Pilih Semua Permission
                    </label>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', globalCheckAllHtml);
        
        // Group permissions by module
        const groupedPermissions = this.groupPermissionsByModule(data.permissions);
        
        // Generate HTML for each group
        Object.entries(groupedPermissions).forEach(([module, permissions]) => {
            const groupHtml = this.generatePermissionGroupHtml(module, permissions, data.role_permissions);
            container.insertAdjacentHTML('beforeend', groupHtml);
        });

        // Show container
        container.classList.remove('d-none');

        // Setup search functionality
        this.setupPermissionSearch(searchInput, container);

        // Setup check all functionality
        this.setupCheckAllFunctionality();
    },

    // Group permissions by module
    groupPermissionsByModule(permissions) {
        return permissions.reduce((groups, permission) => {
            const module = permission.module || 'Umum';
            if (!groups[module]) {
                groups[module] = [];
            }
            groups[module].push(permission);
            return groups;
        }, {});
    },

    // Generate permission group HTML
    generatePermissionGroupHtml(module, permissions, rolePermissions) {
        const isChecked = (permissionId) => rolePermissions.includes(permissionId);
        const allChecked = permissions.every(permission => isChecked(permission.id));
        
        return `
            <div class="permission-group" data-module="${this.escapeHtml(module)}">
                <div class="permission-group-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-folder me-2"></i>
                        ${this.escapeHtml(module)}
                    </h6>
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input check-all-module" 
                               id="check_all_${this.escapeHtml(module)}" 
                               ${allChecked ? 'checked' : ''}>
                        <label class="form-check-label small" for="check_all_${this.escapeHtml(module)}">
                            Pilih Semua
                        </label>
                    </div>
                </div>
                <div class="permission-group-body">
                    ${permissions.map(permission => `
                        <div class="permission-item" data-permission-id="${permission.id}">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input module-${this.escapeHtml(module)}" 
                                       id="permission_${permission.id}" 
                                       name="permissions[]" 
                                       value="${permission.id}"
                                       ${isChecked(permission.id) ? 'checked' : ''}>
                                <label class="form-check-label" for="permission_${permission.id}">
                                    <div class="permission-name">${this.escapeHtml(permission.name)}</div>
                                    ${permission.description ? `
                                        <div class="permission-description">
                                            ${this.escapeHtml(permission.description)}
                                        </div>
                                    ` : ''}
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    },

    // Setup permission search
    setupPermissionSearch(searchInput, container) {
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.toLowerCase().trim();
            
            searchTimeout = setTimeout(() => {
                const groups = container.querySelectorAll('.permission-group');
                let hasResults = false;
                
                groups.forEach(group => {
                    const items = group.querySelectorAll('.permission-item');
                    let groupHasResults = false;
                    
                    items.forEach(item => {
                        const name = item.querySelector('.permission-name').textContent.toLowerCase();
                        const description = item.querySelector('.permission-description')?.textContent.toLowerCase() || '';
                        const matches = name.includes(query) || description.includes(query);
                        
                        item.style.display = matches ? '' : 'none';
                        if (matches) {
                            groupHasResults = true;
                            hasResults = true;
                            
                            // Highlight matching text
                            if (query) {
                                const nameEl = item.querySelector('.permission-name');
                                const descEl = item.querySelector('.permission-description');
                                
                                nameEl.innerHTML = this.highlightText(nameEl.textContent, query);
                                if (descEl) {
                                    descEl.innerHTML = this.highlightText(descEl.textContent, query);
                                }
                            }
                        }
                    });
                    
                    group.style.display = groupHasResults ? '' : 'none';
                });
                
                // Show/hide no results message
                document.getElementById('noPermissionResults').classList.toggle('d-none', hasResults);
            }, 300);
        });
    },

    // Highlight search text
    highlightText(text, query) {
        if (!query) return this.escapeHtml(text);
        
        const regex = new RegExp(`(${this.escapeHtml(query)})`, 'gi');
        return this.escapeHtml(text).replace(regex, '<span class="highlight">$1</span>');
    },

    // Setup check all functionality
    setupCheckAllFunctionality() {
        const container = document.getElementById('permissionList');
        
        // Global check all
        const globalCheckAll = container.querySelector('#check_all_permissions');
        if (globalCheckAll) {
            globalCheckAll.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                container.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                container.querySelectorAll('.check-all-module').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        }

        // Module check all
        container.querySelectorAll('.check-all-module').forEach(moduleCheckbox => {
            moduleCheckbox.addEventListener('change', (e) => {
                const module = e.target.id.replace('check_all_', '');
                const isChecked = e.target.checked;
                
                // Update all checkboxes in this module
                container.querySelectorAll(`.module-${module}`).forEach(checkbox => {
                    checkbox.checked = isChecked;
                });

                // Update global check all
                this.updateGlobalCheckAll(container);
            });
        });

        // Individual checkboxes
        container.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                // Update module check all
                const module = checkbox.className.split(' ').find(cls => cls.startsWith('module-'))?.replace('module-', '');
                if (module) {
                    this.updateModuleCheckAll(container, module);
                }
                
                // Update global check all
                this.updateGlobalCheckAll(container);
            });
        });
    },

    // Update module check all state
    updateModuleCheckAll(container, module) {
        const moduleCheckboxes = container.querySelectorAll(`.module-${module}`);
        const moduleCheckAll = container.querySelector(`#check_all_${module}`);
        
        if (moduleCheckAll && moduleCheckboxes.length > 0) {
            const allChecked = Array.from(moduleCheckboxes).every(checkbox => checkbox.checked);
            moduleCheckAll.checked = allChecked;
        }
    },

    // Update global check all state
    updateGlobalCheckAll(container) {
        const globalCheckAll = container.querySelector('#check_all_permissions');
        const allCheckboxes = container.querySelectorAll('input[name="permissions[]"]');
        
        if (globalCheckAll && allCheckboxes.length > 0) {
            const allChecked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
            globalCheckAll.checked = allChecked;
        }
    },

    // Handle permission form submission
    async handlePermissionSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const roleId = form.querySelector('#permissionRoleId').value;
        const saveButton = form.querySelector('#savePermissionButton');
        const spinner = saveButton.querySelector('.spinner-border');
        const buttonText = saveButton.querySelector('.button-text');
        
        // Get selected permissions
        const selectedPermissions = Array.from(form.querySelectorAll('input[name="permissions[]"]:checked'))
            .map(input => input.value);
        
        // Show loading state
        saveButton.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.classList.add('d-none');
        
        try {
            const response = await fetch(`/api/v1/roles/${roleId}/permissions`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ permissions: selectedPermissions })
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('permissionFormModal')).hide();
                
                // Show success message
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'Permission berhasil disimpan!'
                });
            } else {
                throw new Error(result.message || 'Gagal menyimpan permission');
            }
        } catch (error) {
            console.error('Error saving permissions:', error);
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal menyimpan permission: ' + error.message
            });
        } finally {
            // Reset button state
            saveButton.disabled = false;
            spinner.classList.add('d-none');
            buttonText.classList.remove('d-none');
        }
    }
};

// Export the module
export default RoleManagement; 