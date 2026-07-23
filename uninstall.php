<?php
/**
 * Uninstall — runs when the plugin is deleted from WP Admin.
 * Removes all plugin tables and options.
 * WARNING: This permanently deletes all booking data.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$tables = array(
    'eerb_rooms', 'eerb_games', 'eerb_game_hours', 'eerb_prices',
    'eerb_blocked_slots', 'eerb_customers', 'eerb_bookings',
    'eerb_booking_history', 'eerb_slot_holds', 'eerb_promo_codes', 'eerb_gamekeepers',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
}

$options = array(
    'eerb_db_version', 'eerb_currency', 'eerb_currency_symbol',
    'eerb_slot_hold_minutes', 'eerb_slot_available_color', 'eerb_slot_booked_color',
    'eerb_stripe_mode', 'eerb_stripe_test_pk', 'eerb_stripe_test_sk',
    'eerb_stripe_live_pk', 'eerb_stripe_live_sk', 'eerb_stripe_webhook_secret',
    'eerb_admin_email', 'eerb_email_from_name', 'eerb_email_from_address',
);

foreach ( $options as $option ) {
    delete_option( $option );
}
