{{-- Modal Detail Order --}}
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Detail Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Order</h6>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 40%">Nomor Order</th>
                                <td id="detailOrderNumber">-</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span id="detailOrderStatus" class="order-status">-</span></td>
                            </tr>
                            <tr>
                                <th>Tanggal Order</th>
                                <td id="detailOrderDate">-</td>
                            </tr>
                            <tr>
                                <th>Total Biaya</th>
                                <td id="detailOrderTotal">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Pengiriman</h6>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 40%">Kurir</th>
                                <td id="detailCourier">-</td>
                            </tr>
                            <tr>
                                <th>No. Resi</th>
                                <td id="detailTrackingNumber">-</td>
                            </tr>
                            <tr>
                                <th>Estimasi</th>
                                <td id="detailEstimatedDelivery">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Pengirim</h6>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 40%">Nama</th>
                                <td id="detailSenderName">-</td>
                            </tr>
                            <tr>
                                <th>Telepon</th>
                                <td id="detailSenderPhone">-</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td id="detailSenderAddress">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Penerima</h6>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 40%">Nama</th>
                                <td id="detailReceiverName">-</td>
                            </tr>
                            <tr>
                                <th>Telepon</th>
                                <td id="detailReceiverPhone">-</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td id="detailReceiverAddress">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="mb-3">Detail Barang</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="detailItemsTable">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Berat (kg)</th>
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

                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="mb-3">Catatan</h6>
                        <p id="detailNotes" class="text-muted">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div> 