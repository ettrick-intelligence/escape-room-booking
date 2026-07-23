<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Runs on plugin deactivation.
 * Note: Tables and data are intentionally preserved on deactivation.
 * Full removal only happens on uninstall (uninstall.php).
 */
class EERB_Deactivator {

    public static function deactivate() {
        // Clear any scheduled cron jobs
        wp_clear_scheduled_hook( 'eerb_cleanup_expired_holds' );
        flush_rewrite_rules();
    }
}
