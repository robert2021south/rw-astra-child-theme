jQuery(document).ready(function($) {
    'use strict';

    const RW_Orders = {
        init: function () {

            $(document).on('rw_account:load_orders', ()=> {
                this.loadOrders();
            });

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') === 'my_orders' && window.RW_Account && window.RW_Account.getJwtToken()) {
                this.loadOrders();
            }

        },

        // 🔹
        loadOrders: async function () {
            if (!window.RW_Account || !window.RW_Account.getJwtToken()) return;

            const jwtToken = window.RW_Account.getJwtToken();
            const apiBaseUrl = window.RW_Account.getApiBaseUrl();
            const $ordersTab = $('.rw-orders-tab');
            const self = this;

            try {
                const response = await fetch(`${apiBaseUrl}/user/orders`, {
                    headers: {
                        'Authorization': 'Bearer ' + jwtToken,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const result = await response.json();
                if (result.status === 'success') {
                    $ordersTab.html(self.renderOrders(result.data.orders));
                } else {
                    window.RW_Account.showNotice('Failed to load orders: ' + (result.data?.message || 'Unknown'), 'error');
                }
            } catch (err) {console.log(err)
                window.RW_Account.showNotice('Network error loading orders', 'error');
                $ordersTab.html('<div class="rw-error">Failed to load orders. Please try again.</div>');
            }
        },

        // 🔹
        renderOrders: function(orders) {
            if (!orders || orders.length === 0) {
                return '<div class="rw-empty-state">' +
                    '<span class="rw-empty-icon">📦</span>' +
                    '<p>No orders found</p>' +
                    '</div>';
            }

            let html = '<div class="rw-orders-list">';

            orders.forEach(order => {

                const statusClass = this.getStatusClass(order.status);
                const statusText = this.getStatusText(order.status);

                // 格式化日期
                const orderDate = order.billed_at || order.ordered_at;
                const formattedDate = window.RW_Account.formatDate(orderDate)

                html += `
            <div class="rw-order-card" data-order-id="${order.order_id}">
                <div class="rw-order-header">
                    <div class="rw-order-info">
                        <span class="rw-order-number">#${this.escapeHtml(order.order_number || order.transaction_id)}</span>
                        <span class="rw-order-date">${formattedDate}</span>
                    </div>
                    <span class="rw-order-status ${statusClass}">${statusText}</span>
                </div>

                <div class="rw-order-body">
                    <div class="rw-order-details">
                        <div class="rw-order-items">
                            <span class="rw-items-summary">${this.escapeHtml(order.items_summary.summary)}</span>
                        </div>
                        
                        <div class="rw-order-meta">
                            <div class="rw-payment-badge" title="${order.payment_method.display}">
                                <span class="rw-payment-icon">${order.payment_method.icon}</span>
                                <span class="rw-payment-text">${order.payment_method.display}</span>
                            </div>
                            
                            ${order.has_invoice ? `
                            <div class="rw-invoice-badge">
                                <span class="rw-invoice-icon">📄</span>
                                <span class="rw-invoice-text">Invoice</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <div class="rw-order-amount">
                        <span class="rw-amount-currency">${order.currency}</span>
                        <span class="rw-amount-value">${order.total_amount}</span>
                    </div>
                </div>

                ${order.refunded_amount ? `
                <div class="rw-order-footer">
                    <div class="rw-refund-badge">
                        Refunded: ${order.currency} ${order.refunded_amount}
                        ${order.refunded_at ? `on ${new Date(order.refunded_at).toLocaleDateString()}` : ''}
                    </div>
                </div>
                ` : ''}

                <div class="rw-order-actions">
                    <button class="rw-btn-view" data-order-id="${order.order_id}">
                        View Details
                    </button>
                    
                </div>
            </div>
        `;
            });

            html += '</div>';

            setTimeout(() => this.bindCardEvents(), 0);

            return html;
        },

        // 🔹 -----------
        escapeHtml: function (text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function (m) {
                return map[m];
            });
        },

        // 🔹
        bindCardEvents: function () {

            // 查看详情按钮 - 跳转到订单详情页
            $('.rw-btn-view').off('click').on('click', function (e) {
                e.preventDefault();

                const orderId = $(this).data('order-id');

                const accountUrl = window.RW_Account.getAccountUrl();

                window.location.href = `${accountUrl}?type=order_detail&order_id=${orderId}`;
            });

        },

        // 获取状态样式类
        getStatusClass: function(status) {
            const statusMap = {
                'paid': 'rw-status-paid',
                'refunded': 'rw-status-refunded',
                'pending': 'rw-status-pending',
                'cancelled': 'rw-status-cancelled'
            };
            return statusMap[status] || 'rw-status-unknown';
        },

        // 获取状态显示文本
        getStatusText: function(status) {
            const statusMap = {
                'paid': 'Paid',
                'refunded': 'Refunded',
                'pending': 'Pending',
                'cancelled': 'Cancelled'
            };
            return statusMap[status] || status || 'Unknown';
        },
    };

    RW_Orders.init();
});