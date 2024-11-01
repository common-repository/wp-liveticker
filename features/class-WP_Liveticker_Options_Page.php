<?php
/**
 * Feature Name:	WP Liveticker Options Page
 * Description:		This feature inits the plugins options page
 * Version:			0.1
 * Author:			DasLlama
 * Author URI:		http://dasllama.github.com
 * Licence:			CC-BY-SA

* Changelog
*
* 0.1
* - Initial Commit
*/

if ( ! class_exists( 'WP_Liveticker_Options_Page' ) ) {

	class WP_Liveticker_Options_Page extends WP_Liveticker {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.1
		 * @var		NULL | WP_Liveticker_Options_Page
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @return	WP_Liveticker_Options_Page
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
			
			// Adding the Menu
			add_filter( 'admin_menu', array( $this, 'admin_menu' ) );
		}
		
		/**
		 * Adding the submenu page
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	add_submenu_page
		 * @return	void
		 */
		public function admin_menu () {
			add_submenu_page( 'edit.php?post_type=wp-liveticker-posts', __( 'Settings', parent::$textdomain ), __( 'Settings', parent::$textdomain ), 'manage_options', 'wp-liveticker-settings', array( $this, 'settings_page' ) );
		}
		
		/**
		 * The Settings Page
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	__, _e, admin_url
		 * @return	void
		 */
		public function settings_page() {
			
			if ( isset( $_POST[ 'save_settings' ] ) ) {
				update_option( 'wp_liveticker_settings', $_POST[ 'settings' ] );
				?> <div class="updated"><p><?php _e( 'Settings has been saved', parent::$textdomain ); ?></p></div><?php
			}
			
			if ( isset( $_POST[ 'save_template' ] ) ) {
				update_option( 'wp_liveticker_template', $_POST[ 'template' ] );
				?> <div class="updated"><p><?php _e( 'Template has been saved', parent::$textdomain ); ?></p></div><?php
			}
			
			if ( isset( $_GET[ 'restore' ] ) ) {
				delete_option( 'wp_liveticker_settings' );
				delete_option( 'wp_liveticker_template' );
				?><div class="updated"><p><?php _e( 'Defaults has been restored', parent::$textdomain ); ?></p></div><?php
			}
			
			// Devine the tabs
			$tabs = array(
				'general'	=> __( 'General Settings', parent::$textdomain ),
				'template'	=> __( 'Template', parent::$textdomain ),
			);
			
			// set the current tab to the first element, if no tab is in request
			if ( array_key_exists( $_REQUEST[ 'tab' ], $tabs ) ) {
				$current_tab = $_REQUEST[ 'tab' ];
				$current_tabname = $tabs[ $current_tab ];
			} else {
				$current_tab = current( array_keys( $tabs ) );
				$current_tabname = $tabs[ $current_tab ];
			}
			
			?>
			<div class="wrap">
				<h2 class="nav-tab-wrapper"><?php
					_e( 'Liveticker Settings ', parent::$textdomain );

					foreach( $tabs as $tab_handle => $tabname ) {
						// set the url to the tab
						$url = admin_url( 'edit.php?post_type=wp-liveticker-posts&page=wp-liveticker-settings&tab=' . $tab_handle );
						// check, if this is the current tab
						$active = ( $current_tab == $tab_handle ) ? ' nav-tab-active' : '';
						printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $tabname );
					}
				?></h2>
				
				<div id="poststuff" class="metabox-holder has-right-sidebar">
				
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="wp-liveticker-inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Powered by', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p style="text-align: center;"><a href="http://inpsyde.com"><img src="http://inpsyde.com/wp-content/themes/inpsyde/images/logo.jpg" style="border: 7px solid #fff;" /></a></p>
									<p><?php _e( 'This plugin is powered by <a href="http://inpsyde.com">Inpsyde.com</a> - Your expert for WordPress, BuddyPress and bbPress.', parent::$textdomain ); ?></p>
								</div>
							</div>
							
							<div id="wp-liveticker-inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Usage & Help', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p><?php _e( 'The usage of the ticker is really simple. It comes with a so called <a href="http://codex.wordpress.org/Shortcode">shortcode</a> namend <em>ticker</em>. So here are the steps to create a ticker:', parent::$textdomain ); ?></p>
									<ol>
										<li><?php _e( 'Initiate a <em>Ticker</em> by using the same called menu entry.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Now create an initial Ticker Post and allocate it to the initiated Ticker. It works like the well known categories in the posts.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Publish the Ticker Post.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Go to a post or a page where you want to place the Ticker. Place the shortcode <em>[ticker slug]</em> in the text.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Replace the word <em>slug</em> with the actual slug from the initiated Ticker you created in step one.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Save the post.', parent::$textdomain ); ?></li>
										<li><?php _e( 'Ready! Now you can create Ticker Posts just by clicking <em>Add New</em> in the <em>Ticker Post</em>-Section and your visitors can see your ... erm ... tickles!', parent::$textdomain ); ?></li>
									</ol>
									<p><?php _e( 'With WP-Liveticker you can initiate unlimited Ticker on your Posts - not in a single post, but in different posts.', parent::$textdomain ); ?></p>
									<p><?php _e( 'If you need more help, contact me:', parent::$textdomain ); ?> <a href="mailto:t.herzog@inpsyde.com">t.herzog@inpsyde.com</a></p>
								</div>
							</div>
							
							<div id="wp-liveticker-inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Restore Defaults', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p><?php _e( 'If you made some mistakes in styling and you donot know a way back then you are able to restore the default settings. Your settings will be lost.', parent::$textdomain ); ?></p>
									<p><a href="?post_type=wp-liveticker-posts&page=wp-liveticker-settings&restore" class="button-primary"><?php _e( 'Restore Defaults', parent::$textdomain ); ?></a></p>
								</div>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
								<?php $this->show_tab( array( $this , $current_tab . '_tab' ), $current_tabname ); ?>
							</div>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
		
		/**
		 * Shows the tab, and calls the function for the content of the tab
		 *
		 * @access	private
		 * @since	0.1
		 * @param	string $tab_function function to call for tab content
		 * @param	string $title title of the tab
		 * @return	void
		 */
		private function show_tab( $tab_function, $title ) {
			if ( is_callable( $tab_function ) )
				call_user_func( $tab_function ); 
		}
		
		/**
		 * The General Settings Tab
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	get_option, _e
		 * @return	void
		 */
		public function general_tab() {
			
			$settings = get_option( 'wp_liveticker_settings' );
			if ( ! $settings )
				$settings = $this->get_default_settings();

			?>
			<div id="settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'General Settings', parent::$textdomain ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'In this version, we do not have very detailled settings to change. In future we will have a high configurable live-ticker-plugin. For now, we just have an interval for the refresh and some CSS-Stuff.', parent::$textdomain ); ?></p>
					<form action="edit.php?post_type=wp-liveticker-posts&page=wp-liveticker-settings&tab=general" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="settings[interval]"><?php _e( 'Refresh-Interval', parent::$textdomain ); ?></label>
									</th>
									<td>
										<input id="settings[interval]" name="settings[interval]" type="text" value="<?php echo $settings[ 'interval' ]; ?>" tabindex="1" />
										<p><small><?php _e( 'The Ticker-Box refreshs automatically. For that, we need an interval setted here. The value have to be set in milliseconds (Wished seconds x 100).', parent::$textdomain ); ?></small></p>
									</td>
								</tr>
							</tbody>
						</table>
						<input name="save_settings" type="submit" class="button-primary" tabindex="3" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
						<br class="clear" />
					</form>
				</div>
			</div>
			<?php
		}
		
		/**
		 * The Template Settings Tab
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	get_option, _e
		 * @return	void
		 */
		public function template_tab() {
			
			$template = get_option( 'wp_liveticker_template' );
			if ( ! $template )
				$template = $this->get_default_template();
			
			?>
			<div id="settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Template Settings', parent::$textdomain ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'You are able to manipulate the CSS of the Ticker-Container and its content. Just play around, you still can restore the default settings. Remember that these style-settings only affects the Ticker-Style.', parent::$textdomain ); ?></p>
					<form action="edit.php?post_type=wp-liveticker-posts&page=wp-liveticker-settings&tab=template" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="template[ticker]"><?php _e( 'Ticker-CSS', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[ticker]" name="template[ticker]" tabindex="1" rows="10" class="large-text" tabindex="2"><?php echo $template[ 'ticker' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="template[headline]"><?php _e( 'Headline-CSS', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[headline]" name="template[headline]" tabindex="2" rows="10" class="large-text" tabindex="3"><?php echo $template[ 'headline' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="template[space_line]"><?php _e( 'Space-Line-CSS', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[space_line]" name="template[space_line]" tabindex="3" rows="10" class="large-text" tabindex="4"><?php echo $template[ 'space_line' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="template[the_p_tag]"><?php _e( 'P-Tag-CSS', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[the_p_tag]" name="template[the_p_tag]" tabindex="4" rows="10" class="large-text" tabindex="5"><?php echo $template[ 'the_p_tag' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="template[image]"><?php _e( 'Image-CSS', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[image]" name="template[image]" tabindex="5" rows="10" class="large-text" tabindex="6"><?php echo $template[ 'image' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="template[ticker_rss]"><?php _e( 'RSS Button', parent::$textdomain ); ?></label>
									</th>
									<td>
										<textarea id="template[ticker_rss]" name="template[ticker_rss]" tabindex="5" rows="10" class="large-text" tabindex="7"><?php echo $template[ 'ticker_rss' ]; ?></textarea><br />
									</td>
								</tr>
							</tbody>
						</table>
						<input name="save_template" type="submit" class="button-primary" tabindex="8" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
						<br class="clear" />
					</form>
				</div>
			</div>
			<?php
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Liveticker_Options_Page::get_instance();
}