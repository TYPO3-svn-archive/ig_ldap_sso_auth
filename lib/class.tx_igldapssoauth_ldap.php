<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Michael Gagnon <mgagnon@infoglobe.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class tx_igldapssoauth_ldap for the 'ig_ldap_sso_auth' extension.
 *
 * @author	Michael Gagnon <mgagnon@infoglobe.ca>
 * @package	TYPO3
 * @subpackage	ig_ldap_sso_auth
 *
 * $Id$
 */
class tx_igldapssoauth_ldap {

	function connect($config = array()) {

		// Connect to ldap server.
		if (!tx_igldapssoauth_utility_Ldap::connect($config['host'], $config['port'], $config['protocol'], $config['charset'], $config['server'])) {
			return false;
		}

		// Bind to ldap server.
		if (!tx_igldapssoauth_utility_Ldap::bind($config['binddn'], $config['password'])) {
			tx_igldapssoauth_ldap::disconnect();
			return false;
		}

		return true;

	}

	function valid_user($username = null, $password = null, $basedn = null, $filter = null) {

		// If user found on ldap server.
		if (tx_igldapssoauth_utility_Ldap::search($basedn, str_replace('{USERNAME}', $username, $filter), array('dn'))) {

			// Validate with password.
			if ($password) {

				// Bind DN of user with password.
				if (tx_igldapssoauth_utility_Ldap::bind(tx_igldapssoauth_utility_Ldap::get_dn(), $password)) {

					$dn = tx_igldapssoauth_utility_Ldap::get_dn();

					// Restore last LDAP binding
					$config = tx_igldapssoauth_config::get_values('ldap');
					tx_igldapssoauth_utility_Ldap::bind($config['binddn'], $config['password']);

					return $dn;
				}
				else {
					return TRUE;
				}

				// If enable, SSO authentication without password.
			} elseif (!$password && tx_igldapssoauth_config::is_enable('CASAuthentication')) {

				return tx_igldapssoauth_utility_Ldap::get_dn();

			} else {

				// User invalid. Authentication failed.
				return FALSE;

			}

		}

		return FALSE;

	}

	function search($basedn = null, $filter = null, $attributes = array(), $first_entry = false) {

		$result = array();

		if (tx_igldapssoauth_utility_Ldap::search($basedn, $filter, $attributes)) {

			if ($first_entry) {

				$result = tx_igldapssoauth_utility_Ldap::get_first_entry();
				$result['dn'] = tx_igldapssoauth_utility_Ldap::get_dn();
				unset($result['count']);

			} else {

				$result = tx_igldapssoauth_utility_Ldap::get_entries();

			}

		}

		return $result;

	}

	function get_status() {

		return tx_igldapssoauth_utility_Ldap::get_status();

	}

	function disconnect() {

		tx_igldapssoauth_utility_Ldap::disconnect();

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ig_ldap_sso_auth/lib/class.tx_igldapssoauth_ldap.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ig_ldap_sso_auth/lib/class.tx_igldapssoauth_ldap.php']);
}

?>