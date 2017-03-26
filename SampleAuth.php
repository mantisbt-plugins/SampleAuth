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
			'EVENT_AUTH_FLAGS' => 'auth_flags',
		);

		return $t_hooks;
	}

	function auth_flags() {
		# Don't access DB if db_is_connected() is false.
		# See list of flags in core/authentication_api.php auth_flags() function.
		$t_flags = array(
			'access_level_set_password' => NOBODY,
			'password_managed_elsewhere_message' => 'No passwords are needed with SampleAuth',
			'password_change_not_allowed_message' => 'Passwords are no more, you cannot change them!',
			'access_level_can_use_standard_login' => NOBODY,
			'login_page' => plugin_page( 'login_page', /* redirect */ true ),
			'logout_redirect_page' => plugin_page( 'logout', /* redirect */ true ),
			'perm_session_enabled' => OFF,
			'anonymous_enabled' => OFF,
		);

		return $t_flags;
	}
}
