<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

/**
 * Sample Auth plugin
 */
class SampleAuthPlugin extends AuthPlugin  {
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
			'EVENT_AUTH_FLAGS' => 'auth_flags',
		);

		return $t_hooks;
	}

	function auth_flags( $p_event_name, AuthFlags $p_flags ) {
		# Don't access DB if db_is_connected() is false.
		# See list of flags in core/authentication_api.php auth_flags() function.

		# Passwords managed externally for all users
		$p_flags->setSetPasswordThreshold( NOBODY );
		$p_flags->setPasswordManagedExternallyMessage( 'Passwords are no more, you cannot change them!' );

		# No one can use standard auth mechanism
		$p_flags->setUserStandardLoginThreshold( NOBODY );

		# Override Login page and Logout Redirect
		$p_flags->setLoginPage( plugin_page( 'login_page', /* redirect */ true ) );
		$p_flags->setLogoutRedirectPage( plugin_page( 'logout', /* redirect */ true ) );

		# No long term session for identity provider to be able to kick users out.
		$p_flags->setPermSessionEnabled( false );

		# Disable anonymous access
		$p_flags->setAnonymousEnabled( false );

		# Set default access level for new signups, e.g. users rather than team members.
		$p_flags->setSignupAccessLevel( REPORTER );

		# Enable re-authentication and use more aggressive timeout.
		$p_flags->setReauthenticationEnabled( true );
		$p_flags->setReauthenticationLifetime( 10 );

		return $p_flags;
	}
}
