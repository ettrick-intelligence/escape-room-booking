<?php
/**
 * Tests specific to the Lite version feature restrictions
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'EERB_PLUGIN_DIR' ) ) {
    define( 'EERB_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

class LiteFeaturesTest extends TestCase {

    public function test_erb_lite_constant_is_defined() {
        $this->assertTrue( defined( 'EERB_LITE' ), 'EERB_LITE constant must be defined in the free version' );
        $this->assertTrue( EERB_LITE );
    }

    public function test_no_licence_class() {
        $this->assertFileDoesNotExist(
            EERB_PLUGIN_DIR . 'includes/class-erbpro-licence.php',
            'Lite version must not include the Pro licence class'
        );
    }

    public function test_no_gamekeepers_view() {
        $this->assertFileDoesNotExist(
            EERB_PLUGIN_DIR . 'admin/views/gamekeepers.php',
            'Lite version must not include the Gamekeepers admin screen'
        );
    }

    public function test_no_promo_codes_view() {
        $this->assertFileDoesNotExist(
            EERB_PLUGIN_DIR . 'admin/views/promo-codes.php',
            'Lite version must not include the Promo Codes admin screen'
        );
    }

    public function test_no_reports_view() {
        $this->assertFileDoesNotExist(
            EERB_PLUGIN_DIR . 'admin/views/reports.php',
            'Lite version must not include the Reports admin screen'
        );
    }

    public function test_upgrade_view_exists() {
        $this->assertFileExists(
            EERB_PLUGIN_DIR . 'admin/views/upgrade.php',
            'Lite version must include an Upgrade to Pro screen'
        );
    }

    public function test_2_game_limit_enforced_in_admin() {
        $source = file_get_contents( EERB_PLUGIN_DIR . 'admin/class-erb-admin.php' );
        $this->assertStringContainsString(
            'EERB_LITE',
            $source,
            'Admin class must check EERB_LITE constant to enforce game limit'
        );
        $this->assertStringContainsString(
            '>= 2',
            $source,
            'Admin class must enforce a limit of 2 games'
        );
    }

    public function test_promo_code_field_hidden_in_booking() {
        $source = file_get_contents( EERB_PLUGIN_DIR . 'public/views/booking.php' );
        $this->assertStringContainsString(
            "! defined( 'EERB_LITE' )",
            $source,
            'Booking page must hide promo code field when EERB_LITE is defined'
        );
    }

    public function test_upgrade_page_links_to_pro_site() {
        $source = file_get_contents( EERB_PLUGIN_DIR . 'admin/views/upgrade.php' );
        $this->assertStringContainsString(
            'escaperoombookingpro.com',
            $source,
            'Upgrade page must link to escaperoombookingpro.com'
        );
    }
}
