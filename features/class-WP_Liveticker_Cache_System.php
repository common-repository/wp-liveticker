<?php
/**
 * Feature Name:	WP Liveticker Cache System
 * Description:		This feature implements the internal caching system
 * Version:			0.1.1
 * Author:			DasLlama
 * Author URI:		http://dasllama.github.com
 * Licence:			CC-BY-SA
 *
 * Changelog
 * 
 * 0.1.1
 * - Code: Only save tick on publish
 * - Code: Remove tick if it is setted to draft/trash/delete
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'WP_Liveticker_Cache_System' ) ) {

	class WP_Liveticker_Cache_System extends WP_Liveticker {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.1
		 * @var		NULL | WP_Liveticker_Cache_System
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @return	WP_Liveticker_Cache_System
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
		 * @uses	add_filter
		 * @return	void
		 */
		public function __construct () {
			
			// Add the scripts
			if ( 'wp-liveticker-posts' == get_post_type( $_GET[ 'post' ] ) && is_admin() )
				$this->enqueue_script();
			
			// Save a cache file if the tick has been saved
			add_filter( 'save_post', array( $this, 'save_tick' ) );
		}
		
		/**
		 * Enqueue Scripts
		 * 
		 * @access	public
		 * @since	0.1
		 * @uses	wp_enqueue_script, plugin_dir_url
		 * @return	void
		 */
		public function enqueue_script() {

			wp_enqueue_script( 'WP_Liveticker_Save_Tick', plugin_dir_url( __FILE__ ) . '../js/class-WP_Liveticker_Save_Tick.js', array( 'jquery' ) );
			$vars = $this->load_js_vars();
			wp_localize_script( 'WP_Liveticker_Save_Tick', 'WP_Liveticker_Save_Tick_vars', $vars );
		}
		
		/**
		 * load javasrcipt variables
		 * 
		 * @access	public
		 * @since	0.1
		 * @return	array
		 */
		public function load_js_vars() {
			
			$vars = array(
				'choose_a_ticker'	=> __( 'Please choose a ticker', parent::$textdomain ),
			);
		
			return $vars;
		}
		
		/**
		 * Saves the tick to a cache file
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	DOING_AUTOSAVE, current_user_can
		 * @return	void
		 */
		public function save_tick() {
			
			// Preventing Autosave, we don't want that
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
			
			// We don't need to save because there is Post Array
			if ( 0 >= count( $_POST ) )
				return;
			
			// Do we have a ticker post
			if ( 'wp-liveticker-posts' != $_POST[ 'post_type' ] )
				return;
			
			// Check permissions
			if ( ! current_user_can( 'edit_post', $_POST[ 'ID' ] ) )
				return;
			
			// Only save the tick if we publish the post
			if ( 'publish' != get_post_status( $_POST[ 'ID' ] ) ) {
				$this->remove_tick( $_POST[ 'ID' ] );
				return;
			}
			
			// Generate HTML
			$html .= '<h3>' . get_the_time( 'd.m.Y H:i', $_POST[ 'ID' ] ) . ' - ' . $_POST[ 'post_title' ]. '</h3>';
			if ( has_post_thumbnail( $_POST[ 'ID' ] ) ) {
				$thumbnail_id = get_post_thumbnail_id( $_POST[ 'ID' ] );
				$html .= '<a href="' . wp_get_attachment_url( $thumbnail_id ) . '" title="' . esc_attr( $_POST[ 'post_title' ] ) . '" rel="lightbox">' . get_the_post_thumbnail( $_POST[ 'ID' ], 'thumbnail' ) . '</a>';
			}
			$html .= '<p class="' . $_POST[ 'ID' ] . '">' . wpautop( $_POST[ 'content' ] ) . '</p>';
			if ( has_post_thumbnail( $_POST[ 'ID' ] ) ) {
				$html .= '<br style="clear: both;" />';
			}
			$html .= '<hr />';
			
			// Write the cache for each ticker-category
			foreach ( $_POST[ 'tax_input' ][ 'wp-liveticker' ] as $ticker ) {
				if ( 0 == $ticker )
					continue;
				
				// generate filename
				$term = get_term_by( 'id', $ticker, 'wp-liveticker' );
				$term_slug = $term->slug;
				
				$filename = 'ticker_' . $term->slug . '_' . $_POST[ 'ID' ] . '.cache';
				$this->write_cache( $filename, $html );
			}
		}
		
		/**
		 * Removes the tick
		 * 
		 * @access	public
		 * @since	0.1.1
		 * @uses	get_term_by
		 * @param	int $post_id The post ID
		 * @return	void
		 */
		public function remove_tick( $post_id ) {
			
			$tickers = wp_get_post_terms( $post_id, 'wp-liveticker' );
			
			foreach ( $tickers as $ticker ) {
			
				$filename = 'ticker_' . $ticker->slug . '_' . $post_id . '.cache';
				$cache_file = parent::$cache_dir . $filename;
				
				if ( file_exists( $cache_file ) )
					@unlink( $cache_file );
			}
		}
		
		/**
		 * write string to cache file
		 *
		 * writes a string to a cache file. If cache file is not writable,
		 * or cachdir is not present, it does nothing. Uses cachedir defined in Constructor
		 *
		 * @access	public
		 * @since	0.1
		 * @param	string $file_name name of the cache file
		 * @param	string $data string with content
		 * @return	void
		 */
		public function write_cache( $file_name, $data ) {
			
			$cache_file = parent::$cache_dir . $file_name;
			
			// if the file is not writable, return
			if ( file_exists( $cache_file ) && ! is_writable( $cache_file ) || ! is_writable( parent::$cache_dir ) )
				return;
			
			// cache only, if directory is writable, else do nothing, not to crash the whole website
			if ( is_writable( parent::$cache_dir ) )
				file_put_contents( $cache_file, $data );
		}
		
		/**
		 * returns the cached file
		 *
		 * @access	public
		 * @since	0.1
		 * @param	string $file_name name of the cache file
		 * @return	string the content of the file
		 */
		public static function read_cache( $file_name ) {
			
			if ( file_exists( parent::$cache_dir . $file_name ) )
				return file_get_contents( parent::$cache_dir . $file_name );
		}
		
		/**
		 * this function returns the list of the files which are in the cache
		 *
		 * @access	public
		 * @since	0.1
		 * @param	string $search search the filename
		 * @return	array $cached_files the list of the files which are in the cache
		 */
		public function get_cached_files( $search = NULL ) {
			
			// Set list empty
			$cached_files = array();
			
			// open handle
			$handle = opendir( parent::$cache_dir );
			if ( ! $handle )
				return;
			
			// Loop through directory files
			while ( FALSE != ( $cached_file = readdir( $handle ) ) ) {
			
				// Is this file for us?
				if ( '.cache' == substr( $cached_file, -6 ) ) {
					
					if ( is_null( $search ) )
						$cached_files[] = $cached_file;
					else
						if ( 0 < strpos( $cached_file, $search ) )
							$cached_files[] = $cached_file;
				}
			}
			closedir( $handle );
			
			return $cached_files;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Liveticker_Cache_System::get_instance();
}