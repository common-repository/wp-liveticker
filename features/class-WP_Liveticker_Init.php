<?php
/**
 * Feature Name:	WP Liveticker Init
 * Description:		This feature inits the plugins post type and taxonomy
 * Version:			0.1
 * Author:			DasLlama
 * Author URI:		http://dasllama.github.com
 * Licence:			CC-BY-SA

* Changelog
*
* 0.1
* - Initial Commit
*/

if ( ! class_exists( 'WP_Liveticker_Init' ) ) {

	class WP_Liveticker_Init extends WP_Liveticker {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.1
		 * @var		NULL | WP_Liveticker_Init
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @return	WP_Liveticker_Init
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
		 * @global	$pagenow Current Page Locator
		 * @return	void
		 */
		public function __construct () {
			global $pagenow;
			
			// Add Custom Post Type
			add_filter( 'init', array( $this, 'init_post_type' ) );
			
			// Init Taxonomy for the tickers
			add_filter( 'init', array( $this, 'init_taxonomy' ) );
			
			// Custom Columns
			if ( 'edit.php' == $pagenow && 'wp-liveticker-posts' == $_GET[ 'post_type' ] ) {
				add_filter( 'manage_posts_custom_column', array( $this, 'custom_column_content' ) );
				add_filter( 'manage_edit-wp-liveticker-posts_columns', array( $this, 'custom_column_head' ) );
			}
		}
		
		/**
		 * Add Costum Collumn Head
		 * 
		 * @access	public
		 * @since	0.1
		 * @return	void
		 */
		public function custom_column_head ( $defaults ) {
			$defaults[ 'ticker' ] = __( 'Ticker', parent::$textdomain );
			return $defaults;
		}
		
		/**
		 * Add Costum Collumn Content
		 * 
		 * @access	public
		 * @since	0.1
		 * @return	void
		 */
		public function custom_column_content ( $column_name ) {
			global $post;
			
			if( 'ticker' == $column_name )
				echo get_the_term_list( $post -> ID, 'wp-liveticker', '', ', ', '' );
		}
		
		/**
		 * Initialize Taxonomy
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	register_taxonomy, __
		 * @return	void
		 */
		public function init_taxonomy () {
			
			$labels = array(
				'name'				=> __( 'Initiate Ticker', parent::$textdomain ),
				'all_items'			=> __( 'All Ticker', parent::$textdomain ),
				'edit_item'			=> __( 'Edit Ticker', parent::$textdomain ),
				'parent_item'		=> __( 'Parent Ticker', parent::$textdomain ),
				'update_item'		=> __( 'Update Ticker', parent::$textdomain ),
				'search_items'		=> __( 'Search Ticker', parent::$textdomain ),
				'add_new_item'		=> __( 'Add Ticker', parent::$textdomain ),
				'singular_name'		=> __( 'Ticker', parent::$textdomain ),
				'new_item_name'		=> __( 'Add Ticker', parent::$textdomain ),
				'popular_items'		=> __( 'Popular Ticker', parent::$textdomain ),
				'parent_item_colon'	=> __( 'Parent Ticker:', parent::$textdomain ),
			);
			
			$taxonomy_args = array(
				'public'			=> TRUE,
				'query_var'			=> 'ticker',
				'show_ui'			=> TRUE,
				'show_tagcloud'		=> FALSE,
				'hierarchical'		=> TRUE,
				'show_in_nav_menus'	=> TRUE,
				'labels'			=> $labels,
			);
			
			register_taxonomy( 'wp-liveticker', array( 'wp-liveticker-posts' ), $taxonomy_args );
		}
		
		/**
		 * Initialize Post Type
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	register_post_type, __
		 * @return	void
		 */
		public function init_post_type() {
			
			$labels = array(
				'name'					=> __( 'WP Liveticker', parent::$textdomain ),
				'add_new'				=> __( 'Add Tick', parent::$textdomain ),
				'new_item'				=> __( 'New Tick', parent::$textdomain ),
				'all_items'				=> __( 'Ticks', parent::$textdomain ),
				'edit_item'				=> __( 'Edit Tick', parent::$textdomain ),
				'view_item'				=> __( 'View Tick', parent::$textdomain ),
				'not_found'				=> __( 'There are no Ticks matching the search criterias', parent::$textdomain ),
				'menu_name'				=> __( 'Liveticker', parent::$textdomain ),
				'add_new_item'			=> __( 'Add Tick', parent::$textdomain ),
				'search_items'			=> __( 'Search Ticks', parent::$textdomain ),
				'singular_name'			=> __( 'Tick', parent::$textdomain ),
				'parent_item_colon'		=> __( 'Parent Tick', parent::$textdomain ),
				'not_found_in_trash'	=> __( 'There are no Ticks matching the search criterias', parent::$textdomain ),
			);
			
			$supports = array(
				'title',
				'editor',
				'thumbnail',
				'revision'
			);
			
			$post_type_args = array(
				'public' 				=> TRUE,
				'labels'				=> $labels,
				'rewrite'				=> TRUE,
				'show_ui' 				=> TRUE, 
				'supports' 				=> $supports,
				'query_var' 			=> TRUE,
				'has_archive'			=> TRUE,
				'hierarchical' 			=> FALSE,
				'menu_position' 		=> NULL,
				'capability_type' 		=> 'post',
				'publicly_queryable'	=> TRUE,
			);
			
			register_post_type( 'wp-liveticker-posts', $post_type_args );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Liveticker_Init::get_instance();
}