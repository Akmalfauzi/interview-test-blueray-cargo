{{-- Modal Detail Order --}}
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Detail Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Order</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">No. Order</td>
                                <td id="detailOrderNumber">-</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td><span id="detailOrderStatus">-</span></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td id="detailOrderDate">-</td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td id="detailOrderTotal">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Kurir</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Kurir</td>
                                <td id="detailCourier">-</td>
                            </tr>
                            <tr>
                                <td>Tracking ID</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="detailTrackingId" class="me-2">-</span>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="copyTrackingId()">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>No. Resi</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="detailTrackingNumber" class="me-2">-</span>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="copyTrackingNumber()">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Link Tracking</td>
                                <td>
                                    <a href="#" id="detailTrackingLink" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> Lihat Tracking
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Estimasi Pengiriman</td>
                                <td id="detailEstimatedDelivery">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Pengirim</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Nama</td>
                                <td id="detailSenderName">-</td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td id="detailSenderPhone">-</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td id="detailSenderAddress">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Penerima</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Nama</td>
                                <td id="detailReceiverName">-</td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td id="detailReceiverPhone">-</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td id="detailReceiverAddress">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="mb-3">Daftar Item</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="detailItemsTable">
                                <thead>
                                    <tr>
                                        <th>Nama Item</th>
                                        <th>Jumlah</th>
                                        <th>Berat</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Items will be populated by JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-3">Catatan</h6>
                        <p id="detailNotes" class="mb-0">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyTrackingId() {
    const trackingId = document.getElementById('detailTrackingId').textContent;
    if (trackingId && trackingId !== '-') {
        navigator.clipboard.writeText(trackingId).then(() => {
            // Show success message with SweetAlert2
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Tracking ID berhasil disalin',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menyalin Tracking ID',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        });
    }
}

function copyTrackingNumber() {
    const trackingNumber = document.getElementById('detailTrackingNumber').textContent;
    if (trackingNumber && trackingNumber !== '-') {
        navigator.clipboard.writeText(trackingNumber).then(() => {
            // Show success message with SweetAlert2
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nomor Resi berhasil disalin',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menyalin Nomor Resi',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        });
    }
}
</script>
@endpush 