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
 * An authentication token used for sso credentials coming from typo3.org
 *
 * @scope prototype
 */
class Typo3OrgSsoToken implements \TYPO3\FLOW3\Security\Authentication\TokenInterface {

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected $authenticationProviderName;

	/**
	 * Current authentication status of this token
	 * @var integer
	 */
	protected $authenticationStatus = self::NO_CREDENTIALS_GIVEN;

	/**
	 * The username/password credentials
	 * @var array
	 * @transient
	 */
	protected $credentials = array('username' => '', 'signature' => '');

	/**
	 * @var \TYPO3\FLOW3\Security\Account
	 */
	protected $account;

	/**
	 * @var \TYPO3\FLOW3\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @var array
	 */
	protected $requestPatterns = NULL;

	/**
	 * The authentication entry point
	 * @var \TYPO3\FLOW3\Security\Authentication\EntryPointInterface
	 */
	protected $entryPoint = NULL;

	/**
	 * @param \TYPO3\FLOW3\Utility\Environment $environment The current environment object
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectEnvironment(\TYPO3\FLOW3\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @param \TYPO3\FLOW3\Security\AccountRepository $accountRepository
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectAccountRepository(\TYPO3\FLOW3\Security\AccountRepository $accountRepository) {
		$this->accountRepository = $accountRepository;
	}

	/**
	 * Returns the name of the authentication provider responsible for this token
	 *
	 * @return string The authentication provider name
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAuthenticationProviderName() {
		return $this->authenticationProviderName;
	}

	/**
	 * Sets the name of the authentication provider responsible for this token
	 *
	 * @param string $authenticationProviderName The authentication provider name
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setAuthenticationProviderName($authenticationProviderName) {
		$this->authenticationProviderName = $authenticationProviderName;
	}

	/**
	 * Returns TRUE if this token is currently authenticated
	 *
	 * @return boolean TRUE if this this token is currently authenticated
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isAuthenticated() {
		return ($this->authenticationStatus === self::AUTHENTICATION_SUCCESSFUL);
	}

	/**
	 * Sets the authentication entry point
	 *
	 * @param \TYPO3\FLOW3\Security\Authentication\EntryPointInterface $entryPoint The authentication entry point
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setAuthenticationEntryPoint(\TYPO3\FLOW3\Security\Authentication\EntryPointInterface $entryPoint) {
		$this->entryPoint = $entryPoint;
	}

	/**
	 * Returns the configured authentication entry point, NULL if none is available
	 *
	 * @return \TYPO3\FLOW3\Security\Authentication\EntryPointInterface The configured authentication entry point, NULL if none is available
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAuthenticationEntryPoint() {
		return $this->entryPoint;
	}

	/**
	 * Returns TRUE if \TYPO3\FLOW3\Security\RequestPattern were set
	 *
	 * @return boolean True if a \TYPO3\FLOW3\Security\RequestPatternInterface was set
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasRequestPatterns() {
		if ($this->requestPatterns != NULL) return TRUE;
		return FALSE;
	}

	/**
	 * Sets request patterns
	 *
	 * @param array $requestPatterns Array of \TYPO3\FLOW3\Security\RequestPattern to be set
	 * @return void
	 * @see hasRequestPattern()
	 */
	public function setRequestPatterns(array $requestPatterns) {
		$this->requestPatterns = $requestPatterns;
	}

	/**
	 * Returns an array of set \TYPO3\FLOW3\Security\RequestPatternInterface, NULL if none was set
	 *
	 * @return array Array of set request patterns
	 * @see hasRequestPattern()
	 */
	public function getRequestPatterns() {
		return $this->requestPatterns;
	}

	/**
	 * Updates the username and password credentials from the POST vars, if the POST parameters
	 * are available. Sets the authentication status to REAUTHENTICATION_NEEDED, if credentials have been sent.
	 *
	 * @param \TYPO3\FLOW3\MVC\RequestInterface $request The current request instance
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function updateCredentials(\TYPO3\FLOW3\MVC\RequestInterface $request) {
		$getArguments = $this->environment->getRawGetArguments();

		if (!empty($getArguments['user'])
			&& !empty($getArguments['signature'])
			&& !empty($getArguments['expires'])
			&& !empty($getArguments['version'])
			&& !empty($getArguments['tpa_id'])
			&& !empty($getArguments['action'])
			&& !empty($getArguments['flags'])
			&& !empty($getArguments['userdata'])) {

			$this->credentials['username'] = $getArguments['user'];
			$this->credentials['signature'] = \TYPO3\FLOW3\Utility\TypeHandling::hex2bin($getArguments['signature']);
			$this->credentials['expires'] = $getArguments['expires'];
			$this->credentials['version'] = $getArguments['version'];
			$this->credentials['tpaId'] = $getArguments['tpa_id'];
			$this->credentials['action'] = $getArguments['action'];
			$this->credentials['flags'] = $getArguments['flags'];
			$this->credentials['userdata'] = $getArguments['userdata'];

			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		}
	}

	/**
	 * Returns the credentials (username and password) of this token.
	 *
	 * @return object $credentials The needed credentials to authenticate this token
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getCredentials() {
		return $this->credentials;
	}

	/**
	 * Returns the account if one is authenticated, NULL otherwise.
	 *
	 * @return \TYPO3\FLOW3\Security\Account An account object
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * Set the (authenticated) account
	 *
	 * @param \TYPO3\FLOW3\Security\Account $account An account object
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setAccount(\TYPO3\FLOW3\Security\Account $account = NULL) {
		$this->account = $account;
	}

	/**
	 * Returns the currently valid roles.
	 *
	 * @return array Array of TYPO3\FLOW3\Security\Authentication\Role objects
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getRoles() {
		$account = $this->getAccount();
		return ($account !== NULL && $this->isAuthenticated()) ? $account->getRoles() : array();
	}

	/**
	 * Sets the authentication status. Usually called by the responsible \TYPO3\FLOW3\Security\Authentication\AuthenticationManagerInterface
	 *
	 * @param integer $authenticationStatus One of NO_CREDENTIALS_GIVEN, WRONG_CREDENTIALS, AUTHENTICATION_SUCCESSFUL, AUTHENTICATION_NEEDED
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @throws TYPO3\FLOW3\Security\Exception\InvalidAuthenticationStatusException
	 */
	public function setAuthenticationStatus($authenticationStatus) {
		if (!in_array($authenticationStatus, array(self::NO_CREDENTIALS_GIVEN, self::WRONG_CREDENTIALS, self::AUTHENTICATION_SUCCESSFUL, self::AUTHENTICATION_NEEDED))) {
			throw new \TYPO3\FLOW3\Security\Exception\InvalidAuthenticationStatusException('Invalid authentication status.', 1237224453);
		}
		$this->authenticationStatus = $authenticationStatus;
	}

	/**
	 * Returns the current authentication status
	 *
	 * @return integer One of NO_CREDENTIALS_GIVEN, WRONG_CREDENTIALS, AUTHENTICATION_SUCCESSFUL, AUTHENTICATION_NEEDED
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAuthenticationStatus() {
		return $this->authenticationStatus;
	}

	/**
	 * Returns a string representation of the token for logging purposes.
	 *
	 * @return string The username credential
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function  __toString() {
		return 'Username: "' . $this->credentials['username'] . '"';
	}
}

?>