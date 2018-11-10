<?php
/**
 * Plugin Name: Ud MultiContributer
 * Plugin URI: https://github.com/yudhisthirnahar/wordpress_post_multicontributers
 * Description: Post MultiContributor
 * Version: 1.0
 * Author: Yudhisthir Nahar
 * Author URI: https://github.com/yudhisthirnahar
 *
 * @package Ud MultiContributer
 */

/**
 * Encrypt or Decrypt string.
 *
 * @param string $string as string passed for encryption or decryption.
 * @param string $action e for encrypt and d for decrypt.
 */
function ud_crypt( $string, $action = 'e' ) {
	// you may change these values to your own.
	$secret_key = 'ud_secret_key';
	$secret_iv  = 'ud_secret_key_iv';

	$output         = false;
	$encrypt_method = 'AES-256-CBC';
	$key            = hash( 'sha256', $secret_key );
	$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if ( $action === 'e' ) {
		$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	} elseif ( $action === 'd' ) {
		$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}

/**
 * Custom meta box content.
 *
 * @param object $object Object.
 */
function custom_meta_box_markup( $object ) {
	wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );
	$authors = get_users( array( 'role__in' => array( 'administrator', 'editor', 'author' ) ) );
	$i       = 0;

	if ( empty( $authors ) ) {
		return;
	}

	// Get all users list.
	foreach ( $authors as $author ) {
		$author_list[ $i ]['id']             = $author->data->ID;
		$author_list[ $i ]['name']           = $author->data->display_name;
		$author_list[ $i ]['get_avatar_url'] = get_avatar_url( $author->data->ID );
		$i ++;
	}
	?>
	<ul>
	<?php
	$db_multi_contributer_array = array();
	$db_multi_contributer       = get_post_meta( get_the_ID(), 'multi_contributer', true );
	if ( ! empty( $db_multi_contributer ) ) {
		$db_multi_contributer_array = explode( ',', $db_multi_contributer );
	}

	foreach ( $author_list as $author ) {
		$encoded_user_id = ud_crypt( $author['id'], 'e' );
		?>
		<li>
		<label><input <?php echo esc_attr( in_array( $author['id'], $db_multi_contributer_array, true ) ? 'checked' : '' ); ?> type='checkbox' name='chk_multi_contributer[]' class='cls_chk_multi_contributer' value=<?php echo esc_attr( $encoded_user_id ); ?>><span style="vertical-align:bottom"><?php echo esc_attr( $author['name'] ); ?></span><img style="width:18px;height:18px;margin-left:8px;vertical-align:bottom;" src=<?php echo esc_attr( $author['get_avatar_url'] ); ?>></label>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}

/**
 * Add custom meta box.
 */
function add_custom_meta_box() {
	add_meta_box( 'ud-meta-box', 'Contributors', 'custom_meta_box_markup', 'post', 'side', 'high', null );
}

add_action( 'add_meta_boxes', 'add_custom_meta_box' );

/**
 * Save custom meta box form.
 *
 * @param int  $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function save_ud_meta_box( $post_id, $post, $update ) {
	if ( ! isset( $_POST['meta-box-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['meta-box-nonce'] ) ), basename( __FILE__ ) ) ) {
		return $post_id;
	}
	if ( ! empty( $_POST['chk_multi_contributer'] ) ) {
		$csv_multi_contributer      = '';
		$post_chk_multi_contributer = array_map( 'sanitize_text_field', wp_unslash( $_POST['chk_multi_contributer'] ) );
		if ( isset( $post_chk_multi_contributer ) ) {
			if ( ! empty( $post_chk_multi_contributer ) ) {
				foreach ( $post_chk_multi_contributer as $each_contributer ) {
					$decoded_user_id = ud_crypt( $each_contributer, 'd' );
					if ( empty( $csv_multi_contributer ) ) {
						$csv_multi_contributer = $decoded_user_id;
					} else {
						$csv_multi_contributer = $csv_multi_contributer . ',' . $decoded_user_id;
					}
				}
					update_post_meta( $post_id, 'multi_contributer', $csv_multi_contributer );
			}
		}
	} else {
		return $post_id;
	}
}

add_action( 'save_post', 'save_ud_meta_box', 10, 3 );
add_filter( 'the_content', 'ud_contributors_content_filter', 20 );

/**
 * Append contributor box to posts
 *
 * @param string $content The content.
 */
function ud_contributors_content_filter( $content ) {
	$db_multi_contributer_array = array();
	$db_multi_contributer       = get_post_meta( get_the_ID(), 'multi_contributer', true );
	if ( ! empty( $db_multi_contributer ) ) {
		$db_multi_contributer_array = explode( ',', $db_multi_contributer );
	}

	if ( count( $db_multi_contributer_array ) > 0 ) { // Add image to the beginning of each page.
		$authors = get_users( array( 'include' => $db_multi_contributer_array ) );

		$i = 0;
		if ( ! empty( $authors ) ) {

			foreach ( $authors as $author ) {
				$author_list[ $i ]['id']             = $author->data->ID;
				$author_list[ $i ]['name']           = $author->data->display_name;
				$author_list[ $i ]['get_avatar_url'] = get_avatar_url( $author->data->ID );
				$i ++;
			}

			$contributorsdiv = "<div class='multicontributors' style='padding:10px;border: 3px dotted;'><h3>Contributors:</h3><ul>";
			foreach ( $author_list as $author ) {
				$contributorsdiv .= "<li><a href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "'><label><span style='vertical-align:bottom'>" . $author['name'] . "</span><img style='width:18px;height:18px;margin-left:8px;vertical-align:bottom;' src=" . $author['get_avatar_url'] . '></label></a></li>';
			}
			$contributorsdiv .= '</div></ul>';
			$content          = $content . $contributorsdiv;

			return $content;
		}
	}

	// Returns the content.
	return $content;
}
?>
