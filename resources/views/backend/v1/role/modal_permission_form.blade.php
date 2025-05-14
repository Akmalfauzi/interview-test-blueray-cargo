{{-- Modal Form Permission --}}
<div class="modal fade" 
     id="permissionFormModal" 
     tabindex="-1" 
     aria-labelledby="permissionFormModalLabel" 
     aria-hidden="true" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            {{-- Modal Header --}}
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold" id="permissionFormModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>
                    <span>Atur Permission Role</span>
                </h5>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal" 
                        aria-label="Close">
                </button>
            </div>

            {{-- Modal Form --}}
            <form id="permissionForm">
                @csrf
                <input type="hidden" id="permissionRoleId" name="role_id">
                
                {{-- Modal Body --}}
                <div class="modal-body">
                    {{-- Role Info --}}
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading mb-1">Role: <span id="permissionRoleName" class="fw-bold"></span></h6>
                                <p class="mb-0">Pilih permission yang akan diberikan untuk role ini.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Search Permission --}}
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="searchPermission" 
                                   placeholder="Cari permission...">
                        </div>
                    </div>

                    {{-- Permission Groups --}}
                    <div class="permission-groups">
                        {{-- Loading State --}}
                        <div id="permissionLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 mb-0">Memuat daftar permission...</p>
                        </div>

                        {{-- Permission List Container --}}
                        <div id="permissionList" class="d-none">
                            {{-- Permission groups will be rendered here --}}
                        </div>

                        {{-- No Results --}}
                        <div id="noPermissionResults" class="text-center py-4 d-none">
                            <i class="bi bi-search fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Tidak ada permission yang ditemukan.</p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-top pt-3">
                    <button type="button" 
                            class="btn btn-light px-4" 
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>
                        Batal
                    </button>
                    <button type="submit" 
                            class="btn btn-primary px-4" 
                            id="savePermissionButton">
                        <span class="spinner-border spinner-border-sm d-none" 
                              role="status" 
                              aria-hidden="true">
                        </span>
                        <span class="button-text">
                            <i class="bi bi-save me-2"></i>
                            Simpan Permission
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.permission-groups {
    max-height: 400px;
    overflow-y: auto;
}

.permission-group {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.permission-group-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
}

.permission-group-header h6 {
    margin: 0;
    color: #495057;
}

.permission-group-body {
    padding: 1rem;
}

.permission-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.15s ease-in-out;
}

.permission-item:hover {
    background-color: #f8f9fa;
}

.permission-item .form-check {
    margin: 0;
    flex: 1;
}

.permission-item .form-check-label {
    cursor: pointer;
    user-select: none;
}

.permission-item .permission-description {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Custom scrollbar */
.permission-groups::-webkit-scrollbar {
    width: 8px;
}

.permission-groups::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.permission-groups::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.permission-groups::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Search highlight */
.highlight {
    background-color: #fff3cd;
    padding: 0 2px;
    border-radius: 2px;
}
</style> 