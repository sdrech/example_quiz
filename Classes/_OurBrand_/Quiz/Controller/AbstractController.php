<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \_OurBrand_\Quiz\Domain\Model\Quiz;

abstract class AbstractController extends \TYPO3\Flow\Mvc\Controller\ActionController {
	/**
	 * @var bool
	 */
	protected $isJson = false;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\StudentQuizSession
	 */
	protected $studentQuizSession;

	/**
	 * A list of IANA media types which are supported by this controller
	 *
	 * @var array
	 * @see http://www.iana.org/assignments/media-types/index.html
	 */
	protected $supportedMediaTypes = array('text/html', 'application/json');

	/**
	 * A list of formats and object names of the views which should render them.
	 *
	 * Example:
	 *
	 * array('html' => 'MyCompany\MyApp\MyHtmlView', 'json' => 'MyCompany\...
	 *
	 * @var array
	 */
	protected $viewFormatToObjectNameMap = array('json' => 'TYPO3\Flow\Mvc\View\JsonView');

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\AnswerRepository
	 * @Flow\Inject
	 */
	protected $answerRepository;

	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseRepository
	 * @Flow\Inject
	 */
	protected $exerciseRepository;

	/**
	 * @var \_OurBrand_\Quiz\Service\ExerciseService
	 * @Flow\Inject
	 */
	protected $exerciseService;

	/**
	 * An object which represents the user login session.
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\LoginSession
	 * @Flow\Inject
	 */
	protected $loginSession;

	/**
	 * @var \_OurBrand_\Quiz\Service\StudentQuizSessionService
	 * @Flow\Inject
	 */
	protected $studentQuizSessionService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Translator
	 */
	protected $translator;


