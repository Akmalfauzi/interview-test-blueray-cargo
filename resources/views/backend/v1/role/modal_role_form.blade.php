{{-- resources/views/layouts/backend/v1/components/modal_role_form.blade.php --}}
<div class="modal fade" id="roleFormModal" tabindex="-1" aria-labelledby="roleFormModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleFormModalLabel">Form Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm">
                @csrf {{-- Meskipun ini client-side, CSRF token bisa berguna jika form disubmit ke route web dulu --}}
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

                    {{-- Tempat untuk Permissions (jika Anda menggunakan Spatie/Permission atau sejenisnya) --}}
                    {{-- <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div id="permissionsContainer" class="row">
                            <p class="text-muted">Memuat permissions...</p>
                        </div>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveRoleButton">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
