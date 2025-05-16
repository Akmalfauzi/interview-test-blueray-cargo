@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Tracking History',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Tracking History', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Riwayat Tracking</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="trackingHistoryTable">
                            <thead>
                                <tr>
                                    <th>Tracking ID</th>
                                    <th>No. Resi</th>
                                    <th>Kurir</th>
                                    <th>Status</th>
                                    <th>Pengirim</th>
                                    <th>Penerima</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data will be populated by JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Tracking --}}
<div class="modal fade" id="trackingDetailModal" tabindex="-1" aria-labelledby="trackingDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trackingDetailModalLabel">Detail Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Pengiriman</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Status</td>
                                <td id="detailStatus">-</td>
                            </tr>
                            <tr>
                                <td>Kurir</td>
                                <td id="detailCourier">-</td>
                            </tr>
                            <tr>
                                <td>Link Tracking</td>
                                <td>
                                    <a href="#" id="detailLink" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> Lihat Tracking
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Pengirim</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Nama</td>
                                <td id="detailSenderName">-</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td id="detailSenderAddress">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="mb-3">Informasi Penerima</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="20%">Nama</td>
                                <td id="detailReceiverName">-</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td id="detailReceiverAddress">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-3">Riwayat Pengiriman</h6>
                        <div class="timeline" id="detailTimeline">
                            {{-- Timeline items will be populated by JavaScript --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
    loadTrackingHistory();

    function loadTrackingHistory() {
        fetch('/api/v1/tracking/history', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                renderTrackingTable(response.data);
            } else {
                showError(response.message);
            }
        })
        .catch(error => {
            showError('Gagal memuat data tracking');
        });
    }

    function renderTrackingTable(data) {
        const tbody = document.querySelector('#trackingHistoryTable tbody');
        tbody.innerHTML = '';

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data tracking</td>
                </tr>
            `;
            return;
        }

        data.data.forEach(item => {
            const payload = item.raw_biteship_payload;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${payload.id}</td>
                <td>${payload.waybill_id}</td>
                <td>${payload.courier.company.toUpperCase()}</td>
                <td><span class="badge bg-primary">${payload.status}</span></td>
                <td>
                    <div>${payload.origin.contact_name}</div>
                    <small class="text-muted">${payload.origin.address}</small>
                </td>
                <td>
                    <div>${payload.destination.contact_name}</div>
                    <small class="text-muted">${payload.destination.address}</small>
                </td>
                <td>${formatDate(item.created_at)}</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm" onclick="showTrackingDetail(${JSON.stringify(payload).replace(/"/g, '&quot;')})">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Add pagination info
        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'd-flex justify-content-between align-items-center mt-3';
        paginationInfo.innerHTML = `
            <div class="text-muted">
                Menampilkan ${data.from} sampai ${data.to} dari ${data.total} data
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    ${data.links.map(link => `
                        <li class="page-item ${!link.url ? 'disabled' : ''} ${link.active ? 'active' : ''}">
                            <a class="page-link" href="#" data-url="${link.url || '#'}">${link.label}</a>
                        </li>
                    `).join('')}
                </ul>
            </nav>
        `;

        // Remove existing pagination if any
        const existingPagination = document.querySelector('.d-flex.justify-content-between');
        if (existingPagination) {
            existingPagination.remove();
        }

        // Add new pagination
        document.querySelector('.card-body').appendChild(paginationInfo);

        // Add click event listeners to pagination links
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;
                if (url && url !== '#') {
                    loadPage(url);
                }
            });
        });
    }

    function loadPage(url) {
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                renderTrackingTable(response.data);
            } else {
                showError(response.message);
            }
        })
        .catch(error => {
            showError('Gagal memuat data tracking');
        });
    }

    function showTrackingDetail(data) {
        // Update modal content
        document.getElementById('detailStatus').textContent = data.status;
        document.getElementById('detailCourier').textContent = data.courier.company.toUpperCase();
        document.getElementById('detailLink').href = data.link;
        
        document.getElementById('detailSenderName').textContent = data.origin.contact_name;
        document.getElementById('detailSenderAddress').textContent = data.origin.address;
        
        document.getElementById('detailReceiverName').textContent = data.destination.contact_name;
        document.getElementById('detailReceiverAddress').textContent = data.destination.address;

        // Update timeline
        const timeline = document.getElementById('detailTimeline');
        timeline.innerHTML = '';
        
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
                timeline.appendChild(timelineItem);
            });
        } else {
            timeline.innerHTML = '<p class="text-muted">Belum ada riwayat pengiriman</p>';
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('trackingDetailModal'));
        modal.show();
    }

    function showError(message) {
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

// Make showTrackingDetail available globally
window.showTrackingDetail = function(data) {
    const modal = new bootstrap.Modal(document.getElementById('trackingDetailModal'));
    
    // Update modal content
    document.getElementById('detailStatus').textContent = data.status;
    document.getElementById('detailCourier').textContent = data.courier.company.toUpperCase();
    document.getElementById('detailLink').href = data.link;
    
    document.getElementById('detailSenderName').textContent = data.origin.contact_name;
    document.getElementById('detailSenderAddress').textContent = data.origin.address;
    
    document.getElementById('detailReceiverName').textContent = data.destination.contact_name;
    document.getElementById('detailReceiverAddress').textContent = data.destination.address;

    // Update timeline
    const timeline = document.getElementById('detailTimeline');
    timeline.innerHTML = '';
    
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
            timeline.appendChild(timelineItem);
        });
    } else {
        timeline.innerHTML = '<p class="text-muted">Belum ada riwayat pengiriman</p>';
    }

    modal.show();
};

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
</script>
@endpush