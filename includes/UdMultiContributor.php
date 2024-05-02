<?php

namespace UDMC;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/* Main Class */
if ( ! class_exists( 'UdMultiContributer' ) ) {
	final class UdMultiContributor {
		private static $class_instance = null;

		/**
		 * Constructor method to add the required hooks.
		 *
		 * @since 1.0.0
		 *
		 */
		public function __construct() {
			$this->setup();
		}

		/**
		 * Init plugin.
		 *
		 * @since 1.0.0
		 */
		public function setup() {
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_ud_meta_box' ), 10, 3 );
			add_filter( 'the_content', array( $this, 'contributors_content_filter' ), 999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		}

		/**
		 * Load text domain.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'ud-multicontributor', false, dirname( UDMC_PLUGIN_BASE_NAME ) . '/languages' );
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts_styles() {

			// Check if SCRIPT_DEBUG is true.
			$minified = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// Check if not in the admin area.
			if ( ! is_admin() ) {
				wp_register_style( 'ud-multicontributor-style', UDMC_PLUGIN_URL . "/assets/frontend/css/style{$minified}.css", array(), UDMC_PLUGIN_VERSION, 'all' );
				wp_enqueue_style( 'ud-multicontributor-style' );
			}
		}

		/**
		 * Add custom meta box.
		 *
		 * @since 1.0.0
		 */
		function add_custom_meta_box() {
			error_log('add_custom_meta_box');
			add_meta_box( 'udmc-meta-box', esc_html__( 'Contributors', 'ud-multicontributor' ), array(
				$this,
				'custom_meta_box_markup'
			), 'post', 'side', 'high', null );
		}

		/**
		 * Custom meta box content.
		 *
		 * @since 1.0.0
		 */
		function custom_meta_box_markup( $object ) {
			error_log(999);
			wp_nonce_field( 'udmc_save_contrubutors', 'udmc_nonce_field' );
			$authors = get_users();
			if ( empty( $authors ) ) {
				return;
			}
			?>
			<ul>
				<?php
				$db_multi_contributer_array = array();
				$db_multi_contributer       = get_post_meta( get_the_ID(), '_udmc_multi_contributer', true );
				if ( ! empty( $db_multi_contributer ) ) {
					$db_multi_contributer_array = explode( ',', $db_multi_contributer );
				}

				foreach ( $authors as $author ) {
					$encoded_user_id = udmc_crypt( $author->data->ID, 'e' );
					?>
					<li>
						<label>
							<input <?php echo esc_attr( in_array( $author->data->ID, $db_multi_contributer_array, true ) ? 'checked' : '' ); ?>
							type='checkbox' name='chk_multi_contributer[]' class='cls_chk_multi_contributer'
							value=<?php echo esc_attr( $encoded_user_id ); ?>><span
							style="vertical-align:bottom"><?php echo esc_attr( $author->data->display_name ); ?></span><img
							style="width:18px;height:18px;margin-left:8px;vertical-align:bottom;"
							src=<?php echo esc_url( get_avatar_url( $author->data->ID ) ); ?>>
						</label>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}

		/**
		 * Save custom meta box form.
		 *
		 * @param int $post_id The post ID.
		 * @param object $post The post object.
		 * @param bool $update Whether this is an existing post being updated or not.
		 */
		function save_ud_meta_box( $post_id, $post, $update ) {

			// Verify nonce
			if ( ! isset( $_POST['udmc_nonce_field'] ) || ! wp_verify_nonce( $_POST['udmc_nonce_field'], 'udmc_save_contrubutors' ) ) {
				return $post_id;
			}

			try {
				// Check if it's a default post type.
				if ( 'post' !== $post->post_type ) {
					wp_die( esc_html__( 'Error: Invalid post type.', 'ud-multicontributor' ) );
				}

				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_die( esc_html__( 'Error: You do not have permission to perform this action.', 'ud-multicontributor' ) );
				}
			} catch ( \Exception $e ) {
				// Handle the exception here, for example:
				echo $e->getMessage();
				return $post_id;
			}
			

			if ( ! empty( $_POST['chk_multi_contributer'] ) ) {
				$csv_multi_contributer = '';
				$post_chk_multi_contributer = array_map( 'sanitize_text_field', wp_unslash( $_POST['chk_multi_contributer'] ) );
				if ( isset( $post_chk_multi_contributer ) ) {
					if ( ! empty( $post_chk_multi_contributer ) ) {
						foreach ( $post_chk_multi_contributer as $each_contributer ) {
							$decoded_user_id = udmc_crypt( $each_contributer, 'd' );
							if ( empty( $csv_multi_contributer ) ) {
								$csv_multi_contributer = $decoded_user_id;
							} else {
								$csv_multi_contributer = $csv_multi_contributer . ',' . $decoded_user_id;
							}
						}
						update_post_meta( $post_id, '_udmc_multi_contributer', $csv_multi_contributer );
					}
				}
			} else {
				return $post_id;
			}
		}

		/**
		 * Append contributor box to posts
		 *
		 * @since 1.0.0
		 *
		 * @param string $content The content.
		 */
		function contributors_content_filter( $content ) {

			// Check if it's a single post page.
			if ( is_single() ) {
				$db_multi_contributer_array = array();
				$db_multi_contributer       = get_post_meta( get_the_ID(), '_udmc_multi_contributer', true );
				if ( ! empty( $db_multi_contributer ) ) {
					$db_multi_contributer_array = explode( ',', $db_multi_contributer );
				}

				if ( count( $db_multi_contributer_array ) > 0 ) {
					$authors = get_users( array( 'include' => $db_multi_contributer_array ) );
					if ( ! empty( $authors ) ) {
						ob_start();
						?>
						<div class='udmc-multicontributors'>
							<h3 class='udmc-heading'><?php esc_html_e( 'Contributors', 'ud-multicontributor' ); ?>:</h3>
							<ul>
								<?php
								foreach ( $authors as $author ) {
									$link = get_the_author_meta( 'url', $author->data->ID );
									$link = ! empty( $link ) ? $link : 'javascript:void(0);';
									?>
									<li>
										<a class='udmc-contributor-link' href="<?php echo $link; ?>">
											<div class='udmc-label'>
												<img src="<?php echo get_avatar_url( $author->data->ID ); ?>">
												<span><?php echo $author->data->display_name; ?></span>
											</div>
										</a>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
						<?php
						return $content . ob_get_clean();
					}
				}
			}

			// Returns the content.
			return $content;
		}

		/**
		 * Get class instance.
		 *
			* @since 1.0.0
			*
			* @return object instance of the RestrictRegistrations class.
		 */
		public static function get_instance() {
			if ( is_null( self::$class_instance ) ) {
				self::$class_instance = new self();
			}

			return self::$class_instance;
		}
	}
}