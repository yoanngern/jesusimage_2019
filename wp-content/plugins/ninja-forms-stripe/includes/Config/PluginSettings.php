<?php if ( ! defined( 'ABSPATH' ) ) exit;

return apply_filters( 'nf_stripe_plugin_settings', array(

    /*
    |--------------------------------------------------------------------------
    | Test Credentials
    |--------------------------------------------------------------------------
    */

    'stripe_test_publishable_key' => array(
        'id'    => 'stripe_test_publishable_key',
        'type'  => 'textbox',
        'label' => sprintf( __( 'Test Publishable Key%s', 'ninja-forms-stripe' ), '<span id="nf-stripe"></span>' ),
    ),

    'stripe_test_secret_key' => array(
        'id'    => 'stripe_test_secret_key',
        'type'  => 'textbox',
        'label' => __( 'Test Secret Key', 'ninja-forms-stripe' ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Divider
    |--------------------------------------------------------------------------
    */

    'stripe_divider_credentials' => array(
        'id'    => 'stripe_divider_credentials',
        'type'  => 'html',
        'label' => '',
        'html' => '<hr />'
    ),

    /*
    |--------------------------------------------------------------------------
    | Live Credentials
    |--------------------------------------------------------------------------
    */

    'stripe_live_publishable_key' => array(
        'id'    => 'stripe_live_publishable_key',
        'type'  => 'textbox',
        'label' => __( 'Live Publishable Key', 'ninja-forms-stripe' ),
    ),

    'stripe_live_secret_key' => array(
        'id'    => 'stripe_live_secret_key',
        'type'  => 'textbox',
        'label' => __( 'Live Secret Key', 'ninja-forms-stripe' ),
    ),

));
