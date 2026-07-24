<?php
/**
 * Tests for slot overlap detection (Lite)
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'EERB_PLUGIN_DIR' ) ) {
    define( 'EERB_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'EERB_LITE' ) ) {
    define( 'EERB_LITE', true );
}
if ( ! defined( 'EERB_VERSION' ) ) {
    define( 'EERB_VERSION', '1.3.0' );
}

class SlotOverlapTest extends TestCase {

    public function test_overlap_query_uses_interval_logic() {
        $source = file_get_contents( EERB_PLUGIN_DIR . 'includes/class-erb-db.php' );
        $this->assertStringContainsString( 'slot_start < %s', $source );
        $this->assertStringContainsString( 'slot_end > %s', $source );
    }

    public function test_overlap_logic_catches_partial_overlap() {
        $existing_start  = strtotime( '2026-04-05 12:00:00' );
        $existing_end    = strtotime( '2026-04-05 13:00:00' );
        $requested_start = strtotime( '2026-04-05 12:30:00' );
        $requested_end   = strtotime( '2026-04-05 13:30:00' );
        $overlaps = $requested_start < $existing_end && $requested_end > $existing_start;
        $this->assertTrue( $overlaps );
    }

    public function test_overlap_logic_allows_adjacent_slots() {
        $existing_start  = strtotime( '2026-04-05 12:00:00' );
        $existing_end    = strtotime( '2026-04-05 13:00:00' );
        $requested_start = strtotime( '2026-04-05 13:00:00' );
        $requested_end   = strtotime( '2026-04-05 14:00:00' );
        $overlaps = $requested_start < $existing_end && $requested_end > $existing_start;
        $this->assertFalse( $overlaps );
    }

    public function test_overlap_logic_allows_earlier_slot() {
        $existing_start  = strtotime( '2026-04-05 13:00:00' );
        $existing_end    = strtotime( '2026-04-05 14:00:00' );
        $requested_start = strtotime( '2026-04-05 11:00:00' );
        $requested_end   = strtotime( '2026-04-05 12:00:00' );
        $overlaps = $requested_start < $existing_end && $requested_end > $existing_start;
        $this->assertFalse( $overlaps );
    }
}
