<?php
/**
 *
 *
 * @class    WC_POS_Params
 * @package  WooCommerce POS
 * @author   Paul Kilmurray <paul@kilbot.com.au>
 * @link     http://www.woopos.com.au
 */

class WC_POS_Params {

  /**
   * Required parameters for the POS front end
   */
  static public function frontend() {

    $param = array(
      'accounting'    => self::accounting_settings(),
      'ajaxurl'       => admin_url( 'admin-ajax.php', 'relative' ),
      'customers'     => self::customers(),
      'denominations' => WC_POS_i18n::currency_denominations( get_option('woocommerce_currency') ),
      'discount_keys' => self::discount_keys(),
      'hotkeys'       => self::hotkeys(),
      'i18n'          => WC_POS_i18n::translations(),
      'nonce'         => wp_create_nonce( WC_POS_PLUGIN_NAME ),
      'shipping'      => self::shipping_labels(),
      'tabs'          => self::product_tabs(),
      'tax'           => self::wc_settings(),
      'tax_labels'    => self::tax_labels(),
      'tax_rates'     => self::tax_rates(),
      'user'          => self::user(),
      'wc_api'        => get_woocommerce_api_url( '' )
    );

    return apply_filters( 'woocommerce_pos_params', $param );

  }

  /**
   * @return mixed|void
   */
  static public function admin() {

    $param = array(
      'accounting'  => self::accounting_settings(),
      'ajaxurl'     => admin_url( 'admin-ajax.php', 'relative' ),
      'customers'   => self::customers(),
      'nonce'       => wp_create_nonce( WC_POS_PLUGIN_NAME ),
      'i18n'        => WC_POS_i18n::translations('admin'),
      'wc_api'      => get_woocommerce_api_url( '' )
    );

    return apply_filters( 'woocommerce_pos_admin_params', $param );
  }

  /**
   * Default quick tabs for products
   *
   * @return array
   */
  static private function product_tabs() {
    $tabs = array(
      'all' => array(
        /* translators: woocommerce */
        'label' => __( 'All', 'woocommerce'),
        'active' => true
      ),
      'featured' => array(
        /* translators: woocommerce */
        'label' => __( 'Featured', 'woocommerce'),
        'id' => 'featured:true'
      ),
      'onsale' => array(
        'label' => _x( 'On Sale', 'Product tab: \'On Sale\' products', 'woocommerce-pos'),
        'id' => 'on_sale:true'
      ),
    );
    return $tabs;
  }

  /**
   * Default hotkeys
   *
   * @return array
   */
  static private function hotkeys() {
    $hotkeys = new WC_POS_Admin_Settings_HotKeys();
    return $hotkeys->get_data('hotkeys');
  }

  /**
   * Get the accounting format from user settings
   * POS uses a plugin to format currency: http://josscrowcroft.github.io/accounting.js/
   *
   * @return array $settings
   */
  static private function accounting_settings() {
    $decimal = get_option( 'woocommerce_price_decimal_sep' );
    $thousand = get_option( 'woocommerce_price_thousand_sep' );
    $precision = get_option( 'woocommerce_price_num_decimals' );
    $settings = array(
      'currency' => array(
        'decimal' => $decimal,
        'format'  => self::currency_format(),
        'precision' => $precision,
        'symbol'  => get_woocommerce_currency_symbol( get_woocommerce_currency() ),
        'thousand'  => $thousand,
      ),
      'number' => array(
        'decimal' => $decimal,
        'precision' => $precision,
        'thousand'  => $thousand,
      )
    );
    return $settings;
  }

