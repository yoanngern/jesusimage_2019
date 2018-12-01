<?php

add_filter( 'nf_stripe_plugin_settings', 'ninja_forms_stripe_deprecated_plugin_settings' );
function ninja_forms_stripe_deprecated_plugin_settings( $settings ){
    $deprecated_settings = array(
        'stripe_currency_divider' => array(
            'id'    => 'stripe_currency_divider',
            'type'  => 'html',
            'label' => '',
            'html' => '<hr />'
        ),
        'stripe_currency_deprecated' => array(
            'id'    => 'stripe_currency_deprecated',
            'type'  => 'html',
            'label' => __( 'Transaction Currency', 'ninja-forms-stripe' ),
            'html'  => __( 'Currency Settings have been moved to a General Setting, which can be overridden per form.', 'ninja-forms-stripe' )
        ),
    );
    return array_merge( $settings, $deprecated_settings );
}
