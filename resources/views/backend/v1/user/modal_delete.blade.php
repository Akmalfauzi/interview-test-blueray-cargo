{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Alert untuk error --}}
                <div id="deleteAlertContainer" class="alert alert-danger d-none" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-exclamation-circle-fill fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading mb-1">Terjadi Kesalahan!</h5>
                            <p id="deleteError" class="mb-0"></p>
                        </div>
                    </div>
                </div>

                {{-- Hidden input untuk ID --}}
                <input type="hidden" id="deleteUserId">

                {{-- Konfirmasi pesan --}}
                <div class="text-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning fa-3x mb-3"></i>
                    <h5 class="mb-2">Apakah Anda yakin?</h5>
                    <p class="mb-0" id="deleteConfirmMessage">
                        Anda akan menghapus user ini. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span class="button-text">Hapus</span>
                </button>
            </div>
        </div>
    </div>
</div> 