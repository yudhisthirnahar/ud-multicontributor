<?php
/**
 * Common functions.
 */
namespace UDMC;

/**
 * Encrypt or Decrypt string.
 *
 * @since 1.0.0
 *
 * @param string $string String passed for encryption or decryption.
 * @param string $action Type of action : 'e' for encrypt and 'd' for decrypt.
 *
 * @return string $output Encoded/Decoded output.
 */
function udmc_crypt( $string, $action = 'e' ) {
	$secret_key = 'ud_secret_key';
	$secret_iv  = 'ud_secret_key_iv';

	$output         = false;
	$encrypt_method = 'AES-256-CBC';
	$key            = hash( 'sha256', $secret_key );
	$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if ( 'e' === $action ) {
		$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	} elseif ( 'd' === $action ) {
		$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}
