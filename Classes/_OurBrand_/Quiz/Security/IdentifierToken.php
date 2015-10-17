<?php
namespace _OurBrand_\Quiz\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * An authentication token used for simple password authentication.
 */
class IdentifierToken extends \TYPO3\Flow\Security\Authentication\Token\AbstractToken {

	/**
	 * The identifier credentials
	 * @var array
	 * @Flow\Transient
	 */
	protected $credentials = array('username' => '', 'password' => '');

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * Updates the identifier credential from the GET/POST vars, if the GET/POST parameters
	 * are available. Sets the authentication status to AUTHENTICATION_NEEDED, if credentials have been sent.
	 *
	 * Note: You need to send the password in this parameter:
	 *       __authentication[_OurBrand_][Quiz][Security][IdentifierToken][identifier]
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $actionRequest The current action request
	 * @return void
	 */
	public function updateCredentials(\TYPO3\Flow\Mvc\ActionRequest $actionRequest) {

		$postArguments = $actionRequest->getInternalArguments();

		$username = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($postArguments, '__authentication._OurBrand_.Quiz.Security.IdentifierToken.username');
		$password = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($postArguments, '__authentication._OurBrand_.Quiz.Security.IdentifierToken.password');

		if (!empty($username) && !empty($password)) {

			$this->credentials['username'] = $username;
			$this->credentials['password'] = $password;
			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		}
	}

	/**
	 * Returns a string representation of the token for logging purposes.
	 *
	 * @return string
	 */
	public function  __toString() {
		return 'Identifier token';
	}

}
?>