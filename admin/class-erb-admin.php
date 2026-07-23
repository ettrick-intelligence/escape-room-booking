<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
if ( ! defined( 'ABSPATH' ) ) exit;

class EERB_Admin {

    // ─── Admin Menu ───────────────────────────────────────────────────────────

    public function register_admin_menu() {
        add_menu_page( __( 'Escape Room Booking', 'ettrick-escape-room-booking' ), __( 'Escape Rooms', 'ettrick-escape-room-booking' ), 'manage_options', 'eerb-dashboard', array( $this, 'page_dashboard' ), 'dashicons-calendar-alt', 30 );
        add_submenu_page( 'eerb-dashboard', __( 'Dashboard',        'ettrick-escape-room-booking' ), __( 'Dashboard',              'ettrick-escape-room-booking' ), 'manage_options', 'eerb-dashboard', array( $this, 'page_dashboard' ) );
        add_submenu_page( 'eerb-dashboard', __( 'Games',            'ettrick-escape-room-booking' ), __( 'Games',                  'ettrick-escape-room-booking' ), 'manage_options', 'eerb-games',     array( $this, 'page_games' ) );
        add_submenu_page( 'eerb-dashboard', __( 'Bookings',         'ettrick-escape-room-booking' ), __( 'Bookings',               'ettrick-escape-room-booking' ), 'manage_options', 'eerb-bookings',  array( $this, 'page_bookings' ) );
        add_submenu_page( 'eerb-dashboard', __( 'Customers',        'ettrick-escape-room-booking' ), __( 'Customers',              'ettrick-escape-room-booking' ), 'manage_options', 'eerb-customers', array( $this, 'page_customers' ) );
        add_submenu_page( 'eerb-dashboard', __( 'Settings',         'ettrick-escape-room-booking' ), __( 'Settings',               'ettrick-escape-room-booking' ), 'manage_options', 'eerb-settings',  array( $this, 'page_settings' ) );
        add_submenu_page( 'eerb-dashboard', __( 'Upgrade to Pro',   'ettrick-escape-room-booking' ), __( 'Upgrade to Pro &#x1F680;', 'ettrick-escape-room-booking' ), 'manage_options', 'eerb-upgrade',   array( $this, 'page_upgrade' ) );
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    public function register_settings() {
        foreach ( array(
            'eerb_currency','eerb_currency_symbol','eerb_slot_hold_minutes',
            'eerb_slot_available_color','eerb_slot_booked_color','eerb_stripe_mode',
            'eerb_stripe_test_pk','eerb_stripe_test_sk','eerb_stripe_live_pk','eerb_stripe_live_sk',
            'eerb_stripe_webhook_secret','eerb_admin_email','eerb_email_from_name','eerb_email_from_address','eerb_booking_page_url','eerb_manage_page_url','eerb_calendar_home_url','eerb_date_format',
        ) as $key ) {
            register_setting( 'eerb_settings_group', $key, array( 'sanitize_callback' => 'sanitize_text_field' ) );
        }
    }

    // ─── Assets ───────────────────────────────────────────────────────────────

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'erb-' ) === false && $hook !== 'toplevel_page_erb-dashboard' ) return;
        wp_enqueue_style(  'eerb-admin', EERB_PLUGIN_URL . 'admin/css/erb-admin.css', array(), EERB_VERSION );
        wp_enqueue_script( 'eerb-admin', EERB_PLUGIN_URL . 'admin/js/erb-admin.js',  array( 'jquery' ), EERB_VERSION, true );
        wp_localize_script( 'eerb-admin', 'eerbAdmin', array(
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'eerb_admin_nonce' ),
            'currencySymbol' => get_option( 'eerb_currency_symbol', '£' ),
        ) );
        if ( strpos( $hook, 'eerb-games' ) !== false ) {
            wp_enqueue_script( 'eerb-games', EERB_PLUGIN_URL . 'admin/js/erb-games.js', array( 'eerb-admin' ), EERB_VERSION, true );
        }
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public function page_dashboard()   { include EERB_PLUGIN_DIR . 'admin/views/dashboard.php'; }
    public function page_games()       { include EERB_PLUGIN_DIR . 'admin/views/games.php'; }
    public function page_bookings()    { include EERB_PLUGIN_DIR . 'admin/views/bookings.php'; }
    public function page_customers()   { include EERB_PLUGIN_DIR . 'admin/views/customers.php'; }
    public function page_settings()    { include EERB_PLUGIN_DIR . 'admin/views/settings.php'; }
    public function page_upgrade()     { include EERB_PLUGIN_DIR . 'admin/views/upgrade.php'; }

    // ─── AJAX: Rooms ──────────────────────────────────────────────────────────

    public function ajax_save_room() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
        if ( empty( $name ) ) EERB_Helpers::json_error( __( 'Room name is required.', 'ettrick-escape-room-booking' ) );
        $data = array( 'name' => $name, 'description' => sanitize_text_field( wp_unslash( $_POST['description'] ) ) );
        if ( ! empty( $_POST['id'] ) ) $data['id'] = (int) $_POST['id'];
        $id = EERB_DB::upsert_room( $data );
        EERB_Helpers::json_success( array( 'id' => $id ) );
    }

    public function ajax_delete_room() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $id = (int) ( $_POST['id'] ?? 0 );
        if ( ! $id ) EERB_Helpers::json_error( 'Invalid ID' );
        global $wpdb; $wpdb->delete( $wpdb->prefix . 'eerb_rooms', array( 'id' => $id ) );
        EERB_Helpers::json_success();
    }

    // ─── AJAX: Games ──────────────────────────────────────────────────────────

    public function ajax_save_game() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        // Lite version: enforce 2-game limit
        if ( defined( 'EERB_LITE' ) ) {
            global $wpdb;
            $game_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}eerb_games" );
            $is_new     = empty( $_POST['id'] ) || ! EERB_DB::get_game( (int) $_POST['id'] );
            if ( $is_new && $game_count >= 2 ) {
                EERB_Helpers::json_error( 'You have reached the 2-game limit of the free version. Upgrade to Pro for unlimited games.' );
            }
        }
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
        if ( empty( $name ) ) EERB_Helpers::json_error( __( 'Game name is required.', 'ettrick-escape-room-booking' ) );
        if ( empty( $_POST['room_id'] ) ) EERB_Helpers::json_error( __( 'Please select a physical room.', 'ettrick-escape-room-booking' ) );
        $data = array(
            'room_id'              => (int) $_POST['room_id'],
            'name'                 => $name,
            'slug'                 => sanitize_title( $_POST['slug'] ?? $name ),
            'description'          => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'image_url'            => esc_url_raw( $_POST['image_url'] ?? '' ),
            'duration_minutes'     => max( 15, (int) ( $_POST['duration_minutes'] ?? 60 ) ),
            'setup_minutes'        => max( 0,  (int) ( $_POST['setup_minutes'] ?? 30 ) ),
            'min_notice_hours'     => max( 0,  (int) ( $_POST['min_notice_hours'] ?? 2 ) ),
            'booking_horizon_date' => ! empty( $_POST['booking_horizon_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_horizon_date'] ) ) : null,
            'status'               => in_array( $_POST['status'] ?? '', array( 'active','inactive' ) ) ? $_POST['status'] : 'active',
            'min_players'          => max( 1, (int) ( $_POST['min_players'] ?? 2 ) ),
            'max_players'          => min( 20, max( 1, (int) ( $_POST['max_players'] ?? 8 ) ) ),
        );
        if ( ! empty( $_POST['id'] ) ) $data['id'] = (int) $_POST['id'];
        $id = EERB_DB::upsert_game( $data );
        EERB_Helpers::json_success( array( 'id' => $id ) );
    }

    public function ajax_delete_game() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $id = (int) ( $_POST['id'] ?? 0 );
        if ( ! $id ) EERB_Helpers::json_error( 'Invalid ID' );
        global $wpdb; $wpdb->delete( $wpdb->prefix . 'eerb_games', array( 'id' => $id ) );
        EERB_Helpers::json_success();
    }

    public function ajax_get_game() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $id = (int) ( $_POST['id'] ?? 0 );
        $game = EERB_DB::get_game( $id );
        if ( ! $game ) EERB_Helpers::json_error( 'Not found', 404 );
        $game->hours  = EERB_DB::get_game_hours( $id );
        $game->prices = EERB_DB::get_prices( $id );
        EERB_Helpers::json_success( $game );
    }

    // ─── AJAX: Hours ──────────────────────────────────────────────────────────

    public function ajax_save_hours() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $game_id = (int) ( $_POST['game_id'] ?? 0 );
        if ( ! $game_id ) EERB_Helpers::json_error( 'Invalid game ID' );
        $hours = array();
        foreach ( ( $_POST['hours'] ?? array() ) as $day => $h ) {
            $hours[ (int) $day ] = array(
                'open_time'  => sanitize_text_field( $h['open_time']  ?? '' ),
                'close_time' => sanitize_text_field( $h['close_time'] ?? '' ),
                'is_closed'  => ! empty( $h['is_closed'] ) ? 1 : 0,
            );
        }
        EERB_DB::save_game_hours( $game_id, $hours );
        EERB_Helpers::json_success();
    }

    // ─── AJAX: Pricing ────────────────────────────────────────────────────────

    public function ajax_save_pricing() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $game_id = (int) ( $_POST['game_id'] ?? 0 );
        if ( ! $game_id ) EERB_Helpers::json_error( 'Invalid game ID' );
        $prices = array();
        $game        = EERB_DB::get_game( $game_id );
        $min_players = $game ? (int) $game->min_players : 2;
        $max_players = $game ? (int) $game->max_players : 8;
        foreach ( ( $_POST['prices'] ?? array() ) as $players => $price_pounds ) {
            $players     = (int) $players;
            $price_pence = (int) round( (float) $price_pounds * 100 );
            if ( $players >= $min_players && $players <= $max_players && $price_pence > 0 ) $prices[ $players ] = $price_pence;
        }
        EERB_DB::save_prices( $game_id, $prices );
        EERB_Helpers::json_success();
    }

    // ─── AJAX: Bookings ──────────────────────────────────────────────────────────

    public function ajax_admin_get_booking() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $id      = (int) ( $_POST['id'] ?? 0 );
        $booking = EERB_DB::get_booking( $id );
        if ( ! $booking ) EERB_Helpers::json_error( 'Not found', 404 );
        EERB_Helpers::json_success( $booking );
    }

    public function ajax_admin_cancel_booking() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        $id      = (int) ( $_POST['id'] ?? 0 );
        $booking = EERB_DB::get_booking( $id );
        if ( ! $booking ) EERB_Helpers::json_error( 'Not found', 404 );
        EERB_DB::update_booking( $id, array( 'status' => 'cancelled', 'updated_at' => current_time( 'mysql' ) ) );
        EERB_DB::add_booking_history( array(
            'booking_id' => $id, 'action' => 'cancelled',
            'changed_by' => 'admin', 'created_at' => current_time( 'mysql' ),
        ) );
        $emails = new EERB_Emails();
        $emails->send_cancellation( EERB_DB::get_booking( $id ) );
        EERB_Helpers::json_success();
    }

    // ─── AJAX: Blocked slots (admin calendar management) ─────────────────────

    public function ajax_block_slot() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        EERB_Helpers::json_success();
    }
    public function ajax_unblock_slot() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        EERB_Helpers::json_success();
    }
    public function ajax_save_promo()        { EERB_Helpers::json_error( 'Pro feature.' ); }
    public function ajax_delete_promo()      { EERB_Helpers::json_error( 'Pro feature.' ); }
    public function ajax_save_gamekeeper()   { EERB_Helpers::json_error( 'Pro feature.' ); }
    public function ajax_delete_gamekeeper() { EERB_Helpers::json_error( 'Pro feature.' ); }
    public function ajax_get_bookings() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        EERB_Helpers::json_success( EERB_DB::get_bookings() );
    }
    public function ajax_update_booking() {
        EERB_Helpers::verify_nonce( $_POST['nonce'] ?? '', 'eerb_admin_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) EERB_Helpers::json_error( 'Unauthorised', 403 );
        EERB_Helpers::json_success();
    }
}
