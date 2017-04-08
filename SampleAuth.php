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

		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '2.3.0-dev',
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

	function auth_user_flags( $p_event_name, $p_args ) {
		# Don't access DB if db_is_connected() is false.

		$t_username = $p_args['username'];

		$t_user_id = $p_args['user_id'];

		# If user is unknown, don't handle authentication for it, since this plugin doesn't do
		# auto-provisioning
		if( !$t_user_id ) {
			return null;
		}

		# If anonymous user, don't handle it.
		if( user_is_anonymous( $t_user_id ) ) {
			return null;
		}

		$t_access_level = user_get_access_level( $t_user_id, ALL_PROJECTS );

		# Have administrators use default login flow
		if( $t_access_level >= ADMINISTRATOR ) {
			return null;
		}

		# for everybody else use the custom authentication
		$t_flags = new AuthFlags();

		# Passwords managed externally for all users
		$t_flags->setCanUseStandardLogin( false );
		$t_flags->setPasswordManagedExternallyMessage( 'Passwords are no more, you cannot change them!' );

		# No one can use standard auth mechanism

		# Override Login page and Logout Redirect
		$t_flags->setCredentialsPage( helper_url_combine( plugin_page( 'login', /* redirect */ true ), 'username=' . $t_username ) );
		$t_flags->setLogoutRedirectPage( plugin_page( 'logout', /* redirect */ true ) );

		# No long term session for identity provider to be able to kick users out.
		$t_flags->setPermSessionEnabled( false );

		# Enable re-authentication and use more aggressive timeout.
		$t_flags->setReauthenticationEnabled( true );
		$t_flags->setReauthenticationLifetime( 10 );

		return $t_flags;
	}
}
