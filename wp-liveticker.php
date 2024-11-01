<?php
/**
 * Plugin Name:	WP Liveticker
 * Description:	Ajaxified Live-Ticker which can be placed in an arcticle. Uses Custom Post Types, Custom Taxonomies and the shortcode [ticker]. You can also use own RSS-Feeds for each ticker you created.
 * Version:		0.5.2
 * Author:		DasLlama
 * Author URI:	http://dasllama.github.com
 * Licence:		CC-BY-SA
 * Text Domain:	wp-liveticker
 * Domain Path:	/language
 *
 * Ajaxified Live-Ticker which can be placed in an arcticle.
 * Uses Custom Post Types, Custom Taxonomies and the shortcode [ticker].
 * You can also use own RSS-Feeds for each ticker you created.
 *
 * @author th
 * @version 0.5.2
 *
 * Changelog
 *
 * Upcoming
 * - TODO Feature: Renew Cache functionality
 * - TODO Feature: More Filter options on overview page ( ticker )
 * - TODO Feature: Create own css
 * - TODO Feature: CSS for RSS-Button
 * - TODO Feature: Create own markup
 * - TODO Feature: Template-Presets
 * - TODO Feature: Sidebar-Widget
 * - TODO Feature: Soundimplemention
 * - TODO Feature: More Options ( Thumbnails, Date, RSS-Button, etc )
 * - TODO Feature: Multiple Tickers in one page ( e.g. Loop )
 *
 * 0.5.2
 * - Code: Only save tick on publish
 * - Code: Remove tick if it is setted to draft/trash/delete
 *
 * 0.5.1
 * - Code: Fixing sort issue
 *
 * 0.5
 * - Code: New Codebase
 * - Code: Documentation
 * - Code: Changed settings handling
 * - Code: Find and Fix the known bugs
 * - Code: Update Language Packs
 * - Code: Backward-Compatibility for options
 * - Feature: Multiple Tickers for a tick
 * - Feature: Cache System for the posts
 * - Feature: Some neat jQuery Effects
 * - Feature: Implement RSS-Button with CSS
 * - Misc: Update Screenshots
 * - Misc: Create Tutorials
 *
 * 0.2.3
 * - Code: Fixed-Capability-Bug
 *
 * 0.2.2
 * - Code: Fixed Standard-CSS-Bug
 * - Code: Fixed Settings-Page-Bug
 *
 * 0.2.1
 * - Code: Fixed display bug in shortcode
 * - Code: Fixed Standard-CSS-Bug
 *
 * 0.2
 * - Feature: Made complete Configurable
 * - Feature: Article Pictures in Posts
 * - Code: Clean-Ups, Made Codex-Conform
 *
 * 0.1
 * - Initial Release
 */

