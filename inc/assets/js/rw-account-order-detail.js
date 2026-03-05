jQuery(document).ready(function($) {
    'use strict';

    const RW_Order_Detail = {
        orderId: null,
        orderData: null,

        init: function() {
            //
            this.orderId = this.getOrderIdFromUrl();

            if (!this.orderId) {
                this.showError('No order ID provided');
                return;
            }

            //
            if (!window.RW_Account) {
                console.error('RW_Account base library not loaded');
                this.showError('System error: Base library not loaded');
                return;
            }

            //
            this.loadOrderDetail();
        },

        /**
         *
         */
        getOrderIdFromUrl: function() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('order_id');
        },

        /**
         *
         */
        loadOrderDetail: async function() {
            try {
                const jwtToken = await window.RW_Account.getJwtTokenAsync();
                const apiBaseUrl = window.RW_Account.getApiBaseUrl();

                if (!jwtToken) {
                    this.showError('Authentication token not found');
                    return;
                }

                //
                this.showLoading();

                const response = await fetch(`${apiBaseUrl}/user/order-detail/${this.orderId}`, {
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
                    this.orderData = result.data.order;
                    this.renderOrderDetail(this.orderData);
                } else {
                    this.showError('Order not found');
                }

            } catch (err) {
                console.error('Failed to load order detail:', err);
                this.showError('Failed to load order details. Please try again.');
            }
        },

        /**
         *
         */
        renderOrderDetail: function(order) {
            const container = $('.rw-order-detail-tab');

            if (!container.length) {
                console.error('Order detail container not found');
                return;
            }

            //
            let html = this.buildOrderDetailHTML(order);

            //
            container.html(html);

            //
            this.bindEvents();
        },

        /**
         *
         */
        buildOrderDetailHTML: function(order) {
            const currency = order.currency || 'USD';
            const orderNumber = order.order_number || order.transaction_id;
            const statusClass = this.getStatusBadgeClass(order.status);
            const statusText = this.getStatusText(order.status);

            return `
        <div class="rw-order-detail-card">
            <div class="rw-detail-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">Order #${window.RW_Account.escapeHtml(orderNumber)}</h2>
                    <span class="badge ${statusClass}">${statusText}</span>
                </div>
            </div>

            <div class="rw-order-detail-sections">
                <!-- Items 块 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${this.renderItems(order.items, currency)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Method 块 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        ${this.renderPayments(order.payments)}
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>  
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-sm rw-payment-summary-table">
                                    <tr>
                                        <th>Subtotal:</th>  
                                        <td class="text-end">${currency} ${order.subtotal}</td>
                                    </tr>
                                    ${order.tax_amount > 0 ? `
                                    <tr>
                                        <th>Tax:</th>
                                        <td class="text-end">${currency} ${order.tax_amount}</td>
                                    </tr>
                                    ` : ''}
                                    ${order.discount_amount > 0 ? `
                                    <tr>
                                        <th>Discount:</th>
                                        <td class="text-end text-success">-${currency} ${order.discount_amount}</td>
                                    </tr>
                                    ` : ''}
                                    <tr class="fw-bold">
                                        <th>Total:</th>  
                                        <td class="text-end">${currency} ${order.total_amount}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                ${order.invoice_id && order.invoice_url ? this.renderInvoice(order) : ''}

                ${order.refunds && order.refunds.length > 0 ? this.renderRefunds(order.refunds, currency) : ''}
            </div>
        </div>
    `;
        },

        /**
         *
         */
        renderItems: function(items, currency) {
            if (!items || items.length === 0) {
                return '<tr><td colspan="4" class="text-center">No items found</td></tr>';
            }

            return items.map(item => {
                const unitPrice = (item.unit_price / 100).toFixed(2);
                const total = (item.total / 100).toFixed(2);

                let customData = null;
                if (item.custom_data) {
                    if (typeof item.custom_data === 'string') {
                        try {
                            customData = JSON.parse(item.custom_data);
                        } catch (e) {
                            customData = null;
                        }
                    } else {
                        customData = item.custom_data;
                    }
                }

                return `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                ${item.product_image_url ? `
                                    <img src="${item.product_image_url}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                ` : ''}
                                <div>
                                    <div>${window.RW_Account.escapeHtml(item.product_name)}</div>
                                    ${customData && customData.plugin_slug ? `
                                        <small class="text-muted">${window.RW_Account.escapeHtml(customData.plugin_slug)}</small>
                                    ` : ''}
                                </div>
                            </div>
                        </td>
                        <td>${item.quantity}</td>
                        <td>${currency} ${unitPrice}</td>
                        <td>${currency} ${total}</td>
                    </tr>
                `;
            }).join('');
        },

        /**
         *
         */
        renderPayments: function(payments) {
            if (!payments || payments.length === 0) {
                return '<p class="text-muted mb-0">No payment information available</p>';
            }

            return payments.map(payment => {
                const capturedDate = payment.captured_at || payment.created_at;
                const formattedDate = window.RW_Account.formatDate(capturedDate);
                const icon = payment.payment_type === 'card' ? '💳' : '🅿️';

                return `
            <div class="row align-items-center mb-3">
                <div class="col-auto">
                    <div class="payment-icon fs-2">${icon}</div>
                </div>
                <div class="col">
                    ${payment.payment_type === 'card' ? `
                        <div class="fw-bold">${payment.card_brand?.toUpperCase() || 'Card'} •••• ${payment.card_last4 || '****'}</div>
                        ${payment.card_expiry_month && payment.card_expiry_year ? `
                            <div class="text-muted small">Expires ${payment.card_expiry_month}/${payment.card_expiry_year}</div>
                        ` : ''}
                    ` : `
                        <div class="fw-bold">PayPal</div>
                        ${payment.paypal_email ? `
                            <div class="text-muted small">${window.RW_Account.escapeHtml(payment.paypal_email)}</div>
                        ` : ''}
                    `}
                    <div class="text-success small mt-1">
                        ✓ Captured on ${formattedDate}
                    </div>
                </div>
            </div>
        `;
            }).join('');
        },

        /**
         *
         */
        renderInvoice: function(order) {
            return `
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div>Invoice #${window.RW_Account.escapeHtml(order.invoice_id)}</div>
                                <small class="text-muted">Issued by Paddle</small>
                            </div>
                            <a href="${order.invoice_url}" target="_blank" class="btn btn-primary btn-sm">
                                📄 Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         *
         */
        renderRefunds: function(refunds, currency) {
            return `
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Refund Information</h5>
                    </div>
                    <div class="card-body">
                        ${refunds.map(refund => {
                const amount = (refund.amount / 100).toFixed(2);
                const refundDate = window.RW_Account.formatDate(refund.refunded_at || refund.created_at);

                return `
                                <div class="mb-3">
                                    <div class="fw-bold text-danger">Refunded: ${currency} ${amount}</div>
                                    ${refund.reason ? `<div>Reason: ${window.RW_Account.escapeHtml(refund.reason)}</div>` : ''}
                                    <div class="text-muted small">on ${refundDate}</div>
                                </div>
                            `;
            }).join('')}
                    </div>
                </div>
            `;
        },

        /**
         *
         */
        bindEvents: function() {
            $('.rw-copy-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const textToCopy = $(this).data('copy');
                if (textToCopy && window.RW_Account.copyToClipboard) {
                    window.RW_Account.copyToClipboard(textToCopy, this);
                }
            });
        },

        /**
         *
         */
        getStatusBadgeClass: function(status) {
            const map = {
                'paid': 'badge-success',
                'refunded': 'badge-danger',
                'pending': 'badge-warning',
                'cancelled': 'badge-secondary'
            };
            return map[status] || 'badge-secondary';
        },

        /**
         *
         */
        getStatusText: function(status) {
            const map = {
                'paid': 'Paid',
                'refunded': 'Refunded',
                'pending': 'Pending',
                'cancelled': 'Cancelled'
            };
            return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown');
        },

        /**
         *
         */
        showLoading: function() {
            const container = $('.rw-order-detail-tab');
            if (container.length) {
                container.html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading order details...</p>
                    </div>
                `);
            }
        },

        /**
         *
         */
        showError: function(message) {
            const container = $('.rw-order-detail-tab');
            if (container.length) {
                container.html(`
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> ${message}
                    </div>
                `);
            }

            if (window.RW_Account && window.RW_Account.showNotice) {
                window.RW_Account.showNotice(message, 'error');
            }
        }
    };

    RW_Order_Detail.init();

    //window.RW_Order_Detail = RW_Order_Detail;
});