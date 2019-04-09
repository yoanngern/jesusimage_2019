jQuery( function( $ ) {
  $( 'body:not(.elementor-editor-active) .make-column-clickable-elementor' ).click( function( e ) {
    if ( $( this ).data( 'column-clickable' ) ) {
      if ( $( e.target ).filter( 'a, a *, .no-link, .no-link *' ).length ) {
        return true;
      }

      window.open( $( this ).data( 'column-clickable' ), $( this ).data( 'column-clickable-blank' ) );
      return false;
    }
  });
});
