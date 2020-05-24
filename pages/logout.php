<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( 'core.php' );
require_api( 'authentication_api.php' );

# User is already logged out from Mantis
# TODO by the plugin: logout from external identity provider if necessary or redirect to custom page

/**
*
* plugin_require_api( 'core/CustomAuthPlugin.php' );
* $cap = new CustomAuthPlugin();
* $cap->logout();
*
*/

# default redirect to Mantis login page
print_header_redirect( auth_login_page(), true, false );
