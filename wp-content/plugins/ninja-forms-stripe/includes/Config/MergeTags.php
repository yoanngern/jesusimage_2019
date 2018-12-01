<?php

return array(
    'las4' => array(
        'id' => 'last4',
        'tag' => '{stripe:last4}',
        'label' => __( 'Last 4', 'ninja-forms-stripe' ),
        'callback' => 'last4'
    ),

    'cardtype' => array(
        'id' => 'cardtype',
        'tag' => '{stripe:cardtype}',
        'label' => __( 'Card Type (Brand)', 'ninja-forms-stripe' ),
        'callback' => 'cardtype'
    ),

    'customerID' => array(
        'id' => 'customerID',
        'tag' => '{stripe:customerID}',
        'label' => __( 'Customer ID', 'ninja-forms-stripe' ),
        'callback' => 'customerID'
    ),

    'chargeID' => array(
        'id' => 'chargeID',
        'tag' => '{stripe:chargeID}',
        'label' => __( 'Charge ID', 'ninja-forms-stripe' ),
        'callback' => 'chargeID'
    ),
);
