@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Manajemen Order',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Order', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Order</h3>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddOrder">
                        <i class="fas fa-plus"></i> Tambah Order
                    </button>
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
                                <label for="searchQuery">Cari Order:</label>
                                <input type="text" id="searchQuery" class="form-control form-control-sm" placeholder="Masukkan nomor order atau nama penerima...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="searchButton" class="btn btn-info btn-sm">Cari</button>
                            </div>
                        </div>
                    </div>
                    <hr class="m-0">

                    <div id="ordersTableContainer">
                        {{-- Tabel akan di-render oleh JavaScript di sini --}}
                        <div class="p-5 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Memuat data...</span>
                            </div>
                            <p class="mt-2">Memuat data order...</p>
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

    {{-- Include Modal Detail Order --}}
    @include('backend.v1.order.modal_order_detail')
    
    {{-- Include Modal Konfirmasi Hapus --}}
    @include('backend.v1.order.modal_delete_confirmation')

    {{-- Include Modal Tambah Order --}}
    @include('backend.v1.order.modal_add_order')
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
    /* Style untuk status order */
    .order-status {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-processing {
        background-color: #cce5ff;
        color: #004085;
    }
    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }
    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }
    /* Style untuk modal form */
    .modal-body .form-group {
        margin-bottom: 1rem;
    }
    .modal-body label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .modal-body .required:after {
        content: " *";
        color: red;
    }
    .item-row {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .item-row .remove-item {
        color: #dc3545;
        cursor: pointer;
    }
    /* Address search styles */
    .address-results {
        position: absolute;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 1000;
    }
    .address-item {
        transition: background-color 0.2s;
    }
    .address-item:hover {
        background-color: #f8f9fa;
    }
    .address-item:last-child {
        border-bottom: none !important;
    }
    .form-group {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script type="module">
    import OrderManagement from '{{ asset('js/modules/order-management.js') }}';
    
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
        OrderManagement.init();
    });
</script>
@endpush