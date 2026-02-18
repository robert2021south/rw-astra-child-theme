<?php
/**
 * Template Name: Order Complete
 * Description: Custom page template for order completion
 *
 * @package Astra Child
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get parameters from URL, set default values
$transactionId = isset($_GET['transactionId']) ? esc_url_raw($_GET['transactionId']) : '';
$site_url = isset($_GET['site_url']) ? esc_url_raw($_GET['site_url']) : 'https://robertwp.com';
$plugin_name = isset($_GET['name']) ? sanitize_text_field($_GET['plugin_name']) : 'RW PostViewStats Pro';
$plugin_slug = isset($_GET['slug']) ? sanitize_text_field($_GET['plugin_slug']) : 'rw-postviewstats-pro';
$plugin_version = isset($_GET['version']) ? sanitize_text_field($_GET['plugin_version']) : '1.0.0';

// Pass PHP variables to JavaScript
wp_localize_script('order-complete-min-js', 'orderConfig', array(
    'transactionId' => $transactionId,
    'siteUrl' => $site_url,
    'pluginName' => $plugin_name,
    'pluginSlug' => $plugin_slug,
    'pluginVersion' => $plugin_version,
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('order_complete_nonce')
));

// Do not load header and footer, keep the page clean
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?> Order Complete</title>

    <?php wp_head(); ?>
</head>
<body>
<div class="container">
    <!-- Initial loading state -->
    <div id="initial-loading" class="initial-loading">
        <div class="loading-spinner-large"></div>
        <h2>Loading your order...</h2>
        <p class="muted">Please wait while we prepare your purchase</p>

        <div class="transaction-info">
            <span class="info-label">Transaction ID:</span>
            <strong class="info-value" id="transaction-id-display">
                <?php echo esc_html($_GET['transaction_id'] ?? 'Loading...'); ?>
            </strong>
        </div>

        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>

        <p class="small">This may take a few moments</p>
    </div>

    <div id="app" style="display: none;"></div>
</div>

<script>
    (function displayTransactionIdImmediately() {
        try {
            // Get parameters from URL
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const transactionId = urlParams.get('transaction_id');

            // Find the display element
            const displayElement = document.getElementById('transaction-id-display');

            if (displayElement) {
                if (transactionId) {
                    // If Transaction ID exists, display it directly
                    displayElement.textContent = transactionId;
                    displayElement.className = 'info-value has-value';
                } else {
                    // If no Transaction ID, show a prompt
                    displayElement.textContent = 'Not available';
                    displayElement.className = 'info-value no-value';
                }
            }

            // Store in window object for use by subsequent classes
            window.__cachedTransactionId = transactionId;

        } catch (e) {
            console.warn('Failed to display transaction ID:', e);
        }
    })();
</script>


<?php wp_footer(); ?>
</body>
</html>