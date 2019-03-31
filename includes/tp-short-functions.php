<?php
/*
Plugin Name: TZ Portfolio
Plugin URI: https://www.tzportfolio.com/
Description: All you need for a Portfolio here. TZ Portfolio+ is an open source advanced portfolio plugin for WordPress
Version: 1.0.0
Author: TemPlaza, Sonny
Author URI: https://www.tzportfolio.com/
Text Domain: tz-portfolio
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.


//Make public functions without class creation


/**
 * Trim string by char length
 *
 *
 * @param $s
 * @param int $length
 *
 * @return string
 */
function tp_trim_string( $s, $length = 20 ) {
	$s = strlen( $s ) > $length ? substr( $s, 0, $length ) . "..." : $s;

	return $s;
}


/**
 * Get where user should be headed after logging
 *
 * @param string $redirect_to
 *
 * @return bool|false|mixed|string|void
 */
function tp_dynamic_login_page_redirect( $redirect_to = '' ) {

	$uri = tp_get_core_page( 'login' );

	if (!$redirect_to) {
		$redirect_to = TPApp()->permalinks()->get_current_url();
	}

	$redirect_key = urlencode_deep( $redirect_to );

	$uri = add_query_arg( 'redirect_to', $redirect_key, $uri );

	return $uri;
}


/**
 * Checks if session has been started
 *
 * @return bool
 */
function tp_is_session_started() {

	if ( php_sapi_name() !== 'cli' ) {
		if ( version_compare( phpversion(), '5.4.0', '>=' ) ) {
			return session_status() === PHP_SESSION_ACTIVE ? true : false;
		} else {
			return session_id() === '' ? false : true;
		}
	}

	return false;
}


/**
 * User clean basename
 *
 * @param $value
 *
 * @return mixed|void
 */
function tp_clean_user_basename( $value ) {

	$raw_value = $value;
	$value = str_replace( '.', ' ', $value );
	$value = str_replace( '-', ' ', $value );
	$value = str_replace( '+', ' ', $value );

	/**
	 * TPApp hook
	 *
	 * @type filter
	 * @title tp_clean_user_basename_filter
	 * @description Change clean user basename
	 * @input_vars
	 * [{"var":"$basename","type":"string","desc":"User basename"},
	 * {"var":"$raw_basename","type":"string","desc":"RAW user basename"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_filter( 'tp_clean_user_basename_filter', 'function_name', 10, 2 );
	 * @example
	 * <?php
	 * add_filter( 'tp_clean_user_basename_filter', 'my_clean_user_basename', 10, 2 );
	 * function my_clean_user_basename( $basename, $raw_basename ) {
	 *     // your code here
	 *     return $basename;
	 * }
	 * ?>
	 */
	$value = apply_filters( 'tp_clean_user_basename_filter', $value, $raw_value );

	return $value;
}


/**
 * @function tp_user_ip()
 *
 * @description This function returns the IP address of user.
 *
 * @usage <?php $user_ip = tp_user_ip(); ?>
 *
 * @return string The user's IP address.
 *
 * @example The example below can retrieve the user's IP address
 *
 * <?php
 *
 * $user_ip = tp_user_ip();
 * echo 'User IP address is: ' . $user_ip; // prints the user IP address e.g. 127.0.0.1
 *
 * ?>
 */
