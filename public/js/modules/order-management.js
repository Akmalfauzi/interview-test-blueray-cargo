export default class OrderManagement {
    static init() {
        this.initializeElements();
        this.attachEventListeners();
        this.loadOrders();
        this.loadCouriers();
        this.initAddOrderModal();
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
                                <td>${order.raw_biteship_payload.id}</td>
                                <td>${this.formatDate(order.created_at)}</td>
                                <td>
                                    <div>${order.shipper_name}</div>
                                    <small class="text-muted">${order.shipper_phone}</small>
                                </td>
                                <td>
                                    <div>${order.receiver_name}</div>
                                    <small class="text-muted">${order.receiver_phone}</small>
                                </td>
                                <td>
                                    <div>${order.raw_biteship_payload.courier.company.toUpperCase()}</div>
                                    <small class="text-muted">${order.raw_biteship_payload.courier.type}</small>
                                </td>
                                <td>
                                    <span class="order-status status-${order.status.toLowerCase()}">
                                        ${this.formatStatus(order.status)}
                                    </span>
                                </td>
                                <td>${this.formatCurrency(order.raw_biteship_payload.price)}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" 
                                                onclick="OrderManagement.showDetail('${order.id}')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="OrderManagement.confirmDelete('${order.id}', '${order.raw_biteship_payload.id}')">
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
            document.getElementById('detailOrderNumber').textContent = order.raw_biteship_payload.id;
            document.getElementById('detailOrderStatus').textContent = this.formatStatus(order.status);
            document.getElementById('detailOrderStatus').className = `order-status status-${order.status.toLowerCase()}`;
            document.getElementById('detailOrderDate').textContent = this.formatDate(order.created_at);
            document.getElementById('detailOrderTotal').textContent = this.formatCurrency(order.raw_biteship_payload.price);

            // Courier Information
            document.getElementById('detailCourier').textContent = order.raw_biteship_payload.courier.company.toUpperCase();
            document.getElementById('detailTrackingNumber').textContent = order.raw_biteship_payload.courier.waybill_id;
            document.getElementById('detailTrackingLink').href = order.raw_biteship_payload.courier.link;
            document.getElementById('detailEstimatedDelivery').textContent = this.formatDate(order.raw_biteship_payload.delivery.datetime);

            // Sender Information
            document.getElementById('detailSenderName').textContent = order.shipper_name;
            document.getElementById('detailSenderPhone').textContent = order.shipper_phone;
            document.getElementById('detailSenderAddress').textContent = order.shipper_address;

            // Receiver Information
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
                    <td>${this.formatCurrency(item.price * item.quantity)}</td>
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
            'confirmed': 'Dikonfirmasi',
            'pending': 'Menunggu',
            'processing': 'Diproses',
            'completed': 'Selesai',
            'cancelled': 'Dibatalkan'
        };
        return statusMap[status.toLowerCase()] || status;
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
        this.initAddressSearch();
    }

    static initAddressSearch() {
        // Initialize address search for sender
        this.initAddressSearchField('sender_address', 'sender_address_results');
        // Initialize address search for receiver
        this.initAddressSearchField('receiver_address', 'receiver_address_results');
    }

    static initAddressSearchField(inputId, resultsId) {
        const input = document.getElementById(inputId);
        const resultsContainer = document.createElement('div');
        resultsContainer.id = resultsId;
        resultsContainer.className = 'address-results mt-2';
        resultsContainer.style.display = 'none';
        input.parentNode.appendChild(resultsContainer);

        // Create hidden inputs for all address data
        const hiddenInputs = {
            id: document.createElement('input'),
            name: document.createElement('input'),
            postal_code: document.createElement('input'),
        };

        // Configure hidden inputs
        Object.entries(hiddenInputs).forEach(([key, element]) => {
            element.type = 'hidden';
            element.id = `${inputId}_${key}`;
            element.name = `${inputId}_${key}`;
            input.parentNode.appendChild(element);
        });

        let searchTimeout;
        input.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 3) {
                resultsContainer.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                this.searchAddress(query, resultsContainer, input, hiddenInputs);
            }, 500);
        });

        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    static async searchAddress(query, resultsContainer, input, hiddenInputs) {
        try {
            const response = await fetch(`/api/v1/orders/map-location?query=${encodeURIComponent(query)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal mencari alamat');
            }

            if (data.data.length === 0) {
                resultsContainer.innerHTML = '<div class="p-2 text-muted">Tidak ada hasil ditemukan</div>';
                resultsContainer.style.display = 'block';
                return;
            }

            resultsContainer.innerHTML = data.data.map(location => `
                <div class="address-item p-2 border-bottom" style="cursor: pointer;" 
                     data-id="${location.id}"
                     data-name="${location.name}"
                     data-postal_code="${location.postal_code}">
                    <div class="fw-medium">${location.name}</div>
                </div>
            `).join('');

            // Add click event to each result
            resultsContainer.querySelectorAll('.address-item').forEach(item => {
                item.addEventListener('click', () => {
                    // Update main input with formatted address
                    input.value = item.dataset.name;
                    
                    // Update all hidden inputs
                    Object.keys(hiddenInputs).forEach(key => {
                        const value = item.dataset[key.replace(/_/g, '')] || '';
                        hiddenInputs[key].value = value;
                    });

                    resultsContainer.style.display = 'none';
                });
            });

            resultsContainer.style.display = 'block';
        } catch (error) {
            console.error('Error searching address:', error);
            resultsContainer.innerHTML = '<div class="p-2 text-danger">Gagal mencari alamat</div>';
            resultsContainer.style.display = 'block';
        }
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
                    option.value = courier.courier_code;
                    option.textContent = `${courier.courier_name} (${courier.description}) (${courier.service_type} - ${courier.shipment_duration_range} ${courier.shipment_duration_unit})`;
                    option.dataset.courierCode = courier.courier_code;
                    option.dataset.courierName = courier.courier_name;
                    option.dataset.courierServiceType = courier.service_type;
                    courierSelect.appendChild(option);
                });

                // Add change event listener to populate form fields
                courierSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        // Populate form fields with data attributes
                        document.getElementById('courier_code').value = selectedOption.dataset.courierCode;
                        document.getElementById('courier_name').value = selectedOption.dataset.courierName;
                        document.getElementById('service_type').value = selectedOption.dataset.courierServiceType;
                    } else {
                        // Clear form fields if no courier selected
                        document.getElementById('courier_code').value = '';
                        document.getElementById('courier_name').value = '';
                        document.getElementById('service_type').value = '';
                    }
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

        // Add subtotal display
        const subtotalDiv = document.createElement('div');
        subtotalDiv.className = 'col-md-12 mt-2';
        subtotalDiv.innerHTML = `
            <div class="d-flex justify-content-end">
                <span class="text-muted">Subtotal: </span>
                <span class="item-subtotal ms-2 fw-bold">Rp 0</span>
            </div>
        `;
        itemRow.querySelector('.row').appendChild(subtotalDiv);

        this.itemsContainer.appendChild(itemRow);
        this.itemIndex++;
    }

    static removeItemRow(row) {
        if (this.itemsContainer.children.length > 1) {
            // Add fade out animation
            row.style.transition = 'opacity 0.3s ease-out';
            row.style.opacity = '0';
            
            // Remove after animation
            setTimeout(() => {
                row.remove();
                // Recalculate all subtotals
                this.recalculateAllSubtotals();
            }, 300);
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
        
        // Update subtotal display
        const subtotalElement = row.querySelector('.item-subtotal');
        if (subtotalElement) {
            subtotalElement.textContent = this.formatCurrency(subtotal);
        }
    }

    static recalculateAllSubtotals() {
        this.itemsContainer.querySelectorAll('.item-row').forEach(row => {
            this.calculateSubtotal(row);
        });
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
                
                // Convert FormData to JSON object
                const jsonData = {};
                formData.forEach((value, key) => {
                    // Handle nested arrays (items)
                    if (key.startsWith('items[')) {
                        const matches = key.match(/items\[(\d+)\]\[(\w+)\]/);
                        if (matches) {
                            const [, index, field] = matches;
                            if (!jsonData.items) jsonData.items = [];
                            if (!jsonData.items[index]) jsonData.items[index] = {};
                            jsonData.items[index][field] = value;
                        }
                    } else {
                        jsonData[key] = value;
                    }
                });

                const response = await fetch(this.form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
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