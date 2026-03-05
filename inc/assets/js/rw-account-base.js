jQuery(document).ready(function($) {
    'use strict';

    const RW_Account_Core = {
        jwtToken: null,
        restNonce: null,
        apiBaseUrl: 'https://api.robertwp.com/api',
        accountUrl: '/account/',
        tokenPromise: null, //

        init: function () {
            this.getRestNonce();
            //
            if (this.requiresToken()) {
                this.ensureToken();
            }

            //
            this.initUserCenter();
        },

        getRestNonce: function () {
            if (window.rw_ajax && window.rw_ajax.rest_nonce) {
                this.restNonce = window.rw_ajax.rest_nonce;
                return true;
            }
            return false;
        },

        /**
         * 确保 token 可用（可以在任何页面调用）
         */
        ensureToken: async function() {
            if (this.jwtToken) {
                return this.jwtToken;
            }

            if (!this.tokenPromise) {
                this.tokenPromise = this.fetchUserToken();
            }

            this.jwtToken = await this.tokenPromise;
            return this.jwtToken;
        },

        /**
         * 判断当前页面是否需要 token
         */
        requiresToken: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const pageType = urlParams.get('type');

            //
            if (pageType === 'my_licenses' || pageType === 'my_orders' || pageType === 'order_detail') {
                return true;
            }

            //
            //if (window.location.pathname.includes('/order-detail')) {
            //    return true;
            //}

            //

            return false;
        },

        // 🔹
        initUserCenter: async function () {
            // 确保 token 已获取
            await this.ensureToken();

            const urlParams = new URLSearchParams(window.location.search);
            const pageType = urlParams.get('type');

            if (pageType === 'my_licenses') {
                $(document).trigger('rw_account:load_licenses');
            } else if (pageType === 'my_orders') {
                $(document).trigger('rw_account:load_orders');
            }
            // ✅ 其他页面（如订单详情）不需要触发事件，但 token 已经可用
        },

        // 🔹
        fetchUserToken: async function () {
            try {
                if (!this.restNonce) {
                    console.warn('No rest nonce available');
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
                    throw new Error(`Failed to get token: ${response.status}`);
                }

                const result = await response.json();
                if (result && result.data) {
                    return result.data.token;
                } else {
                    throw new Error('Invalid response format');
                }
            } catch (err) {
                console.error('Token fetch error:', err);
                return null;
            }
        },

        /**
         * 获取 token 的同步方法（如果 token 还没准备好，返回 null）
         */
        getJwtToken: function () {
            return this.jwtToken;
        },

        /**
         * 获取 token 的异步方法（等待 token 准备好）
         */
        getJwtTokenAsync: async function() {
            if (this.jwtToken) {
                return this.jwtToken;
            }
            return await this.ensureToken();
        },

        //
        escapeHtml: function (text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        },

        //
        showNotice: function (message, type) {
            $('.rw-notice').remove();

            const $notice = $('<div class="rw-notice rw-notice-' + type + '">' + message + '</div>');

            const isMobile = window.innerWidth < 768;

            $notice.css({
                position: 'fixed',
                top: isMobile ? '20px' : '80px',
                left: isMobile ? '50%' : 'auto',
                right: isMobile ? 'auto' : '30px',
                transform: isMobile ? 'translateX(-50%)' : 'none',
                padding: '14px 25px',
                background: type === 'error' ? '#f8d7da' : '#d4edda',
                color: type === 'error' ? '#721c24' : '#155724',
                border: '1px solid ' + (type === 'error' ? '#f5c6cb' : '#c3e6cb'),
                borderRadius: isMobile ? '50px' : '8px',
                zIndex: 9999,
                boxShadow: '0 4px 15px rgba(0,0,0,0.15)',
                fontSize: '15px',
                fontWeight: '500',
                maxWidth: isMobile ? '90%' : '400px',
                textAlign: 'center',
                cursor: 'pointer',
                animation: 'fadeInDown 0.3s ease'
            });

            $('body').append($notice);

            $notice.click(function () {
                $(this).fadeOut(200, function () {
                    $(this).remove();
                });
            });

            const displayTime = type === 'error' ? 6000 : 4000;

            setTimeout(function () {
                $notice.fadeOut(300, function () {
                    $(this).remove();
                });
            }, displayTime);
        },

        //
        getApiBaseUrl: function () {
            return this.apiBaseUrl;
        },

        getAccountUrl: function() {
            return this.accountUrl;
        },

        // 🔹
        formatDate: function(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            } catch (e) {
                return 'Invalid date';
            }
        },
    };

    //
    RW_Account_Core.init();

    //
    window.RW_Account = RW_Account_Core;
});