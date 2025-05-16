@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Tracking',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Tracking', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cari Status Pengiriman</h3>
                </div>
                <div class="card-body">
                    <form id="trackingForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trackingNumber">Tracking ID</label>
                                    <input type="text" class="form-control" id="trackingNumber" name="trackingNumber" placeholder="Masukkan tracking ID">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="trackingResult" class="d-none">
                        <div class="tracking-info mb-4">
                            <h4>Informasi Pengiriman</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Status</th>
                                            <td id="trackingStatus">-</td>
                                        </tr>
                                        <tr>
                                            <th>Kurir</th>
                                            <td id="trackingCourier">-</td>
                                        </tr>
                                        <tr>
                                            <th>Link Tracking</th>
                                            <td>
                                                <a href="#" id="trackingLink" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-box-arrow-up-right"></i> Lihat Tracking
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tracking-timeline">
                            <h4>Riwayat Pengiriman</h4>
                            <div class="timeline" id="trackingTimeline">
                                <!-- Timeline items will be inserted here -->
                            </div>
                        </div>
                    </div>

                    <div id="trackingError" class="alert alert-danger d-none">
                        <!-- Error message will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: -20px;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item:last-child:before {
        display: none;
    }
    .timeline-item:after {
        content: '';
        position: absolute;
        left: 10px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
    }
    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
    }
    .timeline-date {
        font-size: 0.85em;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackingForm = document.getElementById('trackingForm');
    const trackingResult = document.getElementById('trackingResult');
    const trackingError = document.getElementById('trackingError');
    const trackingTimeline = document.getElementById('trackingTimeline');

    trackingForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const trackingNumber = document.getElementById('trackingNumber').value;

        if (!trackingNumber) {
            showError('Mohon masukkan tracking ID');
            return;
        }

        try {
            const response = await fetch(`/api/v1/tracking/${trackingNumber}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal mengambil data tracking');
            }

            // Show success notification
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data tracking berhasil ditemukan',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            displayTrackingResult(data.data);
        } catch (error) {
            showError(error.message);
        }
    });

    function displayTrackingResult(data) {
        // Hide error and show result
        trackingError.classList.add('d-none');
        trackingResult.classList.remove('d-none');

        // Update tracking info
        document.getElementById('trackingStatus').textContent = data.status || '-';
        document.getElementById('trackingCourier').textContent = data.courier?.company?.toUpperCase() || '-';
        
        // Update tracking link
        const trackingLink = document.getElementById('trackingLink');
        if (data.link) {
            trackingLink.href = data.link;
            trackingLink.classList.remove('d-none');
        } else {
            trackingLink.classList.add('d-none');
        }

        // Update timeline
        trackingTimeline.innerHTML = '';
        if (data.history && data.history.length > 0) {
            data.history.forEach(item => {
                const timelineItem = document.createElement('div');
                timelineItem.className = 'timeline-item';
                timelineItem.innerHTML = `
                    <div class="timeline-content">
                        <div class="timeline-date">${formatDate(item.updated_at)}</div>
                        <div class="timeline-text">
                            <div class="fw-medium">${item.status}</div>
                            <div class="text-muted">${item.note}</div>
                        </div>
                    </div>
                `;
                trackingTimeline.appendChild(timelineItem);
            });
        } else {
            trackingTimeline.innerHTML = '<p class="text-muted">Belum ada riwayat pengiriman</p>';
        }
    }

    function formatStatus(status) {
        const statusMap = {
            'confirmed': 'Dikonfirmasi',
            'allocated': 'Kurir Ditugaskan',
            'picking_up': 'Kurir Menuju Lokasi',
            'picked_up': 'Paket Diambil',
            'in_transit': 'Dalam Perjalanan',
            'delivered': 'Terkirim',
            'failed': 'Gagal Kirim',
            'cancelled': 'Dibatalkan'
        };
        return statusMap[status.toLowerCase()] || status;
    }

    function showError(message) {
        trackingResult.classList.add('d-none');
        trackingError.classList.remove('d-none');
        trackingError.textContent = message;

        // Show error notification
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    function formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }
});
</script>
@endpush