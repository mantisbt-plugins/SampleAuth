<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

/**
 * Login page POSTs results to login.php
 * Check to see if the user is already logged in
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_css( 'login.css' );

$f_error                 = gpc_get_bool( 'error' );
$f_cookie_error          = gpc_get_bool( 'cookie_error' );
$f_return                = gpc_get_string( 'return', '' );
$f_username              = gpc_get_string( 'username', '' );
$f_reauthenticate        = gpc_get_bool( 'reauthenticate', false );

$t_return = string_sanitize_url( $f_return );

# Set username to blank if invalid to prevent possible XSS exploits
if( !user_is_name_valid( $f_username ) ) {
	$f_username = '';
}

$t_username_label = lang_get( 'username' );
$t_form_title = $f_reauthenticate ? lang_get( 'reauthenticate_title' ) : lang_get( 'login_title' );
$t_form_title .= ' - SampleAuth Testing Only';

# If user is already authenticated and not anonymous
if( auth_is_user_authenticated() && !current_user_is_anonymous() && !$f_reauthenticate) {
	# If return URL is specified redirect to it; otherwise use default page
	if( !is_blank( $t_return ) ) {
		print_header_redirect( $t_return, false, false, true );
	} else {
		print_header_redirect( config_get( 'default_home_page' ) );
	}
}

# Determine whether the username or password field should receive automatic focus.
$t_username_field_autofocus = 'autofocus';
$t_password_field_autofocus = '';
if( $f_username ) {
	$t_username_field_autofocus = '';
	$t_password_field_autofocus = 'autofocus';
}

# Login page shouldn't be indexed by search engines
html_robots_noindex();

layout_login_page_begin();

?>

	<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
		<div class="login-container">
			<div class="space-12 hidden-480"></div>
			<a href="<?php echo config_get( 'logo_url' ) ?>">
				<h1 class="center white">
					<img src="<?php echo helper_mantis_url( config_get( 'logo_image' ) ); ?>">
				</h1>
			</a>
			<div class="space-24 hidden-480"></div>
			<?php
			if( $f_error || $f_cookie_error || $f_reauthenticate ) {
				echo '<div class="alert alert-danger">';

				if( $f_reauthenticate ) {
					echo '<p>' . lang_get( 'reauthenticate_message' ) . '</p>';
				}

				# Only echo error message if error variable is set
				if( $f_error ) {
					echo '<p>' . lang_get( 'login_error' ) . '</p>';
				}

				if( $f_cookie_error ) {
					echo '<p>' . lang_get( 'login_cookies_disabled' ) . '</p>';
				}

				echo '</div>';
			}

			$t_warnings = array();
			$t_upgrade_required = false;
			if( config_get_global( 'admin_checks' ) == ON && file_exists( dirname( __FILE__ ) .'/admin' ) ) {
				# since admin directory and db_upgrade lists are available check for missing db upgrades
				# if db version is 0, we do not have a valid database.
				$t_db_version = config_get( 'database_version', 0 );
				if( $t_db_version == 0 ) {
					$t_warnings[] = lang_get( 'error_database_no_schema_version' );
				}

				# Check for db upgrade for versions > 1.0.0 using new installer and schema
				require_once( 'admin' . DIRECTORY_SEPARATOR . 'schema.php' );
				$t_upgrades_reqd = count( $g_upgrade ) - 1;

				if( ( 0 < $t_db_version ) &&
					( $t_db_version != $t_upgrades_reqd ) ) {

					if( $t_db_version < $t_upgrades_reqd ) {
						$t_warnings[] = lang_get( 'error_database_version_out_of_date_2' );
						$t_upgrade_required = true;
					} else {
						$t_warnings[] = lang_get( 'error_code_version_out_of_date' );
					}
				}
			}
			?>

			<div class="position-relative">
				<div class="signup-box visible widget-box no-border" id="login-box">
					<div class="widget-body">
						<div class="widget-main">
							<h4 class="header lighter bigger">
								<i class="ace-icon fa fa-sign-in"></i>
								<?php echo $t_form_title ?>
							</h4>
							<div class="space-10"></div>
							<!-- Login Form BEGIN -->
							<form id="login-form" method="post" action="plugin.php">
								<fieldset>
									<input type="hidden" name="page" value="SampleAuth/login" />

									<?php
									if( !is_blank( $t_return ) ) {
										echo '<input type="hidden" name="return" value="', string_html_specialchars( $t_return ), '" />';
									}

									if( $t_upgrade_required ) {
										echo '<input type="hidden" name="install" value="true" />';
									}

									# CSRF protection not required here - form does not result in modifications
									?>

									<label for="username" class="block clearfix">
				<span class="block input-icon input-icon-right">
					<input id="username" name="username" type="text" placeholder="<?php echo $t_username_label ?>"
						   size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo string_attribute( $f_username ); ?>"
						   class="form-control <?php echo $t_username_field_autofocus ?>">
					<i class="ace-icon fa fa-user"></i>
				</span>
									</label>
									<?php if( $f_reauthenticate ) {
										echo '<input id="reauthenticate" type="hidden" name="reauthenticate" value="1" />';
									} ?>

									<div class="space-10"></div>

									<input type="submit" class="width-40 pull-right btn btn-success btn-inverse bigger-110" value="<?php echo lang_get( 'login_button' ) ?>" />
									<div class="clearfix"></div>
								</fieldset>
							</form>

							<!-- Login Form END -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
layout_login_page_end();
