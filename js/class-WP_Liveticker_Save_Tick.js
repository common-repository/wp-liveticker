/**
 * Feature Name:	WP Liveticker Save Tick
 * Description:		This jQuery Lib checks and validates the live ticker
 * Version:			0.1
 * Author:			DasLlama
 * Author URI:		http://dasllama.github.com
 * Licence:			CC-BY-SA

* Changelog
*
* 0.1
* - Initial Commit
*/

jQuery.noConflict();
( function( $ ) {
	WP_Liveticker_Save_Tick = {
		
		/**
		 * Initialize Click events
		 * 
		 * @since	0.1
		 * @return	void
		 */
		init : function() {
			
			// If the post has been submitted, check it for a ticker
			$( '#post' ).submit( function() {
				if ( 0 >= $( 'input[name$="tax_input[wp-liveticker][]"]:checked' ).length ) {
					alert( WP_Liveticker_Save_Tick_vars.choose_a_ticker );
					$( '#ajax-loading' ).css( 'visibility', 'hidden' );
					$( '#publish' ).removeClass( 'button-primary-disabled' );
					return false;
				}
			} );
		},
	};
	$( document ).ready( function( $ ) { WP_Liveticker_Save_Tick.init(); } );
} )( jQuery );