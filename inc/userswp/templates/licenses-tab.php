<?php
/**
 * 授权列表模板
 *
 * 显示当前用户的授权
 *
 * @package Astra-Child
 * @subpackage UsersWP
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$user = get_userdata($user_id);
$api = new RW_API_Client();

// 获取当前页码（用于分页）
$current_page = isset($_GET['license_page']) ? max(1, intval($_GET['license_page'])) : 1;

// 获取用户授权数据（假设 API 支持分页参数）
$data = $api->get_user_licenses($user_id, $user->user_email, $current_page);
$licenses = $data['licenses'] ?? [];
$error = $data['error'] ?? '';
$total_pages = $data['total_pages'] ?? 1;
$current_page = $data['current_page'] ?? $current_page;
?>

<div class="uwp-profile-tab-content rw-licenses-tab">
    <?php if ($error): ?>
        <!-- 错误状态：API调用失败 -->
        <div class="rw-error-notice">
            <p class="rw-error-title">⚠️ <?php _e('Unable to load licenses', 'astra-child'); ?></p>
            <p class="rw-error-message"><?php echo esc_html($error); ?></p>
            <p class="rw-error-help">
                <?php _e('Please try again later or contact support if the problem persists.', 'astra-child'); ?>
                <a href="<?php echo esc_url(home_url('/support')); ?>" class="rw-support-link">
                    <?php _e('Contact Support →', 'astra-child'); ?>
                </a>
            </p>
        </div>

    <?php elseif (empty($licenses)): ?>
        <!-- 空状态：用户暂无授权 -->
        <div class="rw-empty-state">
            <span class="rw-empty-icon">🔑</span>
            <h3 class="rw-empty-title"><?php _e('No Licenses Found', 'astra-child'); ?></h3>
            <p class="rw-empty-message">
                <?php _e('You haven\'t purchased any products yet. Browse our products to get started!', 'astra-child'); ?>
            </p>
            <a href="<?php echo esc_url(home_url('/products')); ?>" class="rw-empty-button">
                <?php _e('Browse Products →', 'astra-child'); ?>
            </a>
        </div>

    <?php else: ?>
        <!-- 成功状态：显示授权列表 -->
        <div class="rw-licenses-list">
            <?php foreach ($licenses as $license): ?>
                <div class="rw-license-card" data-license-key="<?php echo esc_attr($license['license_key']); ?>">

                    <!-- 授权头部 -->
                    <div class="rw-license-header">
                        <h4 class="rw-license-title"><?php echo esc_html($license['plugin_name']); ?></h4>
                        <span class="rw-license-status rw-license-status-<?php echo esc_attr($license['status'] ?? 'active'); ?>">
                            <?php echo isset($license['status']) ? ucfirst($license['status']) : 'Active'; ?>
                        </span>
                    </div>

                    <!-- 授权内容 -->
                    <div class="rw-license-body">
                        <!-- 授权密钥 -->
                        <div class="rw-license-key-section">
                            <label class="rw-field-label"><?php _e('License Key', 'astra-child'); ?></label>
                            <div class="rw-license-key-wrapper">
                                <code class="rw-license-key-display"><?php echo esc_html($license['license_key']); ?></code>
                                <button type="button"
                                        class="rw-copy-btn"
                                        data-key="<?php echo esc_attr($license['license_key']); ?>"
                                        data-action="copy-license">
                                    <span class="rw-btn-icon">📋</span>
                                    <span class="rw-btn-text"><?php _e('Copy', 'astra-child'); ?></span>
                                </button>
                            </div>
                        </div>

                        <!-- 授权详情网格 -->
                        <div class="rw-license-details-grid">

                            <?php if (!empty($license['purchase_date'])): ?>
                                <div class="rw-license-detail-item">
                                    <span class="rw-detail-label">📅 <?php _e('Purchase Date', 'astra-child'); ?></span>
                                    <span class="rw-detail-value">
                                    <?php echo date_i18n(get_option('date_format'), strtotime($license['purchase_date'])); ?>
                                </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($license['expiry_date'])): ?>
                                <div class="rw-license-detail-item">
                                    <span class="rw-detail-label">⏰ <?php _e('Expiry Date', 'astra-child'); ?></span>
                                    <span class="rw-detail-value <?php echo (strtotime($license['expiry_date']) < time()) ? 'rw-expired' : 'rw-active'; ?>">
                                    <?php echo date_i18n(get_option('date_format'), strtotime($license['expiry_date'])); ?>
                                </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($license['transaction_id'])): ?>
                                <div class="rw-license-detail-item">
                                    <span class="rw-detail-label">🧾 <?php _e('Order #', 'astra-child'); ?></span>
                                    <span class="rw-detail-value rw-order-id">
                                    <?php echo esc_html(substr($license['transaction_id'], 0, 8)); ?>...
                                </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($license['activations_left'])): ?>
                                <div class="rw-license-detail-item">
                                    <span class="rw-detail-label">💻 <?php _e('Activations Left', 'astra-child'); ?></span>
                                    <span class="rw-detail-value"><?php echo intval($license['activations_left']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 授权底部操作 -->
                    <div class="rw-license-footer">
                        <div class="rw-license-actions">
                            <button type="button"
                                    class="rw-download-btn"
                                    data-key="<?php echo esc_attr($license['license_key']); ?>"
                                    data-action="download-license">
                                <span class="rw-btn-icon">⬇️</span>
                                <span class="rw-btn-text"><?php _e('Download', 'astra-child'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- 分页 -->
            <?php if ($total_pages > 1): ?>
                <div class="rw-pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?php echo esc_url(add_query_arg('license_page', $i)); ?>"
                           class="rw-page-link <?php echo $i == $current_page ? 'rw-current-page' : ''; ?>"
                           data-page="<?php echo $i; ?>"
                           data-action="load-licenses-page">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>