<div class="modal fade" id="roleFormModal" tabindex="-1" aria-labelledby="roleFormModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleFormModalLabel">Form Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm">
                @csrf
                <input type="hidden" id="roleId" name="role_id">
                <div class="modal-body">
                    {{-- Alert untuk menampilkan error --}}
                    <div id="roleFormAlertContainer" class="mb-3" style="display: none;">
                        <div class="alert alert-danger" role="alert">
                            <ul id="roleFormErrors" class="mb-0"></ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="roleName" class="form-label">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required>
                    </div>

                </div>
                {{-- Modal Footer --}}
                <div class="modal-footer border-top pt-3">
                    <button type="button" 
                            class="btn btn-light px-4" 
                            data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Batal
                    </button>
                    <button type="submit" 
                            class="btn btn-primary px-4" 
                            id="saveRoleButton">
                        <span class="spinner-border spinner-border-sm d-none" 
                              role="status" 
                              aria-hidden="true">
                        </span>
                        <span class="button-text">
                            <i class="fas fa-save me-2"></i>
                            Simpan
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
