<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Class for dealing with custom authentication requests
 *
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * Class is written for demonstration purposes ONLY!!!
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @copyright Tamas Dajka 2018
 * @author Tamas Dajka <viper@vipernet.hu>
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 * @plugin SampleAuth
 */

/*
*
* User API is needed for user auto provision
*
*/
require_api( 'user_api.php' );
require_api( 'email_api.php' );

class CustomAuthPlugin {

	/**
	 * Constructor
	 */
	function __construct() {
	    # spaceholder
	}
	
	/**
	 * login
	 *
	 * @params: username, password
	 * @return:
	 * - false on login failure
	 * - username on login success
	 */
	function login( $username, $password ) {
	    /*
	    *
	    * Check access/auth in remote system
	    *
	    */
	    if ( ! $this->auth($username,$password) ) {
		return false;
	    }
	
	    /*
	    *
	    * Should we autoprovision the user?
	    *
	    */
	    if( plugin_config_get( 'autoprovision' ) ) {
		/*
		* Check if user exists, probably needs customization
		*/
		if( ! user_get_id_by_name($username) ) {
		    /*
		    * data needed for autorpovision:
		    * - username
		    * - password
		    *
		    * Optional params:
		    *
		    * - email
		    * - access_level (null)
		    * - protected (false)
		    * - enabled (true)
		    * - realname
		    * - admin_name
		    *
		    */      

		    $user_data = $this->get_user_data( $username );

		    /*
		    * create user, but with empty e-mail => prevent mantis from sending signup e-mail
		    * we set a strong random password
		    *
		    * To get this work, you either have to set $g_allow_blank_email = ON, or change the value here on-the-fly (don't forget to set it back)
		    *
		    */
		    $original_g_allow_blank_email = config_get( 'allow_blank_email' );
		    config_set_global( 'allow_blank_email', ON );
		    user_create( $username, auth_generate_random_password(24), '',  $user_data['access_level'], false, true, $user_data['realname'] );
		    config_set_global( 'allow_blank_email', $original_g_allow_blank_email );

		    /*
		    * Set user e-mail
		    */
		    if( !is_blank( $user_data['email'] ) && email_is_valid( $user_data['email'] ) && ( $t_user_id = user_get_id_by_name( $username ) ) ) {
			user_set_field( $t_user_id,'email', $user_data['email'] );
		    }
		}
	    }
	    
	    return $username;
	}
	
	/**
	 * logout
	 *
	 * @return: bool
	 */
	function logout() {
	    # spaceholder
	    return true;
	}
	
	/**
	 * collects user data from auth system
	 *
	 * @return: array()
	 */
	function get_user_data( $username = '' ) {
	    if ( empty($username) ) {
		return array();
	    }
	    
	    /*
	    * dummy data for now, access_level 25 is REPORTER -> see <ROOT>/core/constant_inc.php
	    */
	    return array( 'username' => $username, 'email' => 'john.doe@gmail.com', 'access_level' => config_get( 'default_new_account_access_level' ), 'realname' => 'John Doe' );
	}
	
	/**
	 * Auth validity check
	 *
	 * @params: username, password
	 * @return: bool
	 */
	function auth( $username, $password ) {
	    if ( empty( $username ) || empty( $password ) ) {
		return false;
	    }
	
	    /*
	    * We should check for external auth here
	    *
	    * dummy return for now
	    */
	
	    # comment this out for testing or write your own
	    return false;
	
	    if( $username == 'john.doe' && $password == 'Abc.123' ) {
		return true;
	    } else {
		return false;
	    }
	}
}
