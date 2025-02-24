<?php
namespace TYPO3\FLOW3\Security\Authentication\Token;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * An authentication token used for simple username and password authentication via HTTP Basic Auth.
 *
 * @scope prototype
 */
class UsernamePasswordHttpBasic extends \TYPO3\FLOW3\Security\Authentication\Token\UsernamePassword {

	/**
	 * Updates the username and password credentials from the HTTP authorization header.
	 * Sets the authentication status to AUTHENTICATION_NEEDED, if the header has been
	 * sent, to NO_CREDENTIALS_GIVEN if no authorization header was there.
	 *
	 * @param \TYPO3\FLOW3\MVC\RequestInterface $request The current request instance
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function updateCredentials(\TYPO3\FLOW3\MVC\RequestInterface $request) {
		$requestHeaders = $this->environment->getRequestHeaders();

		if (isset($requestHeaders['User']) && isset($requestHeaders['Pw'])) {
			$this->credentials['username'] = $requestHeaders['User'];
			$this->credentials['password'] = $requestHeaders['Pw'];
			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		} else {
			$this->credentials = array('username' => NULL, 'password' => NULL);
			$this->authenticationStatus = \TYPO3\FLOW3\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN;
		}
	}
}

?>