jQuery(document).ready(function($) {
    'use strict';

    // 定义命名空间，避免全局污染
    var RW_Account = {

        // 初始化所有功能
        init: function() {
            this.bindOrderTabEvents();
            this.bindLicenseTabEvents();
            this.bindTabSwitchEvents();
        },

        // 订单标签页事件
        bindOrderTabEvents: function() {
            var $ordersTab = $('.rw-orders-tab');

            // 点击查看授权按钮（切换到授权标签页）
            $ordersTab.on('click', '.rw-view-license-btn', function(e) {
                e.preventDefault();

                // 获取授权标签页的链接并点击
                var $licenseTabLink = $('a[href="#uwp-profile-rw_my_licenses"]');

                if ($licenseTabLink.length) {
                    $licenseTabLink.trigger('click');

                    // 可选：显示提示信息
                    RW_Account.showNotice('Switched to Licenses tab', 'info');
                } else {
                    console.warn('License tab link not found');
                }
            });

            // 订单分页
            $ordersTab.on('click', '.rw-page-link', function(e) {
                e.preventDefault();
                var $this = $(this);
                var page = $this.data('page');

                // 显示加载状态
                $this.text('Loading...').css('opacity', '0.7');

                // 通过 AJAX 加载下一页订单
                $.ajax({
                    url: rw_ajax.ajax_url, // 需要先在 wp_localize_script 中定义
                    type: 'POST',
                    data: {
                        action: 'load_orders_page',
                        page: page,
                        user_id: rw_ajax.user_id
                    },
                    success: function(response) {
                        if (response.success) {
                            $ordersTab.html(response.data.html);
                        } else {
                            RW_Account.showNotice('Failed to load orders: ' + response.data.message, 'error');
                        }
                    },
                    error: function() {
                        RW_Account.showNotice('Network error. Please try again.', 'error');
                    },
                    complete: function() {
                        $this.text('Page ' + page).css('opacity', '1');
                    }
                });
            });
        },

        // 授权标签页事件
        bindLicenseTabEvents: function() {
            var $licensesTab = $('.rw-licenses-tab');

            // 复制授权密钥（使用事件委托）
            $licensesTab.on('click', '.rw-copy-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var licenseKey = $btn.data('key');

                if (!licenseKey) {
                    RW_Account.showNotice('License key not found', 'error');
                    return;
                }

                RW_Account.copyToClipboard(licenseKey, $btn);
            });

            // 下载按钮
            $licensesTab.on('click', '.rw-download-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var licenseKey = $btn.data('key');

                // 触发下载
                RW_Account.downloadLicense(licenseKey, $btn);
            });

            // 授权列表分页
            $licensesTab.on('click', '.rw-page-link', function(e) {
                e.preventDefault();
                var $this = $(this);
                var page = $this.data('page');

                $this.text('Loading...').css('opacity', '0.7');

                $.ajax({
                    url: rw_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'load_licenses_page',
                        page: page,
                        user_id: rw_ajax.user_id
                    },
                    success: function(response) {
                        if (response.success) {
                            $licensesTab.html(response.data.html);
                        } else {
                            RW_Account.showNotice('Failed to load licenses: ' + response.data.message, 'error');
                        }
                    },
                    error: function() {
                        RW_Account.showNotice('Network error. Please try again.', 'error');
                    },
                    complete: function() {
                        $this.text('Page ' + page).css('opacity', '1');
                    }
                });
            });
        },

        // 标签页切换事件
        bindTabSwitchEvents: function() {
            // 当切换到授权标签页时
            $('a[href="#uwp-profile-rw_my_licenses"]').on('shown.bs.tab', function(e) {
                console.log('Switched to licenses tab');
                // 可以在这里执行一些初始化操作
            });

            // 当切换到订单标签页时
            $('a[href="#uwp-profile-rw_my_orders"]').on('shown.bs.tab', function(e) {
                console.log('Switched to orders tab');
                // 可以在这里执行一些初始化操作
            });
        },

        // 复制到剪贴板
        copyToClipboard: function(text, $button) {
            // 使用现代的 Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    RW_Account.showCopySuccess($button);
                }).catch(function(err) {
                    RW_Account.fallbackCopy(text, $button);
                });
            } else {
                // 降级方案
                RW_Account.fallbackCopy(text, $button);
            }
        },

        // 降级复制方法（使用 execCommand）
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

        // 显示复制成功效果
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

        // 下载授权文件
        downloadLicense: function(licenseKey, $button) {
            var originalText = $button.text();

            $button.text('Downloading...').css('opacity', '0.7').prop('disabled', true);

            $.ajax({
                url: rw_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'download_license',
                    license_key: licenseKey,
                    user_id: rw_ajax.user_id
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    var filename = '';
                    var disposition = xhr.getResponseHeader('Content-Disposition');

                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }

                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename || 'license.zip';
                    link.click();

                    RW_Account.showNotice('Download started', 'success');
                },
                error: function() {
                    RW_Account.showNotice('Download failed. Please try again.', 'error');
                },
                complete: function() {
                    $button.text(originalText).css('opacity', '1').prop('disabled', false);
                }
            });
        },

        // 显示通知
        showNotice: function(message, type) {
            var $notice = $('<div class="rw-notice rw-notice-' + type + '">' + message + '</div>');

            $notice.css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '12px 20px',
                background: type === 'error' ? '#f8d7da' : '#d4edda',
                color: type === 'error' ? '#721c24' : '#155724',
                border: '1px solid ' + (type === 'error' ? '#f5c6cb' : '#c3e6cb'),
                borderRadius: '4px',
                zIndex: 9999,
                boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
            });

            $('body').append($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // 初始化
    RW_Account.init();
});

// 如果需要全局可访问，可以暴露一个安全的方法
window.RW_Account = {
    copyLicenseKey: function(key) {
        jQuery(document).ready(function($) {
            RW_Account.copyToClipboard(key, $('.rw-copy-btn').first());
        });
    }
};