	/**
	 * @var \_OurBrand_\My\Domain\Model\User
	 */
	protected $currentUser;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\SubjectRepository
	 * @Flow\Inject
	 */
	protected $subjectRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseTypeRepository
	 * @Flow\Inject
	 */
	protected $exerciseTypeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Service\AccessHelper
	 * @Flow\Inject
	 */
	protected $accessHelper;

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * Injects the security context
	 *
	 * @param \TYPO3\Flow\Security\Context $securityContext The security context
	 * @return void
	 */
	public function injectSecurityContext(\TYPO3\Flow\Security\Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * Initializes all actions with commons logic.
	 */
	protected function initializeAction() {

		// Alternative to Flow's own json handling
		// FIXME
		if ($this->request->hasArgument('json')) {
			$this->isJson = true;
			$this->request->setFormat('json');
			$this->view = new \TYPO3\Flow\Mvc\View\JsonView();
			$this->view->setControllerContext($this->controllerContext);
		}


		if ($this->securityContext != null) {
			$account = $this->securityContext->getAccount();
			if ($account == null) {

				if ($this->isJson) {
					$this->throwStatus(403);
				} else {
					$this->authenticationManager->authenticate();
				}

			} else {
				/** @var \_OurBrand_\My\Domain\Model\User $user */
				$user = $account->getParty();
				if (!$user) {
					// Seriously bad!
					$this->throwStatus(403);
				}
				// Necessary for our easy role identifier functions!
				if ($user->getAccounts()->count() == 0) {
					$user->addAccount($account);
				}
				$this->currentUser = $user;

				// Set the quiz subjects that this user has access to.
				if (count($user->getQuizSubscriptionDataForQuizType(0)) == 0) {
					$this->accessHelper->setUserSubjectsAndTeamLevels($user);
				}

			}

		} else {
			if ($this->isJson) {
				$this->throwStatus(403);
			} else {
				$this->authenticationManager->authenticate();
			}
		}


		if ($this->loginSession->getData('studentQuizSession') != null) {
			$this->studentQuizSession = $this->loginSession->getData('studentQuizSession');
		}

		// Prevent browser caching of content. Everything is dynamic in this application.
		$this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
		$this->response->setHeader('Pragma', 'no-cache');
		$this->response->setHeader('Expires', '0');


	}

	/**
	 * Initializes the view with common variables.
	 *
	 * @param \TYPO3\Flow\Mvc\View\ViewInterface $view
	 * @return void
	 */
	protected function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {

		// We don't need to do all this for json responses.
		if ($this->request->hasArgument('json')) {
			return;
		}

		// Are user an Editor?
		$isEditor = 0;
		if ($this->securityContext->hasRole('_OurBrand_.Business:worker')) {
			$isEditor = 1;
		}

		// Are user an Admin?
		$isAdmin = 0;
		if ($this->currentUser->isAdministrator()){
			$isAdmin = 1;
		}

		$inDev = 0;
		if(strstr($_SERVER['HTTP_HOST'],'.local')){
			$inDev = 1;
		}

		// Get file stamp
		$fileStamp = time();
		if ($this->environment->getContext() == 'Production' && file_exists(FLOW_PATH_ROOT.'Data/Temporary/Production/Configuration/ProductionConfigurations.php')) {
			$fileStamp = @filemtime(FLOW_PATH_ROOT.'Data/Temporary/Production/Configuration/ProductionConfigurations.php');
		}

		// Exercise categories
		$exerciseCategoryRepository = new \_OurBrand_\Quiz\Domain\Repository\ExerciseCategoryRepository();
		$exerciseCategories         = $exerciseCategoryRepository->findAll();
		$subjectRepository          = new \_OurBrand_\Quiz\Domain\Repository\SubjectRepository();

		$this->view->assign('archiveUri', $this->getArchiveUri());
		$this->view->assign('UIPath', $this->settings['UIPath']);
		$this->view->assign('isEditor', $isEditor);
		$this->view->assign('isAdmin', $isAdmin);
		$this->view->assign('inDev', $inDev);
		$this->view->assign('logintime', $fileStamp); // When was system updated?
		$this->view->assign('exerciseCategories', $exerciseCategories);
		$this->view->assign('user', $this->currentUser);

		if ($this->request->hasArgument('exercise') || $this->request->hasArgument('currentExercise')) {

			$exercise = $this->getExerciseFromArgument();

			if (is_a($exercise, '\_OurBrand_\Quiz\Domain\Model\Exercise')) {

				// Set type
				$objectName = explode('\\', get_class($exercise));
				$exerciseType = $this->exerciseTypeRepository->findOneByObjectName(array_pop($objectName));
				$exercise->setType($exerciseType);

				$durations = $this->getDurationsForExercise($this->settings['exercise']['durations']);

				$this->view->assign('editExerciseDurations', $durations);
				$this->view->assign('editExerciseCategories', $this->getExerciseCategories($exercise));
				$this->view->assign('editExerciseDifficulties', $this->getDifficultiesForExercise());

				$this->view->assign('previewExerciseDuration', $this->getExerciseDurationLabel($exercise));
				$this->view->assign('previewExerciseSkill', $this->getExerciseSkillLabel($exercise));
				$this->view->assign('previewExerciseDifficulty', $this->getExerciseDifficultyLabel($exercise));

				$this->view->assign('previewExerciseIsHintSet', $exercise->getHint() != '' ? 1 : 0);
				$this->view->assign('previewExerciseIsExplanationSet', $exercise->getExplanation() != '' ? 1 : 0);

				$this->view->assign('subjectOptions', $subjectRepository->findAll());
				$this->view->assign('subjectPlaceholder', $this->translateById('quiz.placeholder.subject'));
			}

			$quiz = $exercise->getQuiz();
		} else if ($this->request->hasArgument('quiz')) {
			$quiz = $this->getQuizFromArgument();
		}

		// Get/Set duration.
		$duration = 0;
		if (isset($quiz) && is_a($quiz, '\_OurBrand_\Quiz\Domain\Model\Quiz')) {
			$duration = $quiz->getDuration();
		}
		$this->view->assign('duration', gmdate("H:i", $duration));
	}

	/**
	 * Creates the return-to-My uri
	 *
	 * @return string
	 */
	protected function getArchiveUri() {

		// Archive type.
		$type = $this->getReturnToArchiveType();

		// Archive action
		$action = '';
		switch ($type) {
			case 0:
				$action = 'showExams';
				break;
			case 1:
				$action = 'showTests';
				break;
			case 2:
				$action = 'showTraining';
				break;
		}

		// Archive Uri
		$archiveUri = $this->buildUri('quizArchive', $action, array(), '_OurBrand_.My', true);

		return $archiveUri;
	}

	/**
	 * Build an Uri.
	 *
	 * @param string $controller
	 * @param string $action
	 * @param array $arguments
	 * @param string $package
	 * @param bool $absoluteUri
	 * @return mixed
	 * @throws \Exception
	 */
	protected function buildUri($controller, $action, $arguments = array(), $package = '', $absoluteUri = false) {
		$uriBuilder = $this->controllerContext->getUriBuilder();

		$uriBuilder
			->reset()
			->setCreateAbsoluteUri($absoluteUri);

		try {
			$uri = $uriBuilder->uriFor($action, $arguments, $controller, $package);
		} catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), $exception->getCode(), $exception);
		}
		return $uri;
	}

	/**
	 * This returns the type of what archive the teacher should return to.
	 *
	 * If we are able to get a Quiz, take type from there, so that when the teacher returns he sees the quiz in
	 * the archive he has chosen it to be in.
	 *
	 * If the teacher made a mistake and wants to return before that, it will return to the archive where the teacher
	 * pressed the plus button.
	 *
	 * @return int
	 */
	protected function getReturnToArchiveType() {
		$type = 0;
		$quiz = $this->getQuizFromArgument();
		if (is_a($quiz, '\_OurBrand_\Quiz\Domain\Model\Quiz')) {
			$type = $quiz->getType();
		} elseif ($this->request->hasArgument('type')) {
			$type = $this->request->getArgument('type');
		}
		return $type;
	}

	/**
	 * @return null
	 */
	protected function getQuizFromArgument() {
		$quizIdentifier = null;
		if ($this->request->hasArgument('quiz')) {
			$quizIdentifier = $this->request->getArgument('quiz');
		}
		if (is_array($quizIdentifier)) {
			$quizIdentifier = $quizIdentifier["__identity"];
		}
		$quiz = null;
		if ($quizIdentifier != '') {
			$quiz = $this->persistenceManager->getObjectByIdentifier(
				$quizIdentifier,
				'\_OurBrand_\Quiz\Domain\Model\Quiz'
			);
		}
		return $quiz;
	}

	/**
	 * @return null
	 */
	protected function getExerciseFromArgument() {
		if ($this->request->hasArgument('exercise')) {
			$exerciseIdentifier = $this->request->getArgument('exercise');
		}
		if ($this->request->hasArgument('currentExercise')) {
			$exerciseIdentifier = $this->request->getArgument('currentExercise');
		}
		if (is_array($exerciseIdentifier)) {
			$exerciseIdentifier = $exerciseIdentifier["__identity"];
		}
		$exercise = null;
		if ($exerciseIdentifier != '') {
			$exercise = $this->persistenceManager->getObjectByIdentifier(
				$exerciseIdentifier,
				'\_OurBrand_\Quiz\Domain\Model\Exercise'
			);
		}
		return $exercise;
	}


	/**
	 * Get translation for specified id from Main.xlf from current package.
	 *
	 * @param string $labelId
	 * @param array $arguments
	 * @return mixed
	 */
	protected function translateById($labelId, array $arguments = array()) {
		return $this->translator->translateById(
			$labelId,
			$arguments,
			null,
			null,
			'Main',
			$this->request->getControllerPackageKey()
		);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return string
	 */
	protected function getExerciseDurationLabel(\_OurBrand_\Quiz\Domain\Model\Exercise $exercise) {
		$title = $this->translateById('exercise.duration.placeholder');
		$duration = $exercise->getDuration();
		if (intval($duration) > 0) {
			$title = $this->translateById('exercise.duration', array('duration' => '&nbsp;' . $duration));
		}
		return $title;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return string
	 */
	protected function getExerciseSkillLabel(\_OurBrand_\Quiz\Domain\Model\Exercise $exercise) {
		$title = $this->translateById('exercise.skill.placeholder');
		$skill = $exercise->getExerciseSkill();
		if (is_a($skill, '\_OurBrand_\Quiz\Domain\Model\ExerciseSkill')) {
			$title = $skill->getTitle();
		}
		return $title;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return string
	 */
	protected function getExerciseDifficultyLabel(\_OurBrand_\Quiz\Domain\Model\Exercise $exercise) {
		$title = $this->translateById('exercise.difficulty.placeholder');
		$difficulty = $exercise->getDifficulty();
		if (is_a($difficulty, '\_OurBrand_\Quiz\Domain\Model\Difficulty')) {
			$title = $this->translateById('exercise.difficulty', array('difficulty' => $difficulty->getTitle()));
		}
		return $title;
	}

	/**
	 * @return array
	 */
	protected function getDifficultiesForExercise() {
		$difficultyRepository = new \_OurBrand_\Quiz\Domain\Repository\DifficultyRepository();
		$difficultyObjects = $difficultyRepository->findAll(); //@TODO: change when language.

		$difficulties = array();
		foreach ($difficultyObjects as $difficultyObject) {
			$difficulties[$difficultyObject->getSorting()] = $difficultyObject;
		}
		ksort($difficulties);
		$out = array();
		foreach ($difficulties as $difficultyObject) {
			$id = $this->persistenceManager->getIdentifierByObject($difficultyObject);
			$out[$id] = $difficultyObject->getTitle();
		}

		return $out;
	}

	/**
	 * @return array
	 */
	protected function getDurationsForExercise($durations) {
		$out = array();
		if (is_string($durations) && strstr($durations, ',')) {
			$durations = $this->trimExplode(',', $durations);
			foreach ($durations as $duration) {
				$out[$duration] = $duration . ' min';
			}
			ksort($out);
		}
		return $out;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return array
	 */
	protected function getExerciseCategories(\_OurBrand_\Quiz\Domain\Model\Exercise $exercise) {
		$out = array();

		$quiz = $exercise->getQuiz();

		if (is_a($quiz, '\_OurBrand_\Quiz\Domain\Model\Quiz')) {

			$subjects = $this->resolveSubjectObjectsIntoArrayByQuiz($quiz);

			if (is_array($subjects) && count($subjects) > 0) {
				foreach ($subjects as $subject) {
					if (is_array($subject['categories']) && count($subject['categories']) > 0) {
						foreach ($subject['categories'] as $category) {
							$out[] = array(
								'label' => $category['title'] . ' (' . $subject['title'] . ')',
								'children' => $category['skills']
							);
						}
					}
				}
			}
		}

		return $out;
	}


	/**
	 * @param $split
	 * @param $string
	 * @return array
	 */
	private function trimExplode($split, $string) {
		$out = array();
		$array = explode($split, $string);
		foreach ($array as $value) {
			$out[] = trim($value);
		}
		return $out;
	}

	/**
	 * Makes sure that subjects are sorted and resolved
	 *
	 * @param $quiz
	 * @return array
	 */
	private function resolveSubjectObjectsIntoArrayByQuiz($quiz) {
		$subjectObjects = $quiz->getSubjects();
		$subjects = array();
		if (is_object($subjectObjects) && count($subjectObjects) > 0) {
			foreach ($subjectObjects as $subjectObject) {
				if (is_a($subjectObject, '\_OurBrand_\Quiz\Domain\Model\Subject')) {
					$categories = $this->resolveCategoryObjectsIntoArrayBySubject($subjectObject);
					$subjects[$subjectObject->getTitle()] = array(
						'title' => $subjectObject->getTitle(),
						'value' => $this->persistenceManager->getIdentifierByObject($subjectObject),
						'categories' => $categories,
					);
				}
			}
			ksort($subjects);
		}
		return $subjects;
	}

	/**
	 * Makes sure that categories are sorted and resolved
	 *
	 * @param $subjectObject
	 * @return array
	 */
	private function resolveCategoryObjectsIntoArrayBySubject($subjectObject) {
		$categoryRepository = new \_OurBrand_\Quiz\Domain\Repository\QuizCategoryRepository();
		$categoryObjects = $categoryRepository->findBySubject($subjectObject);
		$categories = array();
		if (is_object($categoryObjects) && count($categoryObjects) > 0) {
			foreach ($categoryObjects as $categoryObject) {
				if (is_a($categoryObject, '\_OurBrand_\Quiz\Domain\Model\Category')) {
					$skills = $this->resolveSkillObjectsIntoArrayByCategory($categoryObject);
					$categories[$categoryObject->getTitle()] = array(
						'title' => $categoryObject->getTitle(),
						'value' => $this->persistenceManager->getIdentifierByObject($categoryObject),
						'skills' => $skills
					);
				}
			}
			ksort($categories);
		}
		return $categories;
	}

	/**
	 * Makes sure that skills are sorted and resolved
	 *
	 * @param $categoryObject
	 * @return array
	 */
	private function resolveSkillObjectsIntoArrayByCategory($categoryObject) {
		$skillRepository = new \_OurBrand_\Quiz\Domain\Repository\SkillRepository();
		$skillObjects = $skillRepository->findByCategory($categoryObject);
		$skills = array();
		if (is_object($skillObjects) && count($skillObjects) > 0) {
			foreach ($skillObjects as $skillObject) {
				if (is_a($skillObject, '\_OurBrand_\Quiz\Domain\Model\ExerciseSkill')) {
					$skills[$this->persistenceManager->getIdentifierByObject($skillObject)] = $skillObject->getTitle();
				}
			}
			ksort($skills);
		}
		return $skills;
	}

	/**
	 * Does the student has access?
	 *
	 * Checks if this is a correct demo access or if the real user has access
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @return bool
	 */
	protected function studentHasAccess($studentQuizSession) {

		if ((true === $this->currentUser->IsDemoUser() // if user IS a demo user
				&& true === $studentQuizSession->getQuizSession()->getIsDemo()) // , and quizsession is a demo
			|| $studentQuizSession->getStudent() === $this->currentUser->getIdentifier()) {

			return true;
		}

		return false;
	}


	/**
	 * Updates Quiz before updating Exercise. This function is needed when user saves Exercise for Portal
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @param array $quizArguments
	 */
	public function updateQuizBeforeUpdatingExercise($quiz, $quizArguments) {
		$quizArguments['subject'] = $this->subjectRepository->findByIdentifier($quizArguments['subject']);
		$quiz->setPropertiesForPortalQuizWhenEditsTheExercise($quizArguments);
		$this->quizRepository->update($quiz);
	}
}

