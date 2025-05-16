@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Dashboard',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Dashboard', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-primary">
                <div class="inner">
                    <h3 id="totalOrders">-</h3>
                    <p>Total Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z">
                    </path>
                </svg>
                <a href="{{ route('order.index') }}"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-info">
                <div class="inner">
                    <h3 id="confirmedOrders">-</h3>
                    <p>Confirmed Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <a href="{{ route('order.index') }}"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-warning">
                <div class="inner">
                    <h3 id="inProgressOrders">-</h3>
                    <p>In Progress Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99">
                    </path>
                </svg>
                <a href="{{ route('order.index') }}"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-success">
                <div class="inner">
                    <h3 id="deliveredOrders">-</h3>
                    <p>Delivered Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <a href="{{ route('order.index') }}"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Additional Status Boxes --}}
    <div class="row mt-4">
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-secondary">
                <div class="inner">
                    <h3 id="onHoldOrders">-</h3>
                    <p>On Hold Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-danger">
                <div class="inner">
                    <h3 id="problemOrders">-</h3>
                    <p>Problem Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z">
                    </path>
                </svg>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-info">
                <div class="inner">
                    <h3 id="returnedOrders">-</h3>
                    <p>Returned Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3">
                    </path>
                </svg>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-dark">
                <div class="inner">
                    <h3 id="cancelledOrders">-</h3>
                    <p>Cancelled Orders</p>
                </div>
                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            @role('admin')
                            <th>Customer</th>
                            @endrole
                            <th>Shipper</th>
                            <th>Receiver</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersTable">
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Loading Spinner --}}
    <div id="dashboardLoading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Error Alert --}}
    <div id="dashboardError" class="alert alert-danger mt-3" style="display: none;">
        <ul id="dashboardErrors" class="mb-0"></ul>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const $loading = $('#dashboardLoading');
            const $error = $('#dashboardError');
            const $errors = $('#dashboardErrors');
            const $recentOrdersTable = $('#recentOrdersTable');
            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

            function updateDashboardStats() {
                $loading.show();
                $error.hide();
                $errors.empty();

                $.ajax({
                    url: '{{ route('api.v1.dashboard') }}',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Update stats
                        $('#totalOrders').text(response.data.total_orders || 0);
                        $('#confirmedOrders').text(response.data.confirmed_orders || 0);
                        
                        // Calculate in-progress orders
                        const inProgressOrders = 
                            (response.data.allocated_orders || 0) +
                            (response.data.picking_up_orders || 0) +
                            (response.data.picked_orders || 0) +
                            (response.data.dropping_off_orders || 0);
                        $('#inProgressOrders').text(inProgressOrders);
                        
                        $('#deliveredOrders').text(response.data.delivered_orders || 0);
                        $('#onHoldOrders').text(response.data.on_hold_orders || 0);
                        
                        // Calculate problem orders
                        const problemOrders = 
                            (response.data.rejected_orders || 0) +
                            (response.data.courier_not_found_orders || 0) +
                            (response.data.return_in_transit_orders || 0);
                        $('#problemOrders').text(problemOrders);
                        
                        $('#returnedOrders').text(response.data.returned_orders || 0);
                        $('#cancelledOrders').text(response.data.cancelled_orders || 0);

                        // Update recent orders table
                        if (response.data.recent_orders && response.data.recent_orders.length > 0) {
                            let tableHtml = '';
                            response.data.recent_orders.forEach(order => {
                                tableHtml += `
                                    <tr>
                                        <td>${order.id}</td>
                                        ${isAdmin ? `<td>${order.user ? order.user.name : 'N/A'}</td>` : ''}
                                        <td>${order.shipper_name}</td>
                                        <td>${order.receiver_name}</td>
                                        <td><span class="badge bg-${getStatusColor(order.status)}">${formatStatus(order.status)}</span></td>
                                        <td>${new Date(order.created_at).toLocaleDateString('id-ID')}</td>
                                        <td>
                                            <a href="{{ route('order.index') }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });
                            $recentOrdersTable.html(tableHtml);
                        } else {
                            $recentOrdersTable.html(`<tr><td colspan="${isAdmin ? '7' : '6'}" class="text-center">No recent orders</td></tr>`);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.message) {
                            $errors.append('<li>' + response.message + '</li>');
                        } else {
                            $errors.append('<li>Failed to load dashboard data</li>');
                        }
                        $error.show();
                    },
                    complete: function() {
                        $loading.hide();
                    }
                });
            }

            function getStatusColor(status) {
                switch (status.toLowerCase()) {
                    case 'confirmed':
                        return 'primary';
                    case 'allocated':
                    case 'pickingup':
                        return 'info';
                    case 'picked':
                    case 'droppingoff':
                        return 'warning';
                    case 'delivered':
                    case 'returned':
                        return 'success';
                    case 'onhold':
                        return 'secondary';
                    case 'rejected':
                    case 'couriernotfound':
                    case 'cancelled':
                    case 'disposed':
                    case 'returnintransit':
                        return 'danger';
                    default:
                        return 'secondary';
                }
            }

            function formatStatus(status) {
                // Convert camelCase to Title Case with spaces
                return status
                    .replace(/([A-Z])/g, ' $1')
                    .replace(/^./, function(str) { return str.toUpperCase(); });
            }

            // Load initial data
            updateDashboardStats();

            // Refresh data every 5 minutes
            setInterval(updateDashboardStats, 5 * 60 * 1000);
        });
    </script>
@endpush
