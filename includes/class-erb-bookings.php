<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class EERB_Bookings {
    public function get_all( $args = array() ) { return EERB_DB::get_bookings( $args ); }
    public function get( $id ) { return EERB_DB::get_booking( $id ); }
    public function get_by_token( $token ) { return EERB_DB::get_booking_by_token( $token ); }
    public function create( $data ) { return EERB_DB::insert_booking( $data ); }
    public function update( $id, $data ) { EERB_DB::update_booking( $id, $data ); }
    public function log_history( $data ) { EERB_DB::add_booking_history( $data ); }
}
