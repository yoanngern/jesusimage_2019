<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_Stripe_Admin_Metaboxes_Submission extends NF_Abstracts_SubmissionMetabox
{
    public function __construct()
    {
        parent::__construct();

        $this->_title = __( 'Stripe Payment', 'ninja-forms' );

        if( $this->sub && ! $this->sub->get_extra_value( 'stripe_token' ) ){
            remove_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        }
    }

    public function render_metabox( $post, $metabox )
    {
        if( ! $this->sub->get_extra_value( 'stripe_live' ) ){
            echo "<div style='text-align: center; background-color: yellow;'>";
            echo "<strong>";
            echo __( "This is a test transaction.", "ninja-forms-stripe" );
            echo "</strong>";
            echo "</div>";
        }

        echo "<dl>";

        echo "<dt>";
        echo __( "Token", "ninja-forms-stripe" );
        echo "</dt>";

        echo "<dd>";
        echo $this->sub->get_extra_value( 'stripe_token' );
        echo "</dd>";

        echo "<dt>";
        echo __( "Customer ID", "ninja-forms-stripe" );
        echo "</dt>";

        echo "<dd>";
        echo $this->sub->get_extra_value( 'stripe_customer_id' );
        echo "</dd>";

        echo "<dt>";
        echo __( "Charge ID", "ninja-forms-stripe" );
        echo "</dt>";

        echo "<dd>";
        echo $this->sub->get_extra_value( 'stripe_charge_id' );
        echo "</dd>";

        echo "<dt>";
        echo __( "Card", "ninja-forms-stripe" );
        echo "</dt>";

        echo "<dd>";
        echo $this->sub->get_extra_value( 'stripe_brand' );
        echo "</dd>";
        echo "<dd>";
        echo "**** **** **** ";
        echo $this->sub->get_extra_value( 'stripe_last4' );
        echo "</dd>";

        echo "</dl>";

    }
}