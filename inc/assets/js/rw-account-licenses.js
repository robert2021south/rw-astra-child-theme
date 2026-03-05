jQuery(document).ready(function($) {
    'use strict';

    const RW_Licenses = {
        init: function () {

            $(document).on('rw_account:load_licenses', () => {
                this.loadLicenses();
            });
            this.bindCopyBtnClickEvents();

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') === 'my_licenses' && window.RW_Account && window.RW_Account.getJwtToken()) {
                this.loadLicenses();
            }
        },

        // 🔹
        loadLicenses: async function () {
            if (!window.RW_Account || !window.RW_Account.getJwtToken()) return;

            const jwtToken = window.RW_Account.getJwtToken();
            const apiBaseUrl = window.RW_Account.getApiBaseUrl();
            const $licensesTab = $('.rw-licenses-tab');

            try {
                //
                const response = await fetch(`${apiBaseUrl}/user/licenses`, {
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
                    $licensesTab.html(this.renderLicenses(result.data.licenses));
                } else {
                    window.RW_Account.showNotice('Failed to load licenses: ' + (result.data?.message || 'Unknown'), 'error');
                }
            } catch (err) {
                console.error('Licenses loading error:', err);
                window.RW_Account.showNotice('Network error loading licenses', 'error');
                $licensesTab.html('<p class="rw-error">Failed to load licenses. Please try again.</p>');
            }
        },

        // 🔹
        renderLicenses: function (licenses) {
            if (!licenses || licenses.length === 0) {
                return '<p class="rw-empty-state">No licenses found.</p>';
            }

            let html = '<div class="rw-licenses-grid">';

            licenses.forEach(l => {
                const statusClass = l.status === 'active' ? 'rw-license-status-active' :
                    (l.status === 'expired' ? 'rw-license-status-expired' : 'rw-license-status-suspended');
                const statusText = l.status === 'active' ? 'Active' :
                    (l.status === 'expired' ? 'Expired' : 'Suspended');

                const purchaseDate =  window.RW_Account.formatDate(l.purchased_at);
                const expiresAt = l.expires_at ? window.RW_Account.formatDate(l.expires_at) : 'Never';

                const sitesText = l.site_limit === '0' || l.site_limit === '-1' ? 'Unlimited' : l.site_limit + ' site' + (l.site_limit > 1 ? 's' : '');

                html += `
            <div class="rw-license-card">
                
                <div class="rw-license-header">
                    <div class="rw-plugin-info">
                        <h3 class="rw-plugin-name">${window.RW_Account.escapeHtml(l.plugin_name)}</h3>
                        <span class="rw-plugin-version">v${window.RW_Account.escapeHtml(l.version || '1.0.0')}</span>
                    </div>
                    <span class="rw-license-status ${statusClass}">${statusText}</span>
                </div>
                
                
                <div class="rw-license-body">
                    
                    <div class="rw-license-key-section">
                        <span class="rw-field-label">License Key</span>
                        <div class="rw-license-key-wrapper">
                            <span class="rw-license-key-display">${window.RW_Account.escapeHtml(l.license_key)}</span>
                            <button class="rw-copy-btn" data-key="${window.RW_Account.escapeHtml(l.license_key)}">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="rw-license-details-grid">
                        <div class="rw-license-detail-item">
                            <span class="rw-detail-label">Plan</span>
                            <span class="rw-detail-value">${window.RW_Account.escapeHtml(l.plan_type || 'lifetime')}</span>
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
                                <span class="rw-meta-value rw-order-id">${window.RW_Account.escapeHtml(l.transaction_id || 'N/A')}</span>
                            </div>
                            <div class="rw-meta-row">
                                <span class="rw-meta-label">Purchase Date:</span>
                                <span class="rw-meta-value">${purchaseDate}</span>
                            </div>
                            <div class="rw-meta-row">
                                <span class="rw-meta-label">Email:</span>
                                <span class="rw-meta-value">${window.RW_Account.escapeHtml(l.email || 'N/A')}</span>
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

        //
        bindCopyBtnClickEvents: function () {
            const $licensesTab = $('.rw-licenses-tab');
            $licensesTab.on('click', '.rw-copy-btn', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const licenseKey = $btn.data('key');
                if (!licenseKey) return;
                this.copyToClipboard(licenseKey, $btn);
            });
        },

        //
        copyToClipboard: function (text, $button) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => this.showCopySuccess($button))
                    .catch(() => this.fallbackCopy(text, $button));
            } else {
                this.fallbackCopy(text, $button);
            }
        },

        //
        fallbackCopy: function (text, $button) {
            const $textarea = $('<textarea>');
            $textarea.val(text).css({
                position: 'fixed',
                opacity: 0
            }).appendTo('body');

            $textarea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showCopySuccess($button);
                } else {
                    alert('Copy failed. Please select and copy manually.');
                }
            } catch (err) {
                alert('Copy failed. Please select and copy manually.');
            }

            $textarea.remove();
        },

        //
        showCopySuccess: function ($button) {
            const originalText = $button.text();
            const originalBg = $button.css('background');

            $button.text('✅ Copied!')
                .css('background', '#28a745')
                .prop('disabled', true);

            setTimeout(function () {
                $button.text(originalText)
                    .css('background', originalBg)
                    .prop('disabled', false);
            }, 2000);
        },

    };

    //
    RW_Licenses.init();
});