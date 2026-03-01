jQuery(document).ready(function($) {
    'use strict';

    var RW_Account = {
        jwtToken: null,
        externalUuid: null,
        restNonce: null,
        apiBaseUrl: 'https://api.robertwp.com/api',

        init: function() {
            this.bindOrderTabEvents();
            this.bindLicenseTabEvents();
            this.bindTabSwitchEvents();

            this.getRestNonce();

            this.initUserCenter();
        },

        getRestNonce: function() {
            //
            if (window.rw_ajax && window.rw_ajax.rest_nonce) {
                this.restNonce = window.rw_ajax.rest_nonce;
                return true;
            }

            console.error('❌ REST Nonce not found');
            return false;
        },

        // 🔹
        initUserCenter: async function() {
            this.jwtToken = await this.fetchUserToken();
            if (!this.jwtToken) {
                console.warn('Failed to get JWT token for user.');
                return;
            }

            const urlParams = new URLSearchParams(window.location.search);
            const pageType = urlParams.get('type'); //

            if (pageType === 'my_licenses') {
                this.loadLicenses();
            } else if (pageType === 'my_orders') {
                this.loadOrders();
            } else {
            }
        },

        // 🔹
        fetchUserToken: async function() {
            try {
                if (!this.restNonce) {
                    console.error('REST Nonce not available. Cannot authenticate request.');
                    return null;
                }

                const response = await fetch('/wp-json/rw-user-sync/v1/token', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-WP-Nonce': this.restNonce,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        console.error('Authentication failed - user may not be logged in or nonce is invalid');
                    }
                    throw new Error(`Failed to get token: ${response.status}`);
                }

                const data = await response.json();
                if (data && data.data) {
                    this.externalUuid = data.data.external_uuid;
                    return data.data.token;
                } else {
                    throw new Error('Invalid response format');
                }
            } catch (err) {
                console.error('Error fetching user token:', err);
                return null;
            }
        },

        // 🔹
        loadLicenses: async function() {
            if (!this.jwtToken) return;
            const $licensesTab = $('.rw-licenses-tab');

            try {
                //
                //$licensesTab.html('<p class="rw-loading"><i class="fa fa-spinner fa-spin"></i> Loading licenses...</p>');
                const response = await fetch(`${this.apiBaseUrl}/user/licenses`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.jwtToken,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success') {
                    $licensesTab.html(this.renderLicenses(data.data.licenses));
                } else {
                    this.showNotice('Failed to load licenses: ' + (data.data?.message || 'Unknown'), 'error');
                }
            } catch (err) {
                console.error('Licenses loading error:', err);
                this.showNotice('Network error loading licenses', 'error');
                $licensesTab.html('<p class="rw-error">Failed to load licenses. Please try again.</p>');
            }
        },

        // 🔹
        renderLicenses: function(licenses) {
            if (!licenses || licenses.length === 0) {
                return '<p class="rw-empty-state">No licenses found.</p>';
            }

            let html = '<div class="rw-licenses-grid">';

            licenses.forEach(l => {
                //
                const statusClass = l.status === 'active' ? 'rw-license-status-active' :
                    (l.status === 'expired' ? 'rw-license-status-expired' : 'rw-license-status-suspended');
                const statusText = l.status === 'active' ? 'Active' :
                    (l.status === 'expired' ? 'Expired' : 'Suspended');

                //
                const purchaseDate = l.purchased_at ? new Date(l.purchased_at).toLocaleDateString() : 'N/A';
                const expiresAt = l.expires_at ? new Date(l.expires_at).toLocaleDateString() : 'Never';

                //
                const sitesText = l.site_limit === '0' || l.site_limit === '-1' ? 'Unlimited' : l.site_limit + ' site' + (l.site_limit > 1 ? 's' : '');

                html += `
            <div class="rw-license-card">
                
                <div class="rw-license-header">
                    <div class="rw-plugin-info">
                        <h3 class="rw-plugin-name">${this.escapeHtml(l.plugin_name)}</h3>
                        <span class="rw-plugin-version">v${this.escapeHtml(l.version || '1.0.0')}</span>
                    </div>
                    <span class="rw-license-status ${statusClass}">${statusText}</span>
                </div>
                
                
                <div class="rw-license-body">
                    
                    <div class="rw-license-key-section">
                        <span class="rw-field-label">License Key</span>
                        <div class="rw-license-key-wrapper">
                            <span class="rw-license-key-display">${this.escapeHtml(l.license_key)}</span>
                            <button class="rw-copy-btn" data-key="${this.escapeHtml(l.license_key)}">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="rw-license-details-grid">
                        <div class="rw-license-detail-item">
                            <span class="rw-detail-label">Plan</span>
                            <span class="rw-detail-value">${this.escapeHtml(l.plan_type || 'lifetime')}</span>
                        </div>
                        <div class="rw-license-detail-item">
                            <span class="rw-detail-label">Sites</span>
                            <span class="rw-detail-value ${l.site_limit === 1 ? 'rw-active' : ''}">${sitesText}</span>
                        </div>
                        <div class="rw-license-detail-item">
                            <span class="rw-detail-label">Expires</span>
                            <span class="rw-detail-value ${l.expires_at ? 'rw-expired' : ''}">${expiresAt}</span>
                        </div>
                    </div>
                    
                    <div class="rw-license-meta">
                        <div class="rw-meta-toggle" onclick="jQuery(this).next().slideToggle();jQuery(this).find('.rw-toggle-icon').toggleClass('rotated')">
                            <span>More details</span>
                            <span class="rw-toggle-icon">▼</span>
                        </div>
                        <div class="rw-meta-content" style="display: none;">
                            <div class="rw-meta-row">
                                <span class="rw-meta-label">Transaction ID:</span>
                                <span class="rw-meta-value rw-order-id">${this.escapeHtml(l.transaction_id || 'N/A')}</span>
                            </div>
                            <div class="rw-meta-row">
                                <span class="rw-meta-label">Purchase Date:</span>
                                <span class="rw-meta-value">${purchaseDate}</span>
                            </div>
                            <div class="rw-meta-row">
                                <span class="rw-meta-label">Email:</span>
                                <span class="rw-meta-value">${this.escapeHtml(l.email || 'N/A')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            html += '</div>';
            return html;
        },

        // 🔹
        loadOrders: async function() {
            if (!this.jwtToken) return;
            const $ordersTab = $('.rw-orders-tab');

            try {
                //
                //$ordersTab.html('<p class="rw-loading"><i class="fa fa-spinner fa-spin"></i> Loading orders...</p>');

                const response = await fetch(`${this.apiBaseUrl}/user/orders`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.jwtToken,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success') {
                    $ordersTab.html(this.renderOrders(data.data.orders));
                } else {
                    this.showNotice('Failed to load orders: ' + (data.data?.message || 'Unknown'), 'error');
                }
            } catch (err) {
                console.error('Orders loading error:', err);
                this.showNotice('Network error loading orders', 'error');
                $ordersTab.html('<p class="rw-error">Failed to load orders. Please try again.</p>');
            }
        },

        // 🔹
        renderOrders: function(orders) {
            if (!orders || orders.length === 0) {
                return '<p class="rw-empty-state">No orders found.</p>';
            }

            let html = '<div class="rw-orders-grid">';

            orders.forEach(o => {

                const statusClass = o.status === 'paid' ? 'rw-order-status-paid' :
                    (o.status === 'refunded' ? 'rw-order-status-refunded' : 'rw-order-status-pending');
                const statusText = o.status === 'paid' ? 'Paid' :
                    (o.status === 'refunded' ? 'Refunded' : 'Pending');


                const currency = o.currency || 'USD';


                const orderDate = o.created_at ? new Date(o.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : 'N/A';


                const hasRefund = o.refunded_amount && parseFloat(o.refunded_amount) > 0;

                html += `
            <div class="rw-order-card">
                
                <div class="rw-order-header">
                    <div class="rw-order-title-section">
                        <span class="rw-order-label">Order</span>
                        <span class="rw-order-id">${this.escapeHtml(o.transaction_id || 'N/A')}</span>
                    </div>
                    <span class="rw-order-status ${statusClass}">${statusText}</span>
                </div>
                
                
                <div class="rw-order-body">
                    
                    <div class="rw-order-details-grid">
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Total Amount</span>
                            <span class="rw-detail-value rw-order-total">${currency} ${o.total_amount || '0.00'}</span>
                        </div>
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Subtotal</span>
                            <span class="rw-detail-value">${currency} ${o.subtotal || '0.00'}</span>
                        </div>
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Tax</span>
                            <span class="rw-detail-value">${currency} ${o.tax_amount || '0.00'}</span>
                        </div>
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Fee</span>
                            <span class="rw-detail-value">${currency} ${o.fee_amount || '0.00'}</span>
                        </div>
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Earnings</span>
                            <span class="rw-detail-value rw-order-earnings">${currency} ${o.earnings || '0.00'}</span>
                        </div>
                        <div class="rw-order-detail-item">
                            <span class="rw-detail-label">Date</span>
                            <span class="rw-detail-value">${orderDate}</span>
                        </div>
                    </div>
                    
                    
                    ${hasRefund ? `
                    <div class="rw-order-refund-section">
                        <div class="rw-refund-info">
                            <span class="rw-refund-label">Refunded:</span>
                            <span class="rw-refund-amount">${currency} ${o.refunded_amount}</span>
                            ${o.refunded_at ? `<span class="rw-refund-date">on ${new Date(o.refunded_at).toLocaleDateString()}</span>` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
            });

            html += '</div>';
            return html;
        },

        //
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        },

        bindOrderTabEvents: function() {
            var $ordersTab = $('.rw-orders-tab');
            $ordersTab.on('click', '.rw-view-license-btn', function(e) {
                e.preventDefault();
                var $licenseTabLink = $('a[href="#uwp-profile-rw_my_licenses"]');
                if ($licenseTabLink.length) $licenseTabLink.trigger('click');
            });

            $ordersTab.on('click', '.rw-page-link', function(e) {
                e.preventDefault();
                var $this = $(this);
                var page = $this.data('page');
                $this.text('Loading...').css('opacity', '0.7');
                $.ajax({
                    url: rw_ajax.ajax_url,
                    type: 'POST',
                    data: { action: 'load_orders_page', page: page, user_id: rw_ajax.user_id },
                    success: function(response) {
                        if (response.success) $ordersTab.html(response.data.html);
                    },
                    error: function() { RW_Account.showNotice('Network error. Please try again.', 'error'); },
                    complete: function() { $this.text('Page ' + page).css('opacity', '1'); }
                });
            });
        },

        bindLicenseTabEvents: function() {
            var $licensesTab = $('.rw-licenses-tab');
            $licensesTab.on('click', '.rw-copy-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var licenseKey = $btn.data('key');
                if (!licenseKey) return;
                RW_Account.copyToClipboard(licenseKey, $btn);
            });
        },

        bindTabSwitchEvents: function() {
            $('a[href="#uwp-profile-rw_my_licenses"]').on('shown.bs.tab', function(e){});
            $('a[href="#uwp-profile-rw_my_orders"]').on('shown.bs.tab', function(e){});
        },

        copyToClipboard: function(text, $button) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(()=>RW_Account.showCopySuccess($button))
                    .catch(()=>RW_Account.fallbackCopy(text,$button));
            } else { RW_Account.fallbackCopy(text,$button); }
        },

        //
        fallbackCopy: function(text, $button) {
            var $textarea = $('<textarea>');
            $textarea.val(text).css({
                position: 'fixed',
                opacity: 0
            }).appendTo('body');

            $textarea.select();

            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    RW_Account.showCopySuccess($button);
                } else {
                    alert('Copy failed. Please select and copy manually.');
                }
            } catch (err) {
                alert('Copy failed. Please select and copy manually.');
            }

            $textarea.remove();
        },

        //
        showCopySuccess: function($button) {
            var originalText = $button.text();
            var originalBg = $button.css('background');

            $button.text('✅ Copied!')
                .css('background', '#28a745')
                .prop('disabled', true);

            setTimeout(function() {
                $button.text(originalText)
                    .css('background', originalBg)
                    .prop('disabled', false);
            }, 2000);
        },

        //
        showNotice: function(message, type) {
            // 移除之前的通知（避免堆积）
            $('.rw-notice').remove();

            var $notice = $('<div class="rw-notice rw-notice-' + type + '">' + message + '</div>');

            // 根据屏幕宽度决定位置
            var isMobile = window.innerWidth < 768;

            $notice.css({
                position: 'fixed',
                // 移动端显示在顶部中央，PC端显示在右上角偏下一点
                top: isMobile ? '20px' : '80px',
                left: isMobile ? '50%' : 'auto',
                right: isMobile ? 'auto' : '30px',
                transform: isMobile ? 'translateX(-50%)' : 'none',
                padding: '14px 25px',
                background: type === 'error' ? '#f8d7da' : '#d4edda',
                color: type === 'error' ? '#721c24' : '#155724',
                border: '1px solid ' + (type === 'error' ? '#f5c6cb' : '#c3e6cb'),
                borderRadius: isMobile ? '50px' : '8px', // 移动端更圆润
                zIndex: 9999,
                boxShadow: '0 4px 15px rgba(0,0,0,0.15)',
                fontSize: '15px',
                fontWeight: '500',
                maxWidth: isMobile ? '90%' : '400px',
                textAlign: 'center',
                cursor: 'pointer', // 提示可点击关闭
                animation: 'fadeInDown 0.3s ease'
            });

            $('body').append($notice);

            // 点击可立即关闭
            $notice.click(function() {
                $(this).fadeOut(200, function() {
                    $(this).remove();
                });
            });

            // 根据消息类型决定显示时间
            var displayTime = type === 'error' ? 6000 : 4000; // 错误6秒，成功4秒

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, displayTime);
        }

    };

    //
    RW_Account.init();

    //
    window.RW_Account = { copyLicenseKey: function(key) { RW_Account.copyToClipboard(key, $('.rw-copy-btn').first()); } };
});