  /**
   * Get the currency format from user settings
   *
   * @return array $format
   */
  static private function currency_format() {
    $currency_pos = get_option( 'woocommerce_currency_pos' );

    if( $currency_pos == 'right' )
      return array('pos' => '%v%s', 'neg' => '- %v%s', 'zero' => '%v%s');

    if( $currency_pos == 'left_space' )
      return array('pos' => '%s&nbsp;%v', 'neg' => '- %s&nbsp;%v', 'zero' => '%s&nbsp;%v');

    if( $currency_pos == 'right_space' )
      return array('pos' => '%v&nbsp;%s', 'neg' => '- %v&nbsp;%s', 'zero' => '%v&nbsp;%s');

    // default = left
    return array('pos' => '%s%v', 'neg' => '- %s%v', 'zero' => '%s%v');
  }

  /**
   * Get the default customer + guest
   *
   * @return object $customer
   */
  static private function customers() {
    $general = new WC_POS_Admin_Settings_General();
    $settings = $general->get_data();
    $user_id = false;

    if(isset($settings['customer']['id'])){
      $user_id = $settings['customer']['id'];
    }

    if(isset($settings['logged_in_user']) && $settings['logged_in_user']){
      $user_id = get_current_user_id();
    }

    if( $user_id ) {
      $user = get_userdata($user_id);
      $customers['default'] = array(
        'id' => $user->ID,
        'first_name'  => esc_html($user->first_name),
        'last_name'   => esc_html($user->last_name),
        'email'       => esc_html($user->user_email),
        'username'    => esc_html($user->user_login)
      );
    }

    $customers['guest'] = array(
      'id' => 0,
      /* translators: woocommerce */
      'first_name' => __( 'Guest', 'woocommerce' )
    );

    return $customers;
  }

  /**
   * Get the woocommerce shop settings
   *
   * @return array $settings
   */
  static private function wc_settings() {
    $settings = array(
      'tax_label'             => WC()->countries->tax_or_vat(),
      'calc_taxes'            => get_option( 'woocommerce_calc_taxes' ),
      'prices_include_tax'    => get_option( 'woocommerce_prices_include_tax' ),
      'tax_round_at_subtotal' => get_option( 'woocommerce_tax_round_at_subtotal' ),
      'tax_display_cart'      => get_option( 'woocommerce_tax_display_cart' ),
      'tax_total_display'     => get_option( 'woocommerce_tax_total_display' ),
    );
    return $settings;
  }

  /**
   * User settings
   *
   * @return array $settings
   */
  static private function user() {
    global $current_user;

    $settings = array(
      'id'      => $current_user->ID,
      'display_name'  => $current_user->display_name
    );

    return $settings;
  }

  static public function tax_classes(){
    $classes = array_filter( array_map( 'sanitize_title', explode( "\n", get_option('woocommerce_tax_classes' ) ) ) );
    array_unshift( $classes, '' ); // add 'standard'
    return $classes;
  }

  static public function tax_rates() {
    $rates = array();

    // get_shop_base_rate depreciated in 2.3
    $get_rates = method_exists( 'WC_Tax','get_base_tax_rates' ) ? 'get_base_tax_rates' : 'get_shop_base_rate';

    foreach( self::tax_classes() as $class ) {
      if( $rate = WC_Tax::$get_rates( $class ) )
        $rates[$class] = $rate;
    }

    return $rates;
  }

  static public function tax_labels() {
    $labels = array_reduce( explode( "\n", get_option('woocommerce_tax_classes' ) ), function ($result, $label) {
      if( $label = trim($label) ) {
        $result[ sanitize_title($label) ] = $label;
      }
      return $result;
    }, array());

    /* translators: woocommerce */
    $standard[''] = __( 'Standard', 'woocommerce' );

    return $standard + $labels;
  }

  static public function shipping_labels() {

    /* translators: woocommerce */
    $labels = array( '' => __( 'N/A', 'woocommerce' ) );

    $shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();

    foreach( $shipping_methods as $method ){
      $labels[$method->id] = $method->get_title();
    }

    /* translators: woocommerce */
    $labels['other'] = __( 'Other', 'woocommerce' );

    return $labels;
  }

  static private function discount_keys(){
    $settings = new WC_POS_Admin_Settings_General();
    return $settings->get_data('discount_quick_keys');
  }

}