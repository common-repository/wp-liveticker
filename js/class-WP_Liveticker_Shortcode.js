/**
 * Feature Name:	WP Liveticker Shortcode
 * Description:		This jQuery Lib loads the frontend stuff
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
	WP_Liveticker_Shortcode = {
		
		/**
		 * Initialize the events
		 * 
		 * @since	0.1
		 * @return	void
		 */
		init : function() {
			
			// Load the ticker
			if ( $( '#ticker' ) ) {
				WP_Liveticker_Shortcode.load_next_posts();
    			setInterval( WP_Liveticker_Shortcode.load_next_posts, WP_Liveticker_Shortcode_vars.interval );
    			
    			// Remove last hr
    			$( '#ticker' ).children( 'hr' ).last().remove();
    		}
		},
		
		load_next_posts : function() {
			
			var post_vars = {
				loadedfiles: loadedfiles,
				current_ticker: $( '#ticker' ).attr( 'class' ),
				action: 'get_new_ticks'
			};
			
			$.ajax( {
				data: post_vars,
				url: WP_Liveticker_Shortcode_vars.admin_ajax,
				async: true,
				dataType: 'html',
				success: function( response ) {
					var stuff = $.parseJSON( response );
					
					if ( null != stuff ) {
						$.each( stuff.loadedfiles, function( index, value ) {
							loadedfiles.push( value );
						} );
						
						$( stuff.html ).hide().prependTo( '#ticker' ).slideDown( 'fast' );
					}
				}
		    } )
		},
	};
	$( document ).ready( function( $ ) { WP_Liveticker_Shortcode.init(); } );
} )( jQuery );