<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'user_api.php' );

$f_username = gpc_get( 'username' );
$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );
$t_return = string_url( string_sanitize_url( $f_return ) );

# TODO: use custom authentication method here.

$t_user_id = is_blank( $f_username ) ? false : user_get_id_by_name( $f_username );

if( $t_user_id == false ) {
	if( is_blank( $f_return ) ) {
		$t_uri = auth_login_page();
	} else {
		$t_uri = auth_login_page( 'return=' . $t_return );
	}

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
