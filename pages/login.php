<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'user_api.php' );

$f_username = gpc_get_string( 'username', '' );
$f_password = gpc_get_string( 'password', '' );
$f_reauthenticate = gpc_get_bool( 'reauthenticate', false );
$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );

$t_return = string_url( string_sanitize_url( $f_return ) );

$f_username = auth_prepare_username( $f_username );
$f_password = auth_prepare_password( $f_password );

/*
*
* Log in the user with the custom class
*
* class should return username/false upon successful/failed login
*
*/

plugin_require_api( 'core/CustomAuthPlugin.php' );
$cap = new CustomAuthPlugin();
if ( ( $f_username = $cap->login($f_username,$f_password) ) ) {
    /*
    *
    * All set, good to go
    *
    * if you want to assign the user to project(s) based on a criteria
    * than this is the right place. Don't forget to check if it's already assigned
    *
    */
}

$t_user_id = is_blank( $f_username ) ? false : user_get_id_by_name( $f_username );

if( $t_user_id == false ) {
	$t_query_args = array(
		'error' => 1,
		'username' => $f_username,
	);

	if( !is_blank( 'return' ) ) {
		$t_query_args['return'] = $t_return;
	}

	if( $f_reauthenticate ) {
		$t_query_args['reauthenticate'] = 1;
	}

	$t_query_text = http_build_query( $t_query_args, '', '&' );
	// we will create a loop this way - this will redirect us again to this login page...
	//$t_uri = auth_login_page( $t_query_text );
	// no "stack like" auth mechs, forcing default page on error
	$t_uri = helper_url_combine( AUTH_PAGE_USERNAME, $t_query_args);

	print_header_redirect( $t_uri );
}

# Let user into MantisBT
auth_login_user( $t_user_id );

# Redirect to original page user wanted to access before authentication
if( !is_blank( $t_return ) ) {
	print_header_redirect( 'login_cookie_test.php?return=' . $t_return );
}

# If no return page, redirect to default page
print_header_redirect( config_get( 'default_home_page' ) );
