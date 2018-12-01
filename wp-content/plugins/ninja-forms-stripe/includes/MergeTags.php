<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Stripe_MergeTags
 */
final class NF_Stripe_MergeTags extends NF_Abstracts_MergeTags
{
    protected $id = 'stripe';

    public function __construct()
    {
        parent::__construct();
        $this->title = __( 'Stripe', 'ninja-forms' );
        $this->merge_tags = NF_Stripe()->config( 'MergeTags' );
    }

    public function __call($name, $arguments)
    {
        // If the mergetag property is not set, then return an empty string.
        return ( isset( $this->$name ) ) ? $this->$name : '';
    }

    public function set( $property, $value )
    {
        $this->$property = $value;
    }

} // END CLASS NF_PayPalExpress_MergeTags
