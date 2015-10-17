<?php
namespace _OurBrand_\Quiz\Tests\Functional\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Client\Browser;
use TYPO3\Flow\Mvc\Routing\Route;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Annotations as Flow;

/**
 * Functional tests for the ActionController
 */
class QuizControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	protected $testableSecurityEnabled = TRUE;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 */
	protected $quizRepository;


	/**
	 * @Flow\Inject(lazy = false)
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \_OurBrand_\Quiz\Controller\QuizController
	 */
	protected $quizController;


	/**
	 * @var string
	 */
	protected $token;

	/**
	 * Additional setup: Repositories, security
	 */
	public function setUp() {

		parent::setUp();

		$this->userRepository = $this->objectManager->get('_OurBrand_\Quiz\Domain\Repository\UserRepository');
		$this->quizRepository = $this->objectManager->get('_OurBrand_\Quiz\Domain\Repository\QuizRepository');
//		$this->quizController = $this->objectManager->get('_OurBrand_\Quiz\Controller\QuizController');

	}



	/**
	 * @test
	 */
	public function placeholderTest() {
		$this->assertEquals(true, true);
	}

	/**
	 *
	 * test
	 */
	public function canLoginWithTokenAndUserIsCreated() {

		$this->login();

		//$this->assertContains('<!DOCTYPE html>', $response->getContent());
		//$this->assertEquals('200 OK', $response->getStatus());

		$user = $this->userRepository->findByIdentifier($this->token);

	}

	/**
	 *
	 * test
	 */
	public function instructorCanSeeIndex(){
		$this->login();
		$response = $this->browser->request('http://localhost/_OurBrand_.quiz/quiz/index');

		//$this->assertContains($this->token, $response->getContent());

	}


	/**
	 * test
	 */
	public function instructorCanCreateQuiz(){
		$this->login('instructor');
		$response = $this->browser->request('http://localhost/_OurBrand_.quiz/quiz/create', 'POST');
		//$this->assertContains($this->token, strtolower($response->getContent()));
		//$this->assertEquals(1, $this->quizRepository->findAll()->count());
	}

	/**
	 *
	 * test
	 */
	public function studentCanNotCreateQuiz(){
		$this->login('student');
		$response = $this->browser->request('http://localhost/_OurBrand_.quiz/quiz/create', 'POST');
		//$this->assertContains('access denied', strtolower($response->getContent()));


	}


	/**
	 * @param string $role
	 */
	protected function login($role = 'instructor'){
		$this->browser->request('http://localhost/_OurBrand_.quiz/authentication/logout');
		$this->token = md5(rand());
		$res = $this->browser->request('http://localhost/_OurBrand_.quiz/authentication/login', 'POST', array('token' => $this->token, 'role' => $role));
		$res->getContent();
	}


}
?>
