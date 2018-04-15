<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

/**
 * Sample Auth plugin
 */
class SampleAuthPlugin extends MantisPlugin  {
	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = '';

		$this->version = '0.2';
		$this->requires = array(
			'MantisCore' => '2.14.0-dev',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'https://www.mantisbt.org';
	}

	/**
	 * plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			'EVENT_AUTH_USER_FLAGS' => 'auth_user_flags',
		);

		return $t_hooks;
	}

	function config() {
	    return array(
		    # set to 'true', if the plugin can do autoprovisioning - otherwise only "known" users will be able to log in
		    'autoprovision' => true,
		    # sets the access level configured by autoprov; defaults to system configures default access level
		    'default_access_level' => config_get( 'default_new_account_access_level' )
		);
	}

	function auth_user_flags( $p_event_name, $p_args ) {
		# Don't access DB if db_is_connected() is false.
		$t_username = $p_args['username'];

		$t_user_id = $p_args['user_id'];

		# If user is unknown and autoprovision is not set, than don't handle authentication for it
		if( !$t_user_id && ! plugin_config_get( 'autoprovision' ) ) {
			return null;
		}

		# If anonymous user, don't handle it.
		if( user_is_anonymous( $t_user_id ) ) {
			return null;
		}

		if( $t_user_id ) {
		    $t_access_level = user_get_access_level( $t_user_id, ALL_PROJECTS );

		    # Have administrators use default login flow
		    if( $t_access_level >= ADMINISTRATOR ) {
			return null;
		    }
		}

		/*
		*
		* add any filter parameters here
		*
		* e.g. if you want the plugin to handle usernames only which contain '@':
		*
		* if ( ! preg_match('/^.*@.*$/',$t_username) ) {
		* 	return null;
		* }
		*
		* or to use this custom authenticateion for everybody else:
		*
		*/

		$t_flags = new AuthFlags();

		# Passwords managed externally for all users
		$t_flags->setCanUseStandardLogin( false );
		$t_flags->setPasswordManagedExternallyMessage( 'Passwords are no more, you cannot change them!' );

		# No one can use standard auth mechanism

		# Override Credentials, Authenticator page and Logout Redirect - see 'pages' subdirectory
		/*
		*
		* custom Credentials Page for user. This is displayed after the user did input his username (and the username is known to Mantis or plugin is autoprov capable)
		*
		*/
		//$t_flags->setCredentialsPage( helper_url_combine( plugin_page( 'credentials', /* redirect */ true ), 'username=' . $t_username ) );
		/*
		*
		* custom Authenticator Page for user. This is called, when the user entered both username and password in the standard MantisBT login flow
		* username and password in $_POST
		*
		* Please NOTE: if you don't do any filtering - e.g. e-mail - than this will be the only Auth Plugin besides the built-in! Stacking is not (yes) supported
		*
		*/
		$t_flags->setAuthenticatorPage( helper_url_combine( plugin_page( 'login', /* redirect */ true ), ( !empty($t_username) ? 'username=' . $t_username : '' ) ) );
		/*
		*
		* custom Logout Page for user.
		*
		*/
		//$t_flags->setLogoutRedirectPage( plugin_page( 'logout', /* redirect */ true ) );

		# No long term session for identity provider to be able to kick users out.
		$t_flags->setPermSessionEnabled( false );

		# Enable re-authentication and use more aggressive timeout.
		$t_flags->setReauthenticationEnabled( true );
		$t_flags->setReauthenticationLifetime( 10 );

		return $t_flags;
	}
}
