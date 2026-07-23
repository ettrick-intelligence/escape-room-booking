<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class EERB_Games {
    public function get_all( $active_only = true ) { return EERB_DB::get_games( $active_only ); }
    public function get( $id ) { return EERB_DB::get_game( $id ); }
    public function get_by_slug( $slug ) { return EERB_DB::get_game_by_slug( $slug ); }
    public function get_sibling( $game_id ) { return EERB_DB::get_room_sibling( $game_id ); }
}