function tp_user_ip() {
	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * TPApp hook
	 *
	 * @type filter
	 * @title tp_user_ip
	 * @description Change User IP
	 * @input_vars
	 * [{"var":"$ip","type":"string","desc":"User IP"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_filter( 'tp_user_ip', 'function_name', 10, 1 );
	 * @example
	 * <?php
	 * add_filter( 'tp_user_ip', 'my_user_ip', 10, 1 );
	 * function my_user_ip( $ip ) {
	 *     // your code here
	 *     return $ip;
	 * }
	 * ?>
	 */
	return apply_filters( 'tp_user_ip', $ip );
}


/**
 * If conditions are met return true;
 *
 * @param $data
 *
 * @return bool
 */
function tp_field_conditions_are_met( $data ) {
	if (!isset( $data['conditions'] )) return true;

	$state = 1;

	foreach ($data['conditions'] as $k => $arr) {
		if ($arr[0] == 'show') {

			$val = $arr[3];
			$op = $arr[2];

			if (strstr( $arr[1], 'role_' ))
				$arr[1] = 'role';

			$field = tp_profile( $arr[1] );

			switch ($op) {
				case 'equals to':

					$field = maybe_unserialize( $field );

					if (is_array( $field ))
						$state = in_array( $val, $field ) ? 1 : 0;
					else
						$state = ( $field == $val ) ? 1 : 0;

					break;
				case 'not equals':

					$field = maybe_unserialize( $field );

					if (is_array( $field ))
						$state = !in_array( $val, $field ) ? 1 : 0;
					else
						$state = ( $field != $val ) ? 1 : 0;

					break;
				case 'empty':

					$state = ( !$field ) ? 1 : 0;

					break;
				case 'not empty':

					$state = ( $field ) ? 1 : 0;

					break;
				case 'greater than':
					if ($field > $val) {
						$state = 1;
					} else {
						$state = 0;
					}
					break;
				case 'less than':
					if ($field < $val) {
						$state = 1;
					} else {
						$state = 0;
					}
					break;
				case 'contains':
					if (strstr( $field, $val )) {
						$state = 1;
					} else {
						$state = 0;
					}
					break;
			}
		} else if ($arr[0] == 'hide') {

			$state = 1;
			$val = $arr[3];
			$op = $arr[2];

			if (strstr( $arr[1], 'role_' ))
				$arr[1] = 'role';

			$field = tp_profile( $arr[1] );

			switch ($op) {
				case 'equals to':

					$field = maybe_unserialize( $field );

					if (is_array( $field ))
						$state = in_array( $val, $field ) ? 0 : 1;
					else
						$state = ( $field == $val ) ? 0 : 1;

					break;
				case 'not equals':

					$field = maybe_unserialize( $field );

					if (is_array( $field ))
						$state = !in_array( $val, $field ) ? 0 : 1;
					else
						$state = ( $field != $val ) ? 0 : 1;

					break;
				case 'empty':

					$state = ( !$field ) ? 0 : 1;

					break;
				case 'not empty':

					$state = ( $field ) ? 0 : 1;

					break;
				case 'greater than':
					if ($field <= $val) {
						$state = 0;
					} else {
						$state = 1;
					}
					break;
				case 'less than':
					if ($field >= $val) {
						$state = 0;
					} else {
						$state = 1;
					}
					break;
				case 'contains':
					if (strstr( $field, $val )) {
						$state = 0;
					} else {
						$state = 1;
					}
					break;
			}
		}
	}

	return ( $state ) ? true : false;
}


/**
 * Exit and redirect to home
 */
function tp_redirect_home() {
	exit( wp_redirect( home_url() ) );
}


/**
 * @param $url
 */
function tp_js_redirect( $url ) {
	if ( headers_sent() || empty( $url ) ) {
		//for blank redirects
		if ( '' == $url ) {
			$url = set_url_scheme( '//' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		}

		register_shutdown_function( function( $url ) {
			echo '<script data-cfasync="false" type="text/javascript">window.location = "' . $url . '"</script>';
		}, $url );

		if ( 1 < ob_get_level() ) {
			while ( ob_get_level() > 1 ) {
				ob_end_clean();
			}
		} ?>
		<script data-cfasync='false' type="text/javascript">
			window.location = '<?php echo $url; ?>';
		</script>
		<?php exit;
	} else {
		wp_redirect( $url );
	}
	exit;
}


/**
 * Get limit of words from sentence
 *
 * @param $str
 * @param int $wordCount
 *
 * @return string
 */
function tp_get_snippet( $str, $wordCount = 10 ) {
	if (str_word_count( $str ) > $wordCount) {
		$str = implode(
			'',
			array_slice(
				preg_split(
					'/([\s,\.;\?\!]+)/',
					$str,
					$wordCount * 2 + 1,
					PREG_SPLIT_DELIM_CAPTURE
				),
				0,
				$wordCount * 2 - 1
			)
		);
	}

	return $str;
}


/**
 * Get youtube video ID from url
 *
 * @param $url
 *
 * @return bool
 */
function tp_youtube_id_from_url( $url ) {
	$pattern =
		'%^# Match any youtube URL
		(?:https?://)?  # Optional scheme. Either http or https
		(?:www\.)?      # Optional www subdomain
		(?:             # Group host alternatives
		  youtu\.be/    # Either youtu.be,
		| youtube\.com  # or youtube.com
		  (?:           # Group path alternatives
			/embed/     # Either /embed/
		  | /v/         # or /v/
		  | /watch\?v=  # or /watch\?v=
		  )             # End path alternatives.
		)               # End host alternatives.
		([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
		$%x';
	$result = preg_match( $pattern, $url, $matches );
	if (false !== $result) {
		return $matches[1];
	}

	return false;
}


/**
 * Find closest number in an array
 *
 * @param $array
 * @param $number
 *
 * @return mixed
 */
function tp_closest_num( $array, $number ) {
	sort( $array );
	foreach ( $array as $a ) {
		if ( $a >= $number ) return $a;
	}

	return end( $array );
}


/**
 * Get server protocol
 *
 * @return  string
 */
function tp_get_domain_protocol() {

	if (is_ssl()) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	return $protocol;
}


/**
 * Set SSL to media URI
 *
 * @param  string $url
 *
 * @return string
 */
function tp_secure_media_uri( $url ) {

	if (is_ssl()) {
		$url = str_replace( 'http:', 'https:', $url );
	}

	return $url;
}


/**
 * Force strings to UTF-8 encoded
 *
 * @param  mixed $value
 *
 * @return mixed
 */
function tp_force_utf8_string( $value ) {

	if (is_array( $value )) {
		$arr_value = array();
		foreach ($value as $key => $val) {
			$utf8_decoded_value = utf8_decode( $val );

			if (mb_check_encoding( $utf8_decoded_value, 'UTF-8' )) {
				array_push( $arr_value, $utf8_decoded_value );
			} else {
				array_push( $arr_value, $val );
			}

		}

		return $arr_value;
	} else {

		$utf8_decoded_value = utf8_decode( $value );

		if (mb_check_encoding( $utf8_decoded_value, 'UTF-8' )) {
			return $utf8_decoded_value;
		}
	}

	return $value;
}


/**
 * Filters the search query.
 *
 * @param  string $search
 *
 * @return string
 */
function tp_filter_search( $search ) {
	$search = trim( strip_tags( $search ) );
	$search = preg_replace( '/[^a-z \.\@\_\-]+/i', '', $search );

	return $search;
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.3.68
 * @return mixed string $host if detected, false otherwise
 */
function tp_get_host() {
	$host = false;

	if (defined( 'WPE_APIKEY' )) {
		$host = 'WP Engine';
	} else if (defined( 'PAGELYBIN' )) {
		$host = 'Pagely';
	} else if (DB_HOST == 'localhost:/tmp/mysql5.sock') {
		$host = 'ICDSoft';
	} else if (DB_HOST == 'mysqlv5') {
		$host = 'NetworkSolutions';
	} else if (strpos( DB_HOST, 'ipagemysql.com' ) !== false) {
		$host = 'iPage';
	} else if (strpos( DB_HOST, 'ipowermysql.com' ) !== false) {
		$host = 'IPower';
	} else if (strpos( DB_HOST, '.gridserver.com' ) !== false) {
		$host = 'MediaTemple Grid';
	} else if (strpos( DB_HOST, '.pair.com' ) !== false) {
		$host = 'pair Networks';
	} else if (strpos( DB_HOST, '.stabletransit.com' ) !== false) {
		$host = 'Rackspace Cloud';
	} else if (strpos( DB_HOST, '.sysfix.eu' ) !== false) {
		$host = 'SysFix.eu Power Hosting';
	} else if (strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}


/**
 * Let To Num
 *
 * Does Size Conversions
 *
 * @since 1.3.68
 * @author Chris Christoff
 *
 * @param string $v
 *
 * @return int|string
 */
function tp_let_to_num( $v ) {
	$l = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch (strtoupper( $l )) {
		case 'P': // fall-through
		case 'T': // fall-through
		case 'G': // fall-through
		case 'M': // fall-through
		case 'K': // fall-through
			$ret *= 1024;
			break;
		default:
			break;
	}

	return $ret;
}


/**
 * Maybe set empty time limit
 */
function tp_maybe_unset_time_limit() {
	@set_time_limit( 0 );
}