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
get_header();
?>
    <div class="ast-container">
        <div id="primary" class="content-area primary">
            <main id="main" class="site-main">
                <article class="post-0 page type-page status-draft hentry">

                    <div class="entry-content">
                        <!-- Initial loading state -->
                        <div id="initial-loading" class="initial-loading">
                            <div class="loading-spinner-large"></div>
                            <h2>Loading your order...</h2>
                            <p class="muted">Please wait while we prepare your purchase</p>

                            <div class="transaction-info">
                                <span class="info-label">Transaction ID:</span>
                                <strong class="info-value" id="transaction-id-display">
                                    <?php echo esc_html(sanitize_text_field($_GET['slug']) ?: 'Loading...'); ?>
                                </strong>
                            </div>

                            <div class="loading-progress">
                                <div class="loading-progress-bar"></div>
                            </div>

                            <p class="small">This may take a few moments</p>
                        </div>

                        <!-- Main content area, initially hidden -->
                        <div id="app" style="display: none;"></div>
                    </div>
                </article>
            </main>
        </div>
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


<?php
get_footer();
?>