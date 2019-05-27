jQuery( function( $ ) {
  $( document ).on( 'click', 'body:not(.elementor-editor-active) .make-column-clickable-elementor', function( e ) {
    if ( $( this ).data( 'column-clickable' ) ) {
      if ( $( e.target ).filter( 'a, a *, .no-link, .no-link *' ).length ) {
        return true;
      }

      // smooth scroll
      if ( $( this ).data( 'column-clickable' ).match( "^#" ) ) {
        var hash = $( this ).data( 'column-clickable' );

        $( 'html, body' ).animate( {
          scrollTop: $( hash ).offset().top
        }, 800, function() {
          window.location.hash = hash;
        });

        return true;
      }

      window.open( $( this ).data( 'column-clickable' ), $( this ).data( 'column-clickable-blank' ) );
      return false;
    }
  });
});
