<!-- Modal Tambah Order -->
<div class="modal fade" id="modalAddOrder" tabindex="-1" aria-labelledby="modalAddOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddOrderLabel">Tambah Order Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddOrder" action="{{ route('api.v1.orders.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Informasi Pengirim -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Informasi Pengirim</h6>
                            <div class="form-group">
                                <label for="sender_name" class="required">Nama Pengirim</label>
                                <input type="text" class="form-control" id="sender_name" name="sender_name" required>
                            </div>
                            <div class="form-group">
                                <label for="sender_phone" class="required">Nomor Telepon Pengirim</label>
                                <input type="tel" class="form-control" id="sender_phone" name="sender_phone" required>
                            </div>
                            <div class="form-group">
                                <label for="sender_address" class="required">Alamat Pengirim</label>
                                <textarea class="form-control" id="sender_address" name="sender_address" rows="3" required></textarea>
                            </div>
                        </div>

                        <!-- Informasi Penerima -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Informasi Penerima</h6>
                            <div class="form-group">
                                <label for="receiver_name" class="required">Nama Penerima</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name" required>
                            </div>
                            <div class="form-group">
                                <label for="receiver_phone" class="required">Nomor Telepon Penerima</label>
                                <input type="tel" class="form-control" id="receiver_phone" name="receiver_phone" required>
                            </div>
                            <div class="form-group">
                                <label for="receiver_address" class="required">Alamat Penerima</label>
                                <textarea class="form-control" id="receiver_address" name="receiver_address" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Informasi Pengiriman -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="mb-3">Informasi Pengiriman</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="courier_id" class="required">Kurir</label>
                                        <select id="courier_id" name="courier_id" class="form-control" required>
                                            <option value="">Pilih Kurir</option>
                                        </select>
                                        <input type="hidden" id="courier_code" name="courier_code">
                                        <input type="hidden" id="courier_name" name="courier_name">
                                        <input type="hidden" id="service_type" name="service_type">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes">Catatan</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Daftar Item -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Daftar Item</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                    <i class="fas fa-plus"></i> Tambah Item
                                </button>
                            </div>
                            <div id="itemsContainer">
                                <!-- Item rows will be added here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template untuk Item Row -->
<template id="itemRowTemplate">
    <div class="item-row">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required">Nama Item</label>
                    <input type="text" class="form-control item-name" name="items[INDEX][name]" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required">Jumlah</label>
                    <input type="number" class="form-control item-quantity" name="items[INDEX][quantity]" min="1" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required">Berat (kg)</label>
                    <input type="number" class="form-control item-weight" name="items[INDEX][weight]" step="0.01" min="0.1" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required">Harga</label>
                    <input type="number" class="form-control item-price" name="items[INDEX][price]" min="0" required>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-link text-danger remove-item">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template> 