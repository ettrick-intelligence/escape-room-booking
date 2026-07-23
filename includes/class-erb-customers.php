<?php if ( ! defined( 'ABSPATH' ) ) exit;
class EERB_Customers {
    public function get_by_email( $email ) { return EERB_DB::get_customer_by_email( $email ); }
    public function get( $id ) { return EERB_DB::get_customer( $id ); }
    public function create( $data ) { return EERB_DB::insert_customer( $data ); }
    public function update( $id, $data ) { EERB_DB::update_customer( $id, $data ); }
    public function verify_password( $customer, $password ) {
        return password_verify( $password, $customer->password_hash );
    }
    public function hash_password( $password ) { return password_hash( $password, PASSWORD_DEFAULT ); }
}
