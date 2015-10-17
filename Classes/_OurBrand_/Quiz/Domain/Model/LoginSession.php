<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("session")
 */
class LoginSession {


	/**
	 * @var string
	 */
	protected $redirectUriAfterLogout;


	/**
	 * @var \_OurBrand_\My\Domain\Model\User
	 *
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string
	 */
	protected $redirectUriAfterLogin;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @return \_OurBrand_\My\Domain\Model\User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn(){
		return (is_object($this->user) && $this->user->getIdentifier() != '');
	}



	/**
	 * @param string $redirectUriAfterLogin
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function setRedirectUriAfterLogin($redirectUriAfterLogin) {
		$this->redirectUriAfterLogin = $redirectUriAfterLogin;
	}

	/**
	 * @return string
	 */
	public function getRedirectUriAfterLogin() {
		return $this->redirectUriAfterLogin;
	}

	/**
	 * @param string $Identifier
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function setIdentifier($Identifier) {
		$this->Identifier = $Identifier;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->Identifier;
	}

	/**
	 * @param string $redirectUriAfterLogout
	 */
	public function setRedirectUriAfterLogout($redirectUriAfterLogout) {
		$this->redirectUriAfterLogout = $redirectUriAfterLogout;
	}

	/**
	 * @return string
	 */
	public function getRedirectUriAfterLogout() {
		return $this->redirectUriAfterLogout;
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 */
	public function setData($key, $data){
		$this->data[$key] = $data;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getData($key){
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Invalidates the session object by removing
	 * properties.
	 *
	 * @return void
	 */
	public function invalidate(){
		$this->data = array();
		$this->redirectUriAfterLogin = '';
		$this->user = null;
	}


}
?>