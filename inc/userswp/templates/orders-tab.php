<?php
/**
 * 订单列表模板
 *
 * 显示当前用户的订单历史
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

// 获取当前页码
$current_page = isset($_GET['order_page']) ? max(1, intval($_GET['order_page'])) : 1;

// 获取用户订单数据
$data = $api->get_user_orders($user_id, $user->user_email, $current_page);
$orders = $data['orders'] ?? [];
$error = $data['error'] ?? '';
$total_pages = $data['total_pages'] ?? 1;
$current_page = $data['current_page'] ?? $current_page;
?>

<div class="uwp-profile-tab-content rw-orders-tab">
    <?php if ($error): ?>
        <!-- 错误状态 -->
        <div class="rw-error-notice">
            <p class="rw-error-title">⚠️ <?php _e('Unable to load orders', 'astra-child'); ?></p>
            <p class="rw-error-message"><?php echo esc_html($error); ?></p>
            <p class="rw-error-help">
                <?php _e('Please try again later or contact support if the problem persists.', 'astra-child'); ?>
                <a href="<?php echo esc_url(home_url('/support')); ?>" class="rw-support-link">
                    <?php _e('Contact Support →', 'astra-child'); ?>
                </a>
            </p>
        </div>

    <?php elseif (empty($orders)): ?>
        <!-- 空状态 -->
        <div class="rw-empty-state">
            <span class="rw-empty-icon">🛒</span>
            <h3 class="rw-empty-title"><?php _e('No Orders Found', 'astra-child'); ?></h3>
            <p class="rw-empty-message">
                <?php _e('You haven\'t placed any orders yet. Browse our products to get started!', 'astra-child'); ?>
            </p>
            <a href="<?php echo esc_url(home_url('/plugins')); ?>" class="rw-empty-button">
                <?php _e('Browse Products →', 'astra-child'); ?>
            </a>
        </div>

    <?php else: ?>
        <!-- 显示订单列表 -->
        <div class="rw-orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="rw-order-card" data-order-id="<?php echo esc_attr($order['transaction_id']); ?>">

                    <!-- 订单头部 -->
                    <div class="rw-order-header">
                        <div class="rw-order-header-left">
                            <strong class="rw-order-label"><?php _e('Order', 'astra-child'); ?> #</strong>
                            <code class="rw-order-id"><?php echo esc_html($order['transaction_id']); ?></code>
                        </div>
                        <div class="rw-order-header-right">
                            <span class="rw-order-status rw-order-status-<?php echo esc_attr($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 订单内容 -->
                    <div class="rw-order-body">
                        <table class="rw-order-items-table">
                            <thead>
                            <tr>
                                <th class="rw-col-product"><?php _e('Product', 'astra-child'); ?></th>
                                <th class="rw-col-quantity"><?php _e('Qty', 'astra-child'); ?></th>
                                <th class="rw-col-price"><?php _e('Price', 'astra-child'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $items = isset($order['items']) ? $order['items'] : [['name' => $order['plugin_name'], 'quantity' => 1, 'price' => $order['amount']]];
                            foreach ($items as $item):
                                ?>
                                <tr>
                                    <td class="rw-col-product"><?php echo esc_html($item['name'] ?? $order['plugin_name']); ?></td>
                                    <td class="rw-col-quantity"><?php echo isset($item['quantity']) ? intval($item['quantity']) : '1'; ?></td>
                                    <td class="rw-col-price">$<?php echo number_format(($item['price'] ?? $order['amount']) / 100, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr class="rw-order-total">
                                <th colspan="2" class="rw-total-label"><?php _e('Total:', 'astra-child'); ?></th>
                                <td class="rw-total-value">$<?php echo number_format($order['amount'] / 100, 2); ?></td>
                            </tr>
                            <?php if (!empty($order['refunded_amount'])): ?>
                                <tr class="rw-order-refund">
                                    <th colspan="2" class="rw-refund-label"><?php _e('Refunded:', 'astra-child'); ?></th>
                                    <td class="rw-refund-value">-$<?php echo number_format($order['refunded_amount'] / 100, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>

                    <!-- 订单底部 -->
                    <div class="rw-order-footer">
                        <div class="rw-order-meta">
                            <span class="rw-order-date">
                                📅 <?php echo date_i18n(get_option('date_format'), strtotime($order['created_at'])); ?>
                            </span>
                            <span class="rw-order-payment">
                                💳 <?php _e('Payment:', 'astra-child'); ?> Paddle
                            </span>
                        </div>
                        <div class="rw-order-actions">
                            <?php if ($order['status'] === 'paid'): ?>
                                <button type="button"
                                        class="rw-view-license-btn"
                                        data-transaction="<?php echo esc_attr($order['transaction_id']); ?>"
                                        data-action="view-license">
                                    <span class="rw-btn-icon">🔑</span>
                                    <span class="rw-btn-text"><?php _e('View License', 'astra-child'); ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页 -->
        <?php if ($total_pages > 1): ?>
            <div class="rw-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?php echo esc_url(add_query_arg('order_page', $i)); ?>"
                       class="rw-page-link <?php echo $i == $current_page ? 'rw-current-page' : ''; ?>"
                       data-page="<?php echo $i; ?>"
                       data-action="load-orders-page">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>