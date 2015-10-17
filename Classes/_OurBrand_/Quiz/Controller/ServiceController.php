<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Exception\AccessDeniedException;

class ServiceController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var array
	 */
	protected $supportedMediaTypes = array('application/json');

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'TYPO3\Flow\Mvc\View\JsonView';

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\Inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var bool
	 */
	protected $requireSecure = TRUE;


	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;


	/**
	 * @var \_OurBrand_\Quiz\Service\ExerciseService
	 * @Flow\Inject
	 */
	protected $exerciseService;

	/**
	 * @inheritdoc
	 */
	public function initializeAction() {
		if($this->environment->getContext() == 'Development') {
			$this->requireSecure = FALSE;
		}
	}


	/**
	 * @param string $authentication A hash of the shared secret and the params.
	 * @param string $userIdentifier
	 * @param string $subject Which subject to filter by.
	 *
	 * @throws \Exception
	 */
	public function quizListAction($authentication, $userIdentifier, $subject = '') {

		if(!$this->verifyAuthentication($authentication, (string)($userIdentifier.$subject))) {
			throw new AccessDeniedException('Access denied');
		}

		if($this->requireSecure && !$this->request->getHttpRequest()->isSecure()) {
			throw new \Exception('Connection must be secure', 40);
		}

		$user = $this->userRepository->findByIdentifier($userIdentifier);

		$userQuizzes = $this->quizRepository->findAllByUser($user);
		$publicQuizzes = $this->quizRepository->findAllPublic();

		$out = array();
		foreach($userQuizzes as $quiz) {
			$outQuiz = $this->makeExportArrayFromQuiz($quiz);
			$out[$this->persistenceManager->getIdentifierByObject($quiz)] = $outQuiz;
		}

		$out2 = array();
		foreach($publicQuizzes as $quiz) {

			// No duplicates.
			if(isset($out[$this->persistenceManager->getIdentifierByObject($quiz)])) {
				continue;
			}
			$outQuiz = $this->makeExportArrayFromQuiz($quiz);
			$out2[$this->persistenceManager->getIdentifierByObject($quiz)] = $outQuiz;
		}

		$this->view->assign('value', array('userQuizzes' => $out, 'publicQuizzes' => $out2));
	}

	/**
	 * List exercises in quiz.
	 * @param string $authentication
	 * @param string $quizIdentifier
	 *
	 */
	public function quizExercisesListAction($authentication, $quizIdentifier) {
		if(!$this->verifyAuthentication($authentication, (string)($quizIdentifier))) {
			throw new AccessDeniedException('Access denied');
		}
		$quiz = $this->quizRepository->findByIdentifier($quizIdentifier);
		if($quiz == null) {
			throw new \Exception('Quiz not found');
		}
		$out = array();
		foreach($quiz->getExercises() as $exercise) {
			$out[$this->persistenceManager->getIdentifierByObject($exercise)] = $this->makeExportArrayFromExercise($exercise);
		}

		$this->view->assign('value', $out);

	}


	/**
	 * @param string $authentication
	 * @param string $quizIdentifier
	 */
	public function quizDeleteAction($authentication, $quizIdentifier) {
		if(!$this->verifyAuthentication($authentication, (string)($quizIdentifier))) {
			throw new AccessDeniedException('Access denied');
		}
		$quiz = $this->quizRepository->findByIdentifier($quizIdentifier);
		if($quiz) {
			$this->quizRepository->remove($quiz);
			$this->persistenceManager->persistAll();
		}
		$this->view->assign('value', array('ok'));
	}


	/**
	 * Verifies the authentication token.
	 *
	 * @param string $authenticationHash
	 * @param string $parameters
	 *
	 */
	private function verifyAuthentication($authenticationHash, $parameters) {
		if(md5($this->settings['webserviceKey'].$parameters) == $authenticationHash
			|| $this->environment->getContext() == 'Development') {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 * @return array
	 */
	private function makeExportArrayFromQuiz($quiz) {

		$thumbnailMaxWidth = 200;
		$thumbnailMaxHeight = 200;
		$previewImage =  ( $quiz->getBannerImage() ? $quiz->getBannerImage()->getThumbNail($thumbnailMaxWidth, $thumbnailMaxHeight)->getOriginalResource() : null );

		$outQuiz = array(
			'__identifier' => $this->persistenceManager->getIdentifierByObject($quiz),
			'title' => $quiz->getTitle(),
			'bannerImage' => $this->resourcePublisher->getPersistentResourceWebUri($previewImage),
			'subject' => $quiz->getSubject(),
			'numberOfExercises' => $quiz->getExercises()->count(),
		);

		return $outQuiz;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return array
	 */
	private function makeExportArrayFromExercise($exercise) {
		$outExercise = array(
			'__identifier' => $this->persistenceManager->getIdentifierByObject($exercise),
			'title' => $exercise->getTitle(),
			'type' => $this->getTypeForExercise($exercise),
			'duration' => $exercise->getDuration(),
		);

		return $outExercise;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function getTypeForExercise($exercise) {
		$exerciseClass = get_class($exercise);
		$parts = explode('\\', $exerciseClass);
		$exerciseClass = array_pop($parts);
		foreach($this->settings['exercises'] as $exercise) {
			if($exercise['class'] == $exerciseClass) {
				return $exercise['name'];
			}
		}
		return '';
	}

}