if ( ! class_exists( 'WP_Liveticker' ) ) {
	
	if ( function_exists( 'add_filter' ) )
		add_filter( 'plugins_loaded' ,  array( 'WP_Liveticker', 'get_instance' ) );
	
	class WP_Liveticker {
		
		/**
		* The plugins textdomain
		*
		* @static
		* @access	public
		* @since	0.5
		* @var		string
		*/
		public static $textdomain = '';
		
		/**
		 * The plugins textdomain path
		 *
		 * @static
		 * @access	public
		 * @since	0.5
		 * @var		string
		 */
		public static $textdomainpath = '';
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.5
		 * @var		NULL | WP_Liveticker
		 */
		private static $instance = NULL;
		
		/**
		 * local cache dir
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @var		string
		 */
		public static $cache_dir = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.5
		 * @return	WP_Liveticker
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
		 * @uses	add_filter, is_admin, current_user_can, get_option, update_option
		 * 			plugin_dir_path, sprintf, __
		 * @return	void
		 */
		public function __construct () {
			
			// Textdomain
			self::$textdomain = $this->get_textdomain();
			// Textdomain Path
			self::$textdomainpath = $this->get_domain_path();
			// Initialize the localization
			$this->load_plugin_textdomain();
			
			// Backward-Compatibility
			if ( ! get_option( 'wp_liveticker_template' ) )
				update_option( 'wp_liveticker_template', get_option( 'wp_liveticker_settings' ) );
			
			// Setting the Cache dir
			self::$cache_dir = plugin_dir_path( __FILE__ ) . 'cache/';
			
			// Setting Admin Notice
			if ( ! is_writeable( self::$cache_dir ) && is_admin() && current_user_can( 'manage_options' ) ) {
				?><div class="error"><p><?php echo sprintf( __( 'Please make sure, that <strong>%s</strong> is writeable!', self::$textdomain ), self::$cache_dir ); ?></p></div><?php
			}
			
			// Load the features
			$this->load_features();
		}
		
		/**
		 * Get a value of the plugin header
		 *
		 * @since 0.5
		 * @uses get_plugin_data, ABSPATH
		 * @param string $value
		 * @return string The plugin header value
		 */
		protected function get_plugin_header( $value = 'TextDomain' ) {
			
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		
			$plugin_data = get_plugin_data( __FILE__ );
			$plugin_value = $plugin_data[ $value ];
		
			return $plugin_value;
		}
		
		/**
		 * Get the Textdomain
		 *
		 * @access	public
		 * @since	0.5
		 * @return	string The plugins textdomain
		 */
		public function get_textdomain() {
			
			return $this->get_plugin_header( 'TextDomain' );
		}
		
		/**
		 * Get the Textdomain Path where the language files are located
		 *
		 * @access	public
		 * @since	0.5
		 * @return	string The plugins textdomain path
		 */
		public function get_domain_path() {
			
			return $this->get_plugin_header( 'DomainPath' );
		}
		
		/**
		 * Load the localization
		 *
		 * @access	public
		 * @since	0.5
		 * @uses	load_plugin_textdomain, plugin_basename
		 * @return	void
		 */
		public function load_plugin_textdomain() {
			
			load_plugin_textdomain( self::$textdomain, FALSE, dirname( plugin_basename( __FILE__ ) ) . self::$textdomainpath );
		}
		
		/**
		 * Returns array of features, also
		 * saves them in the class var $loaded_modules.
		 * Scans the plugins subfolder "/features"
		 *
		 * @access	protected
		 * @since	0.5
		 * @return	void
		 */
		protected function load_features() {
		
			// Get dir
			$handle = opendir( dirname( __FILE__ ) . '/features' );
			if ( ! $handle )
				return;
				
			// Loop through directory files
			while ( FALSE != ( $plugin = readdir( $handle ) ) ) {
		
				// Is this file for us?
				if ( '.php' == substr( $plugin, -4 ) ) {
		
					// Save in class var
					$this->loaded_modules[ substr( $plugin, 0, -4 ) ] = TRUE;
		
					// Include module file
					require_once dirname( __FILE__ ) . '/features/' . $plugin;
				}
			}
			closedir( $handle );
		}
		
		/**
		 * Load the default settings
		 *
		 * @access	public
		 * @since	0.1
		 * @return	array $settings
		 */
		public function get_default_settings() {
			
			$settings = array(
				'interval'		=> 25000
			);
			
			return $settings;
		}
		
		/**
		 * Load the default template
		 *
		 * @access	public
		 * @since	0.5
		 * @return	array $template
		 */
		public function get_default_template() {
			
			$template = array(
				'headline'		=> 'margin: 3px 0 !important;',
				'space_line'	=> "padding: 0px;\nborder-top: 1px dashed #ccc;",
				'the_p_tag'		=> "margin: 0 !important;\npadding: 0 !important;",
				'ticker'		=> "padding: 5px;\noverflow: auto;\nmargin: 10px 0;\nmin-height: 150px;\nmax-height: 500px;\nbackground: #f1f1f1;\nborder: 1px dashed #ccc;",
				'image'			=> "float: left;\nborder: 0;\npadding: 0;\nmargin: 0 5px 5px 0;",
				'ticker_rss'	=> "border: 0;\nfloat: right;\nmargin-right: 25px;\nmargin-bottom: -25px;",
			);
				
			return $template;
		}
	}
}