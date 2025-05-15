export default class OrderManagement {
    static init() {
        this.initializeElements();
        this.attachEventListeners();
        this.loadOrders();
        this.initAddOrderModal();
        this.loadCouriers();
    }

    static initializeElements() {
        // Table elements
        this.elements = {
            tableContainer: document.getElementById('ordersTableContainer'),
            paginationLinks: document.getElementById('paginationLinks'),
            paginationInfo: document.getElementById('paginationInfo'),
            perPageSelect: document.getElementById('perPage'),
            searchQuery: document.getElementById('searchQuery'),
            searchButton: document.getElementById('searchButton'),
            confirmDeleteButton: document.getElementById('confirmDeleteButton'),
            preloader: null
        };

        // Create preloader element
        this.createPreloader();
    }

    static createPreloader() {
        const preloader = document.createElement('div');
        preloader.id = 'globalPreloader';
        preloader.className = 'global-preloader';
        preloader.innerHTML = `
            <div class="preloader-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data...</p>
            </div>
        `;
        document.body.appendChild(preloader);
        this.elements.preloader = preloader;
    }

    static togglePreloader(show) {
        if (this.elements.preloader) {
            this.elements.preloader.style.display = show ? 'flex' : 'none';
        }
    }

    static attachEventListeners() {
        // Search and pagination events
        this.elements.searchButton.addEventListener('click', () => this.loadOrders());
        this.elements.searchQuery.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.loadOrders();
        });
        this.elements.perPageSelect.addEventListener('change', () => this.loadOrders());

        // Delete confirmation
        this.elements.confirmDeleteButton.addEventListener('click', () => this.deleteOrder());
    }

    static async loadOrders(page = 1) {
        this.togglePreloader(true);
        const searchQuery = this.elements.searchQuery.value;
        const perPage = this.elements.perPageSelect.value;

        try {
            // Fetch orders
            const response = await fetch(`/api/v1/orders?page=${page}&per_page=${perPage}&search=${searchQuery}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal memuat data order');
            }

            // Render table
            this.renderOrdersTable(data.data);
            this.renderPagination(data.data);
            this.renderPaginationInfo(data.data);

        } catch (error) {
            console.error('Error loading orders:', error);
            this.elements.tableContainer.innerHTML = `
                <div class="p-5 text-center text-danger">
                    <i class="bi bi-exclamation-circle fs-1"></i>
                    <p class="mt-2">${error.message}</p>
                </div>
            `;
        } finally {
            this.togglePreloader(false);
        }
    }

    static renderOrdersTable(data) {
        if (!data.data.length) {
            this.elements.tableContainer.innerHTML = `
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">Tidak ada data order</p>
                </div>
            `;
            return;
        }

        const table = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th>Tanggal</th>
                            <th>Pengirim</th>
                            <th>Penerima</th>
                            <th>Kurir</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.data.map(order => `
                            <tr>
                                <td>${order.order_number}</td>
                                <td>${this.formatDate(order.created_at)}</td>
                                <td>${order.sender_name}</td>
                                <td>${order.receiver_name}</td>
                                <td>${order.courier_name}</td>
                                <td>
                                    <span class="order-status status-${order.status.toLowerCase()}">
                                        ${this.formatStatus(order.status)}
                                    </span>
                                </td>
                                <td>${this.formatCurrency(order.total_amount)}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" 
                                                onclick="OrderManagement.showDetail('${order.id}')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="OrderManagement.confirmDelete('${order.id}', '${order.order_number}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        this.elements.tableContainer.innerHTML = table;
    }

    static renderPagination(data) {
        if (data.last_page <= 1) {
            this.elements.paginationLinks.innerHTML = '';
            return;
        }

        let links = '';
        
        // Previous page
        links += `
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.current_page - 1}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (
                i === 1 || // First page
                i === data.last_page || // Last page
                (i >= data.current_page - 2 && i <= data.current_page + 2) // Pages around current
            ) {
                links += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (
                i === data.current_page - 3 ||
                i === data.current_page + 3
            ) {
                links += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }

        // Next page
        links += `
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.current_page + 1}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;

        this.elements.paginationLinks.innerHTML = `
            <ul class="pagination pagination-sm mb-0">
                ${links}
            </ul>
        `;

        // Add click events to pagination links
        this.elements.paginationLinks.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                if (!link.parentElement.classList.contains('disabled')) {
                    this.loadOrders(link.dataset.page);
                }
            });
        });
    }

    static renderPaginationInfo(data) {
        const start = (data.current_page - 1) * data.per_page + 1;
        const end = Math.min(start + data.per_page - 1, data.total);
        
        this.elements.paginationInfo.textContent = `Menampilkan ${start} sampai ${end} dari ${data.total} data`;
    }

    static async showDetail(orderId) {
        this.togglePreloader(true);
        try {
            const response = await fetch(`/api/v1/orders/${orderId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal memuat detail order');
            }

            const order = data.data;

            // Populate modal fields
            document.getElementById('detailOrderNumber').textContent = order.order_number;
            document.getElementById('detailOrderStatus').textContent = this.formatStatus(order.status);
            document.getElementById('detailOrderStatus').className = `order-status status-${order.status.toLowerCase()}`;
            document.getElementById('detailOrderDate').textContent = this.formatDate(order.created_at);
            document.getElementById('detailOrderTotal').textContent = this.formatCurrency(order.total_amount);

            document.getElementById('detailCourier').textContent = order.courier_name;
            document.getElementById('detailTrackingNumber').textContent = order.tracking_number || '-';
            document.getElementById('detailEstimatedDelivery').textContent = order.estimated_delivery || '-';

            document.getElementById('detailSenderName').textContent = order.sender_name;
            document.getElementById('detailSenderPhone').textContent = order.sender_phone;
            document.getElementById('detailSenderAddress').textContent = order.sender_address;

            document.getElementById('detailReceiverName').textContent = order.receiver_name;
            document.getElementById('detailReceiverPhone').textContent = order.receiver_phone;
            document.getElementById('detailReceiverAddress').textContent = order.receiver_address;

            // Populate items table
            const itemsTable = document.getElementById('detailItemsTable').querySelector('tbody');
            itemsTable.innerHTML = order.items.map(item => `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.weight} kg</td>
                    <td>${this.formatCurrency(item.price)}</td>
                    <td>${this.formatCurrency(item.subtotal)}</td>
                </tr>
            `).join('');

            document.getElementById('detailNotes').textContent = order.notes || '-';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            modal.show();

        } catch (error) {
            console.error('Error loading order detail:', error);
            window.Toast.fire({
                icon: 'error',
                title: error.message
            });
        } finally {
            this.togglePreloader(false);
        }
    }

    static confirmDelete(orderId, orderNumber) {
        document.getElementById('orderNumberToDelete').textContent = orderNumber;
        document.getElementById('confirmDeleteButton').dataset.orderId = orderId;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
        modal.show();
    }

    static async deleteOrder() {
        this.togglePreloader(true);
        const orderId = document.getElementById('confirmDeleteButton').dataset.orderId;
        
        try {
            const response = await fetch(`/api/v1/orders/${orderId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal menghapus order');
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteOrderModal')).hide();

            // Show success message
            window.Toast.fire({
                icon: 'success',
                title: 'Order berhasil dihapus'
            });

            // Reload orders
            this.loadOrders();

        } catch (error) {
            console.error('Error deleting order:', error);
            window.Toast.fire({
                icon: 'error',
                title: error.message
            });
        } finally {
            this.togglePreloader(false);
        }
    }

    static formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    static formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    }

    static formatStatus(status) {
        const statusMap = {
            'PENDING': 'Menunggu',
            'PROCESSING': 'Diproses',
            'COMPLETED': 'Selesai',
            'CANCELLED': 'Dibatalkan'
        };
        return statusMap[status] || status;
    }

    static initAddOrderModal() {
        this.itemIndex = 0;
        this.modal = document.getElementById('modalAddOrder');
        this.form = document.getElementById('formAddOrder');
        this.itemsContainer = document.getElementById('itemsContainer');
        this.addItemBtn = document.getElementById('addItemBtn');
        this.itemTemplate = document.getElementById('itemRowTemplate');

        // Add first item row by default
        this.addItemRow();
        
        // Initialize event listeners for the form
        this.initEventListeners();
    }

    static async loadCouriers() {
        try {
            const response = await fetch('/api/v1/orders/couriers', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (data.success) {
                const courierSelect = document.getElementById('courier_id');
                
                // Clear existing options except the first one
                courierSelect.innerHTML = '<option value="">Pilih Kurir</option>';
                
                // Add new options
                data.data.forEach(courier => {
                    const option = document.createElement('option');
                    option.value = courier.id;
                    option.textContent = `${courier.courier_name} (${courier.description}) (${courier.service_type} - ${courier.shipment_duration_range} ${courier.shipment_duration_unit})`;
                    courierSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading couriers:', error);
            window.Toast.fire({
                icon: 'error',
                title: 'Gagal memuat data kurir'
            });
        }
    }

    static addItemRow() {
        const template = this.itemTemplate.content.cloneNode(true);
        const itemRow = template.querySelector('.item-row');
        
        // Replace INDEX placeholder with current index
        itemRow.innerHTML = itemRow.innerHTML.replace(/INDEX/g, this.itemIndex);
        
        // Add event listeners for the new row
        const removeBtn = itemRow.querySelector('.remove-item');
        removeBtn.addEventListener('click', () => this.removeItemRow(itemRow));

        // Add calculation listeners
        const quantityInput = itemRow.querySelector('.item-quantity');
        const priceInput = itemRow.querySelector('.item-price');
        const weightInput = itemRow.querySelector('.item-weight');

        [quantityInput, priceInput].forEach(input => {
            input.addEventListener('input', () => this.calculateSubtotal(itemRow));
        });

        this.itemsContainer.appendChild(itemRow);
        this.itemIndex++;
    }

    static removeItemRow(row) {
        if (this.itemsContainer.children.length > 1) {
            row.remove();
        } else {
            window.Toast.fire({
                icon: 'warning',
                title: 'Minimal harus ada satu item'
            });
        }
    }

    static calculateSubtotal(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const subtotal = quantity * price;
        
        // You can add a subtotal display if needed
        // row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
    }

    static initEventListeners() {
        // Add item button click
        this.addItemBtn.addEventListener('click', () => this.addItemRow());

        // Form submit
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            this.togglePreloader(true);
            
            try {
                const formData = new FormData(this.form);
                const response = await fetch(this.form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (data.success) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'Order berhasil ditambahkan'
                    });

                    // Close modal and reset form
                    const modal = bootstrap.Modal.getInstance(this.modal);
                    modal.hide();
                    this.form.reset();
                    this.itemsContainer.innerHTML = '';
                    this.addItemRow();

                    // Refresh the orders table
                    this.loadOrders();
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menyimpan order');
                }
            } catch (error) {
                console.error('Error submitting order:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: error.message || 'Gagal menyimpan order'
                });
            } finally {
                this.togglePreloader(false);
            }
        });
    }
}

// Make OrderManagement available globally
window.OrderManagement = OrderManagement; 