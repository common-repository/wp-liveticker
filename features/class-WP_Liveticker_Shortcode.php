<?php
/**
 * Feature Name:	WP Liveticker Shortcode
 * Description:		This feature implements the shortcode for the posts and pages
 * Version:			0.1.1
 * Author:			DasLlama
 * Author URI:		http://dasllama.github.com
 * Licence:			CC-BY-SA
 * Changelog
 * 
 * 0.1.1
 * - Fixing sort issue
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'WP_Liveticker_Shortcode' ) ) {

	class WP_Liveticker_Shortcode extends WP_Liveticker {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.1
		 * @var		NULL | WP_Liveticker_Shortcode
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @return	WP_Liveticker_Shortcode
		 */
		public static function get_instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data, initialize translations and start the hooks
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	add_shortcode, add_filter
		 * @return	void
		 */
		public function __construct () {
			
			// Add the shortcode
			add_shortcode( 'ticker', array( $this, 'load_ticker' ) );
			
			// Load the style
			add_filter( 'wp_head', array( $this, 'enqueue_style' ) );
			
			// Load the scripts
			add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			
			// Get new ticks
			add_filter( 'wp_ajax_get_new_ticks', array( $this, 'get_new_ticks' ) );
			add_filter( 'wp_ajax_nopriv_get_new_ticks', array( $this, 'get_new_ticks' ) );
		}
		
		/**
		 * Load the frontend style
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	get_option
		 * @return	void
		 */
		public function enqueue_style() {
			
			$template = get_option( 'wp_liveticker_template' );
			if ( ! $template )
				$template = $this->get_default_template();
			?>
			<style type="text/css">
				#ticker {
					<?php echo $template[ 'ticker' ]; ?>
				}
				
				#ticker h3 {
					<?php echo $template[ 'headline' ]; ?>
				}
				
				#ticker p {
					<?php echo $template[ 'the_p_tag' ]; ?>
				}
				
				#ticker hr {
					<?php echo $template[ 'space_line' ]; ?>
				}
				
				#ticker img {
					<?php echo $template[ 'image' ]; ?>
				}
				
				.ticker-rss {
					<?php echo $template[ 'ticker_rss' ]; ?>
				}
			</style>
			<?php
		}
		
		/**
		 * Enqueue Scripts
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	wp_enqueue_script, plugin_dir_url
		 * @return	void
		 */
		public function enqueue_scripts () {
			
			wp_enqueue_script( 'WP_Liveticker_Shortcode', plugin_dir_url( __FILE__ ) . '../js/class-WP_Liveticker_Shortcode.js', array( 'jquery' ) );
			$vars = $this->load_js_vars();
			wp_localize_script( 'WP_Liveticker_Shortcode', 'WP_Liveticker_Shortcode_vars', $vars );
		}
		
		/**
		 * load javasrcipt variables
		 *
		 * @access	public
		 * @since	0.1
		 * @return	array
		 */
		public function load_js_vars () {
			
			$settings = get_option( 'wp_liveticker_settings' );
			if ( ! $settings )
				$settings = $this->get_default_settings();
			
			$vars = array(
				'admin_ajax'	=> get_bloginfo( 'url' ) . '/wp-admin/admin-ajax.php',
				'interval'		=> $settings[ 'interval' ]
			);
			
			return $vars;
		}
		
		/**
		 * initial load of the ticker
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	add_shortcode
		 * @return	void
		 */
		public function load_ticker( $settings ) {
			
			// Current Ticker Slug
			$ticker_slug = $settings[ 0 ];
			
			// Get the needed files
			$files = WP_Liveticker_Cache_System::get_cached_files( $ticker_slug );
			sort( $files );
			$files = array_reverse( $files );
						
			// build the output
			$html .= '<a href="' . get_bloginfo( 'url' ) . '/wp-liveticker/' . $ticker_slug . '/feed/" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '../images/rss.jpg" class="ticker-rss" /></a>';
			$html .= '<div id="ticker" class="' . $ticker_slug . '">';
			foreach ( $files as $file ) {
				$html .= WP_Liveticker_Cache_System::read_cache( $file );
			}
			$html .= '</div>';
			
			// Output needed javascript
			$html .= '<script type="text/javascript">';
				$html .= 'var loadedfiles = ["' . implode( '", "', $files ) . '"];';
			$html .= '</script>';
			
			return $html;
		}
		
		/**
		 * Load add returns new ticker posts
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	
		 * @return	void
		 */
		public function get_new_ticks() {
			
			// Set empty
			$new_files = array();
			
			// Load new files
			$files = WP_Liveticker_Cache_System::get_cached_files( $_GET[ 'current_ticker' ] );
			sort( $files );
			$files = array_reverse( $files );
			
			foreach ( $files as $file ) {
				if ( ! in_array( $file, $_GET[ 'loadedfiles' ] ) ) {
					$new_files[] = $file;
					$_GET[ 'loadedfiles' ][] = $file;
				}
			}
			
			// are the new files present?
			if ( ! is_array( $new_files ) || 0 >= count( $new_files ) )
				die;
			
			// Loaded Files
			$response[ 'loadedfiles' ] = $new_files;
			
			// Read Cache and push html
			$html = '';
			foreach ( $new_files as $file ) {
				$html .= WP_Liveticker_Cache_System::read_cache( $file );
			}
			$response[ 'html' ] = $html;
			
			echo json_encode( $response );
			
			// It's ajax, so die. Thanks.
			die;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Liveticker_Shortcode::get_instance();
}