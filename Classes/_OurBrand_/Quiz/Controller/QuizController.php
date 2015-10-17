<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use _OurBrand_\Quiz\Domain\Model\QuizSession;
use _OurBrand_\Quiz\Domain\Model\StudentQuizSession;
use TYPO3\Flow\Annotations as Flow;
use \_OurBrand_\Quiz\Domain\Model\Quiz;
use TYPO3\Flow\I18n\Exception\InvalidArgumentException;
use TYPO3\Flow\Security\Exception\AccessDeniedException;
use _OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback;

class QuizController extends AbstractController {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TeamLevelRepository
	 * @Flow\Inject
	 */
	protected $teamLevelRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\GradeRepository
	 * @Flow\Inject
	 */
	protected $gradeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizCategoryRepository
	 * @Flow\Inject
	 */
	protected $quizCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizGradeRepository
	 * @Flow\Inject
	 */
	protected $quizGradeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizTypeRepository
	 * @Flow\Inject
	 */
	protected $quizTypeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ContentCategoryRepository
	 * @Flow\Inject
	 */
	protected $contentCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Service\QuizService
	 * @Flow\Inject
	 */
	protected $quizService;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TrackStudentAudioPlaybackRepository
	 * @Flow\Inject
	 */
	protected $trackStudentAudioPlaybackRepository;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizSessionRepository
	 * @Flow\inject
	 */
	protected $quizSessionRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\StudentQuizSessionRepository
	 * @Flow\inject
	 */
	protected $studentQuizSessionRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TagRepository
	 * @Flow\inject
	 */
	protected $tagRepository;


	/*
	 * List of quiz types
	 */
	private $typeOptions = array(
		0 => "Exam",
		1 => "Test",
		2 => "Part of Activity"
	);

	/*
	 * Default time of duration for Test and Training (in sec.)
	 */
	private $defaultDuration = 230400;


	/**
	 * @return void
	 */
	public function indexAction() {
		if ($this->currentUser->isInstructor() || $this->currentUser->isEditor()) {
			if ($this->currentUser->isWorker()) {
				$quizzes = $this->quizRepository->findAllQuiz();
			} else {
				$quizzes = $this->quizRepository->findAllByUser($this->currentUser->getIdentifier());
			}

			$ABO2API = new \_OurBrand_\ABO2API\Api\Abo2Api();
			$teams = $ABO2API->getTeamsForAccountUser($this->currentUser->getIdentifier());

			$sessions = $this->studentQuizSessionRepository->findAll();
			$this->view->assign('quizzes', $quizzes);
			$this->view->assign('teams', $teams);
			$this->view->assign('studentQuizSessions', $sessions);
		} else {
			$this->redirect('studentindex');
		}
	}


	/**
	 * @return void
	 */
	public function studentIndexAction() {

		if ($this->currentUser->isInstructor()) {
			$this->redirect('index');
		}

		$sessions = $this->studentQuizSessionRepository->findByStudent($this->currentUser->getIdentifier());

		$this->view->assign('studentQuizSessions', $sessions);
		$this->view->assign('disableContinuesSave', true);
		$this->view->assign('hideFinishButtonAndClock', true);
		$this->view->assign('title', $this->translateById('overview'));
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 */
	public function createAction($quiz = null) {

		if (!$quiz) {
			$quiz = new \_OurBrand_\Quiz\Domain\Model\Quiz();
		}

		if ($this->request->hasArgument('quiztype')) {
			$quiz->setType((int)$this->request->getArgument('quiztype'));
		}

		if (!$this->accessHelper->canUserCreateQuiz($this->currentUser, $quiz->getType())) {
			$this->throwStatus(403);
		}


		$quiz->setCreator($this->currentUser->getIdentifier());
		$quiz->setAuthor($this->currentUser->getName());
		$quiz->setQuiz($this->currentUser->isworker());
		$this->quizRepository->add($quiz);
		$this->persistenceManager->persistAll();

		$this->redirect('edit', 'quiz', null, array('quiz' => $quiz));
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 * @throws \Exception
	 */
	public function deleteAction($quiz) {

		if ($this->currentUser->isworker()) {
			$sessions = $this->studentQuizSessionRepository->findByQuiz($quiz);
			if ($sessions->count() > 0) {
				throw new \Exception('Can not delete quiz that has been assigned');
			}

			$quiz->touch();
			$quiz->setIsDeleted(true);

			$this->quizRepository->update($quiz);
			$this->persistenceManager->persistAll();
		}
		$this->redirect('index');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 * @throws \Exception
	 */
	public function editAction($quiz) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $quiz)) {
			$this->throwStatus(403);
		}

		if($quiz->getSnapshotOf() instanceof Quiz) {
			$this->redirect('edit', 'quiz', '_OurBrand_.quiz', array('quiz' => $quiz->getSnapshotOf()));
		}

		// TODO: Work over
		$sessions = $this->studentQuizSessionRepository->findByQuiz($quiz);
		if ($sessions->count() > 0) {
			throw new \Exception('Can not edit quiz that has been assigned');
		}

		$this->view->assign('quiz', $quiz);
		$this->view->assign('isEditingQuiz', 1);
		$this->view->assign('defaultDuration', $this->defaultDuration / 60);
		$this->view->assign('typeOptions', $this->typeOptions);
		$this->view->assign('subjectOptions', $this->subjectRepository->findAll());
		$this->view->assign('teamLevelOptions', $this->teamLevelRepository->findAll());
		$this->view->assign('teamLevelPlaceholder', $this->translateById('quiz.placeholder.teamLevel'));
		$this->view->assign('typePlaceholder', $this->translateById('quiz.placeholder.type'));
		$this->view->assign('subjectPlaceholder', $this->translateById('quiz.placeholder.subject'));
		$this->view->assign('introductionPlaceholder', $this->translateById('quiz.placeholder.introduction'));
	}

	/**
	 * Displays the finish page.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function finishAction(\_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $quiz)) {
			$this->throwStatus(403);
		}

		// Todo: Should never be necessary. Quiz that has not been finished should not have been assigned.
		$sessions = $this->studentQuizSessionRepository->findByQuiz($quiz);
		if ($sessions->count() > 0) {
			throw new \Exception('Can not edit quiz that has been assigned');
		}

		// Get data for view.
		$contentCategoryOptions = $this->contentCategoryRepository->findAll();
		$gradeOptions = $this->gradeRepository->findAll();
		$quizGrades = $this->quizGradeRepository->findByQuiz($quiz);
		$subjects = $quiz->getSubjects();
		$quizCategoryOptions = $this->getQuizCategoryOptions($subjects, $quiz->getType());
		$quizTypeOptions = $this->quizTypeRepository->findBySubjectsAndProduct($subjects, $quiz->getType());
		$tagOptions = $this->tagRepository->findAll();

		// Handle quiz grades
		if ($quiz->getMaxScore() < count($gradeOptions)) {
			foreach ($quizGrades as $quizGrade) {
				$this->quizGradeRepository->remove($quizGrade);
			}
			$this->persistenceManager->persistAll();
		} elseif (!$quizGrades->count()) {
			$this->preFillGrades($quiz, $gradeOptions);
			$quizGrades = $this->quizGradeRepository->findByQuiz($quiz);
		}

		// Are we handling a special subject
		$hasLanguageSubject = 0;
		foreach ($quiz->getSubjects() as $subject) {
			if ($subject->getIsLanguage()) {
				$hasLanguageSubject = 1;
				break;
			}
		}

		// Handle duration
		$duration = $quiz->getDuration();
		$minAndSeconds = gmdate("H:i", $duration);
		$parts = explode(':', $minAndSeconds);
		$minutes = $parts[1];
		$hours = $parts[0];
		$subjectIsEmpty = $subjects->isEmpty() ? 1 : 0;

		$this->view->assign('subjectIsEmpty', $subjectIsEmpty);
		$this->view->assign('isFinishingQuiz', 1);
		$this->view->assign('hasLanguageSubject', $hasLanguageSubject);
		$this->view->assign('minutes', $minutes);
		$this->view->assign('hours', $hours);
		$this->view->assign('defaultDuration', $this->defaultDuration / 60);
		$this->view->assign('quiz', $quiz);
		$this->view->assign('quizGrades', $quizGrades);
		$this->view->assign('quizTypeOptions', $quizTypeOptions);
		$this->view->assign('quizCategoryOptions', $quizCategoryOptions);
		$this->view->assign('contentCategoryOptions', $contentCategoryOptions);
		$this->view->assign('gradeOptions', $gradeOptions);
		$this->view->assign('tagOptions', $tagOptions);
	}

	/**
	 * Get Category options
	 *
	 * @param $subjects
	 * @param $type
	 * @return array
	 */
	protected function getQuizCategoryOptions($subjects, $type) {
		$options = array();
		$quizCategories = $this->quizCategoryRepository->findBySubjectsAndProductAndParentCategory($subjects, $type, null);

		foreach ($quizCategories as $quizCategory) {

			$categoryId = $this->persistenceManager->getIdentifierByObject($quizCategory);
			$options[$categoryId] = $quizCategory->getValue();

			$subs = $quizCategory->getSubCategories();
			if (is_object($subs) && count($subs)) {
				foreach ($subs as $sub) {
					$subCategoryId = $this->persistenceManager->getIdentifierByObject($sub);
					$options[$subCategoryId] = $quizCategory->getValue() . ' - ' . $sub->getValue();
				}
			}
		}


		return $options;
	}

	/**
	 * JSON function, used for autocomplete Input on the finish page.
	 *
	 */
	public function getTagsAction() {
		$q = '';
		if ($this->request->hasArgument('q')) {
			$q = $this->request->getArgument('q');
		}
		$tags = $this->tagRepository->findAllStartingWithLetters($q);
		$result = array();
		foreach ($tags as $tag) {
			$result[] = array(
				'id' => $this->persistenceManager->getIdentifierByObject($tag),
				'text' => $tag->getValue()
			);
		}

		$this->view->assign('value', $result);
	}

	/**
	 * Set Quiz Grades for this quiz from data array.
	 * Data array must be in this format:
	 * <code>
	 * $quizGrades = array(
	 *  [gradeIdentifier] = array(
	 *    'minimumScore' => 10
	 *    'maximumScore' => 15
	 *   ),
	 *  [gradeIdentifier] = array(
	 *    'minimumScore' => 16
	 *    'maximumScore' => 20
	 *   )
	 * );
	 * </code>
	 *
	 * @param array $quizGrades
	 */
	public function updateQuizGradesFromArray($quizGrades) {
		foreach ($quizGrades as $data) {
			$quizGrade = $this->quizGradeRepository->findOneByQuizAndGrade($data['quiz'], $data['grade']);
			if (!is_null($quizGrade)) {
				$quizGrade->setMinimumScore($data['minimumScore']);
				$quizGrade->setMaximumScore($data['maximumScore']);

				$this->quizGradeRepository->update($quizGrade);
			}
		}
	}


	/**
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	protected function preFillGrades($quiz) {
		$gradeOptions = $this->gradeRepository->findAll(); // Sorted Highest to Lowest grade
		$maxScore = $quiz->getMaxScore();

		if ($maxScore >= count($gradeOptions)) {
			$increment = max(floor($maxScore / (count($gradeOptions) - 1)), 1);

			$lastMax = $maxScore;
			$lastMin = max($maxScore - $increment, 0) + 1;

			//Always end with 0 (also in the start)
			if ($lastMin - $increment < 1) {
				$lastMin = 0;
			}
			$iteration = 1;
			foreach ($gradeOptions as $grade) {
				if ($iteration == count($gradeOptions) - 1) {
					//Second last always has a minimum of 1
					$lastMin = 1;
				} elseif ($iteration == count($gradeOptions)) {
					//Always end with 0/0 for the lowest grade
					$lastMax = 0;
					$lastMin = 0;
				}

				$quizGrade = new \_OurBrand_\Quiz\Domain\Model\QuizGrade;
				$quizGrade->setQuiz($quiz);
				$quizGrade->setGrade($grade);

				$quizGrade->setMaximumScore($lastMax);
				$lastMax = max($lastMax - $increment, 0);

				$quizGrade->setMinimumScore($lastMin);
				$lastMin = max($lastMax - $increment, 0) + 1;

				$this->quizGradeRepository->add($quizGrade);

				$iteration++;
			}

			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * Used for auto-saving. We allow for the quiz to not
	 * fulfill validation rules.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @Flow\IgnoreValidation("$quiz")
	 */
	public function updateSilentAction($quiz) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $quiz)) {
			$this->throwStatus(403);
		}

		try {
			$this->accessHelper->checkQuizAccordingToCurrentAccess($quiz, $this->currentUser);
		} catch(\Exception $e) {
			$code = $e->getCode();
			$field = '';
			switch ($code) {
				case \_OurBrand_\Quiz\Service\AccessHelper::EXCEPTION_ILLEGAL_TYPE:
					$field = 'type';
				break;
				/* Subject and level are always allowed for all users.
				case \_OurBrand_\Quiz\Service\AccessHelper::EXCEPTION_ILLEGAL_SUBJECT:
					$field = 'subjects';
				break;
				case \_OurBrand_\Quiz\Service\AccessHelper::EXCEPTION_ILLEGAL_LEVEL:
					$field = 'levels';
				break;
				*/
			}
			$this->view->assign(
				'value',
				array(
					'message' => 'access-error',
					'field' => $field,
					'readyForCompletion' => $quiz->getReadyForCompletion(),
					'exercises' => array()
				)
			);
			$this->throwStatus(403);
			return;
		}


		if ($this->request->hasArgument('quizGrade')) {
			$data = $this->request->getArgument('quizGrade');

			$quizGrades = array();
			foreach ($data as $identifier => $values) {
				$quizGrades[] = array(
					'quiz' => $quiz,
					'grade' => $this->persistenceManager->getObjectByIdentifier(
							$identifier,
							'\_OurBrand_\Quiz\Domain\Model\Grade'
						),
					'minimumScore' => (isset($values['minimumScore']) ? $values['minimumScore'] : 0),
					'maximumScore' => (isset($values['maximumScore']) ? $values['maximumScore'] : $quiz->getMaxScore())
				);
			}
			$this->updateQuizGradesFromArray($quizGrades);
		}

		if ($this->request->hasArgument('knowledgePrerequisites')) {
			$knowledgePrerequisites = $this->request->getArgument('knowledgePrerequisites');
			$quiz->setKnowledgePrerequisitesFromArray($knowledgePrerequisites);
		}

		if ($this->request->hasArgument('tags')) {
			$tags = explode(',', $this->request->getArgument('tags'));
			$quiz->setTagsFromArray($tags);
		}
		$this->updateQuiz($quiz, true);

		if ($this->request->hasArgument('json')) {
			$exercises = array();
			/** @var \_OurBrand_\Quiz\Domain\Model\Exercise $exercise */
			foreach ($quiz->getExercises() as $exercise) {
				$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
				$exercises[$identifier] = $exercise->getReadyForCompletion();
			}

			$this->view->assign(
				'value',
				array(
					'message' => 'ok',
					'readyForCompletion' => $quiz->getReadyForCompletion(),
					'exercises' => $exercises
				)
			);
		} else {
			$this->redirect('edit', null, null, array('quiz' => $quiz));
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function updateAction($quiz) {

		if ($this->request->hasArgument('knowledgePrerequisites')) {
			$knowledgePrerequisites = $this->request->getArgument('knowledgePrerequisites');
			$quiz->setKnowledgePrerequisitesFromArray($knowledgePrerequisites);
		}

		$this->updateQuiz($quiz, true);

		if ($this->request->hasArgument('json')) {
			$this->view->assign('value', array('message' => 'ok', 'object' => $quiz));
		} else {
			$this->redirect('edit', null, null, array('quiz' => $quiz));
		}
	}

	/**
	 *
	 */
	public function updateArchiveLinkAction() {
		$this->view->assign(
			'value',
			array(
				'archiveUri' => $this->getArchiveUri($this->request->getArgument('type'))
			)
		);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 * @throws \Exception
	 */
	public function completeAction($quiz) {
		
		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $quiz)) {
			$this->throwStatus(403);
		}

		//Extra security check if ready for completion, otherwise we can't finish
		if (!$quiz->getReadyForCompletion()) {
			throw new \Exception('Quiz is not ready for completion');
		}
		$this->updateQuiz($quiz, false);
		$this->redirect('index');
	}

	/**
	 * Update draft status, touch and change duration.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @param boolean $draft
	 */
	public function updateQuiz($quiz, $draft) {
		$quiz->touch();
		$quiz->calculateDuration();
		$quiz->setIsDraft($draft);

		if ($this->request->hasArgument('category')) {
			$category = $this->persistenceManager->getObjectByIdentifier(
				$this->request->getArgument('category'),
				'\_OurBrand_\Quiz\Domain\Model\Category'
			);
			$quiz->setCategory($category);
		}

		if ($draft == FALSE) {
			$this->quizService->makeSnapshotAndPersist($quiz);
		}

		$this->quizRepository->update($quiz);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @Flow\IgnoreValidation("$quiz")
	 * @throws \Exception
	 */
	public function destroyAction($quiz) {
		// Several things we need to check for. If the quiz has been assigned to
		// student we may have a problem. (we do)
		$sessions = $this->studentQuizSessionRepository->findByQuiz($quiz);
		if ($sessions->count() > 0) {
			throw new \Exception('Can not delete quiz that has been assigned');
		}

		$this->quizRepository->remove($quiz);
		$this->redirect('index');
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function duplicateAction($quiz) {

		$newQuiz = $this->quizService->makeCopy($quiz);

		if ($newQuiz == null) {
			// TODO: User error handling.
		}

		$newQuiz->setCreator($this->currentUser->getIdentifier());
		$newQuiz->setIsDraft(true);

		$this->quizRepository->add($newQuiz);

		$this->persistenceManager->persistAll();
		$this->redirect('edit', null, null, array('quiz' => $newQuiz));
	}


	/**
	 * Mainly included for test purposes.
	 * @param string $teamIdentifier
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function createStudentQuizSessionAction($teamIdentifier, $quiz) {
		//Found in ABO2API
		$ABO2API = new \_OurBrand_\ABO2API\Api\Abo2Api();
		$team = $ABO2API->getTeamByIdentifier($teamIdentifier);

		$quizAssignment = new \_OurBrand_\Quiz\Domain\Model\QuizAssignment();
		$quizAssignment->setInstructorIdentifier($this->currentUser->getIdentifier());
		$quizAssignment->setQuiz($quiz);
		$quizAssignment->setTeamIdentifier($teamIdentifier);

		$students = $team->getAccountUsers();
		foreach ($students as $student) {
			$studentQuizAssignment = new \_OurBrand_\Quiz\Domain\Model\StudentQuizAssignment();
			$studentQuizAssignment->setStudentIdentifier($student->getUser()->getUsername());
			$studentQuizAssignment->setQuizAssignment($quizAssignment);
			//$studentQuizAssignment->setTimeToComplete($studentQuizAssignment->getQuiz()->getDuration());
			$quizAssignment->addStudentAssignment($studentQuizAssignment);
		}

		$this->quizService->assignQuizToStudents($quizAssignment);

		$this->redirect('index');
	}


	/**
	 * Mainly included for test purposes.
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function deleteStudentQuizSessionAction($studentQuizSession) {
		$this->view->assign('session', $studentQuizSession);
	}


	/**
	 * Mainly included for test purposes.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param bool $force
	 *
	 * @throws \Exception
	 */
	public function destroyStudentQuizSessionAction($studentQuizSession, $force = false) {
		if ($studentQuizSession->getStartTime() && !$force) {
			// TODO: Correct exception.
			throw new \Exception('Can not delete session which has been started');
		}

		$answers = $this->answerRepository->findBySession($studentQuizSession);
		foreach ($answers as $answer) {
			$this->answerRepository->remove($answer);
		}

		$trackers = $this->trackStudentAudioPlaybackRepository->findByStudentQuizSession($studentQuizSession);
		foreach ($trackers as $tracker) {
			$this->trackStudentAudioPlaybackRepository->remove($tracker);
		}

		$this->studentQuizSessionRepository->remove($studentQuizSession);
		$this->redirect('index');
	}


	/**
	 * @param array $exercises
	 */
	public function reOrderExercisesAction($exercises) {
		foreach ($exercises as $number => $identifier) {
			$exercise = $this->persistenceManager->getObjectByIdentifier(
				$identifier,
				'\_OurBrand_\Quiz\Domain\Model\Exercise'
			);
			if ($exercise) {
				$exercise->setNumber($number);
			}
			$this->exerciseRepository->update($exercise);
		}
		$this->persistenceManager->persistAll();
		$this->view->assign('value', array('message' => 'ok'));
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @throws AccessDeniedException
	 */
	public function showStartAction($studentQuizSession) {

		if (!$this->studentHasAccess($studentQuizSession)) {
			throw new AccessDeniedException('Access denied');
		}

		if ($studentQuizSession->getFinishedTime() != null) {
			// TODO: Trigger nicer error
			throw new \Exception('You can\'t take this test again');
		}

		$quiz = $studentQuizSession->getQuizSession()->getQuiz();
		$quizTypes = $this->typeOptions;
		$quizType = isset($quizTypes[$quiz->getType()]) ? $quizTypes[$quiz->getType()] : "";

		// if quiz is Exam then show specified time by author, otherwise show default time of duration
		$duration = ($quiz->getType() == Quiz::EXAM_TYPE) ? $quiz->getDuration() : $this->defaultDuration;

		$this->view->assign('duration', $duration);
		$this->view->assign('isAudioPresents', $this->isAudioPresents($quiz));
		$this->view->assign('attachments', $this->getAllAttachments($quiz));
		$this->view->assign('durationLastMin', substr($duration / 60, -1, 1));
		$this->view->assign('quizType', $quizType);
		$this->view->assign('disableContinuesSave', true);
		$this->view->assign('showUserName', true);
		$this->view->assign('hideFinishButtonAndClock', true);
		$this->view->assign('quiz', $quiz);
		$this->view->assign('session', $studentQuizSession);
	}

	/**
	 * Is any audio file in exercises in quiz?
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @return bool
	 */
	private function isAudioPresents($quiz) {
		$isAudioPresents = false;
		foreach ($quiz->getExercises() as $exercise) {
			if ($exercise->getSoundFile() !== null) {
				$isAudioPresents = true;
			}
			if (is_a($exercise, '_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarInsertDictationExercise')
				|| is_a($exercise, '_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarDictationExercise')
			) {
				foreach ($exercise->getSnippets() as $snippet) {
					if (!is_null($snippet->getAudio())) {
						$isAudioPresents = true;
						break;
					}
				}
			}
		}

		return $isAudioPresents;
	}

	/**
	 * Get all PDF, Audio and RichTexts from quiz
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @return array
	 */
	private function getAllAttachments($quiz) {
		$attachment = array();

		foreach ($quiz->getExercises() as $exerciseId => $exercise) {
			$pdf = $exercise->getPdfFile();
			$sound = $exercise->getSoundFile();
			$text = $exercise->getTextContent();

			if (!empty($sound)) {
				$name = $exercise->getSoundFile()->getTitle();
				$attachment[$name . $exerciseId] = array(
					"content" => "Sound",
					"type" => $exercise->getSoundFile()->getType(),
					"title" => $name,
					"exercise" => $exerciseId,
				);
			}
			if (!empty($pdf)) {
				$name = $exercise->getPdfFile()->getTitle();
				$attachment[$name . $exerciseId] = array(
					"content" => "Pdf",
					"type" => $exercise->getPdfFile()->getType(),
					"title" => $name,
					"exercise" => $exerciseId,
				);
			}
			if (!empty($text)) {
				$name = $exercise->getTextContent()->getTitle();
				$type = $exercise->getTextContent()->getType();
				$attachment[$name . $exerciseId] = array(
					"content" => "Text",
					"type" => empty($type) ? 0 : $type,
					"title" => $name,
					"exercise" => $exerciseId,
				);
			}
		}
		usort(
			$attachment,
			function ($a, $b) {
				return strcasecmp($a['title'], $b['title']);
			}
		);

		return $attachment;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @throws \Exception
	 * @throws AccessDeniedException
	 */
	public function previewStartAction($quiz) {
		$quizTypes = $this->typeOptions;
		$quizType = isset($quizTypes[$quiz->getType()]) ? $quizTypes[$quiz->getType()] : "";

		// if quiz is Exam then show specified time by author, otherwise show default time of duration
		$duration = ($quiz->getType() == 0) ? $quiz->getDuration() : $this->defaultDuration;

		$this->view->assign('duration', $duration);
		$this->view->assign('durationLastMin', substr($duration / 60, -1, 1));
		$this->view->assign('isAudioPresents', $this->isAudioPresents($quiz));
		$this->view->assign('attachments', $this->getAllAttachments($quiz));
		$this->view->assign('quizType', $quizType);
		$this->view->assign('disableContinuesSave', true);
		$this->view->assign('hideFinishButtonAndClock', true);
		$this->view->assign('quiz', $quiz);
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('previewSession', array('timeRemaining' => 3600));
	}


	/**
	 * @param string $goto
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function studentNavigateAction($goto, $exercise) {
		$studentQuizSession = $this->studentQuizSession;

		if ($goto == "finish") {
			$this->redirect('studentfinish', 'quiz');
		} elseif ($goto == "next") {
			$exercise = $studentQuizSession->getQuizSession()->getQuiz()->findNextExercise($exercise);
		} elseif ($goto == "prev") {
			$exercise = $studentQuizSession->getQuizSession()->getQuiz()->findPrevExercise($exercise);
		} elseif (is_numeric($goto)) {
			$exercises = $studentQuizSession->getQuizSession()->getQuiz()->getExercises();
			$exercise = $exercises[$goto];
		}

		//Shouldn't happen...
		if (empty($exercise)) {
			$this->redirect('studentindex');
		}

		$this->redirect('show', 'exercise', null, array('exercise' => $exercise));
	}

	/**
	 * @param string $goto
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function previewNavigateAction($goto, $quiz, $exercise = null) {

		if ($quiz->getExercises()->count() == 0 || $exercise == null) {
			$this->redirect('previewstart', 'quiz', null, array('quiz' => $quiz));
		}

		if ($goto == "next" && !is_null($exercise)) {
			$exercise = $quiz->findNextExercise($exercise);
		} elseif ($goto == "prev" && !is_null($exercise)) {
			$exercise = $quiz->findPrevExercise($exercise);
		} elseif (is_numeric($goto)) {
			$exercises = $quiz->getExercises();
			$exercise = $exercises[$goto];
		}

		$this->redirect('preview', 'exercise', null, array('exercise' => $exercise));
	}


	/**
	 * Initialize the start time and current time
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 *
	 * @throws \TYPO3\Flow\Mvc\Exception\InvalidRequestMethodException
	 */
	public function studentStartAction($studentQuizSession) {

		if (!$this->studentHasAccess($studentQuizSession)) {
			$this->throwStatus(403);
		}

		$currentTime = new \TYPO3\Flow\Utility\Now();

		if ($studentQuizSession->getQuizSession()->getStartTime() != null
			&& $studentQuizSession->getQuizSession()->getStartTime() > $currentTime
		) {
			$this->throwStatus(403);
		}

		if ($studentQuizSession->getQuizSession()->getStopTime() != null
			&& $studentQuizSession->getQuizSession()->getStopTime() < $currentTime
		) {
			$this->throwStatus(403);
		}

		$studentQuizSession->setStartTime($currentTime);
		$studentQuizSession->setCurrentTime($currentTime);

		$studentQuizSession->setTimeToComplete($studentQuizSession->getQuizSession()->getQuiz()->getDuration());
		$this->studentQuizSessionRepository->update($studentQuizSession);

		$this->loginSession->setData('studentQuizSession', $studentQuizSession);

		$quiz = $studentQuizSession->getQuizSession()->getQuiz();
		$this->redirect('show', 'exercise', null, array('exercise' => $quiz->getExercises()->first()));
	}

	/**
	 * Update to the current time. Meaning the time between the last "update" and now is lost
	 * Then redirect to the latest exercise without an answer.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function studentResumeAction($studentQuizSession) {

		if (!$this->studentHasAccess($studentQuizSession)) {
			$this->throwStatus(403);
		}

		$quiz = $studentQuizSession->getQuizSession()->getQuiz();
		$studentQuizSession->setCurrentTime(new \DateTime());
		$studentQuizSession->setTimesResumed($studentQuizSession->getTimesResumed() + 1);
		$this->loginSession->setData('studentQuizSession', $studentQuizSession);
		$this->studentQuizSessionRepository->update($studentQuizSession);

		foreach ($quiz->getExercises() as $exercise) {
			$answers = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise);
			if ($answers->count() == 0) {
				$this->redirect('show', 'exercise', null, array('exercise' => $exercise));
			}
		}

		$this->redirect('show', 'exercise', null, array('exercise' => $quiz->getExercises()->first()));
	}

	/**
	 * Register that the student is finished with the quiz. Redirects
	 * to quiz summary page.
	 */
	public function studentFinishAction() {

		// TODO: Redo access
		if (!$this->studentQuizSession) {
			$this->throwStatus(403, 'No quiz session');
		}

		//Save the score to the StudentQuizSession
		$answers = $this->answerRepository->findByStudentQuizSession($this->studentQuizSession);
		$score = 0;
		foreach ($answers as $answer) {
			$score += $answer->getScore();
		}
		$this->studentQuizSession->setScore($score);

		$this->studentQuizSession->setFinishedTime(new \DateTime());
		$this->studentQuizSessionRepository->update($this->studentQuizSession);

		$this->persistenceManager->persistAll();

		// Remove the quiz session from our loginsession object.
		$this->loginSession->setData('studentQuizSession', null);

		$this->redirect('showsummary', 'quiz', null, array('studentQuizSession' => $this->studentQuizSession));
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @throws AccessDeniedException
	 */
	public function showSummaryAction($studentQuizSession) {
		$quizGrades = null;
		// TODO: Access

		// We will have to build some custom objects here.
		$quiz = $studentQuizSession->getQuizSession()->getQuiz();
		/** @var \_OurBrand_\Quiz\Domain\Model\Exercise $exercise */
		foreach ($quiz->getExercises() as $exercise) {
			$answer = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise)->getFirst();
			$exercise->setAnswer($answer);
		}
		
		if ($studentQuizSession->getQuizSession()->getShowGradeOnSummary()) {
			$quizGrades = $this->quizGradeRepository->findOneByQuizAndScore($quiz, $studentQuizSession->getScore());
		}
		
		// need to round percentage score to nearest 10 (20,30,40....), for result image
		$percentageScore = ($studentQuizSession->getScore() / $quiz->getMaxscore() * 100);
		$roundedPercentageScore = (round($percentageScore / 10) * 10);


		$this->view->assign('isDemo', $studentQuizSession->getQuizSession()->getIsDemo());

		$this->view->assign('quiz', $quiz);
		$this->view->assign('quizGrades', $quizGrades);
		$this->view->assign('roundedPercentageScore', $roundedPercentageScore);
		$this->view->assign('studentQuizSession', $studentQuizSession);

		//Disable autosaving here
		$this->view->assign('disableContinuesSave', true);
		$this->view->assign('disableResultsButton', true);

		$this->view->assign('title', $this->translateById('quiz.overview'));
	}
	
	/**
	 * Development function
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function ResetStudentQuizSessionAction($studentQuizSession) {
		$quiz = $studentQuizSession->getQuizSession()->getQuiz();
		foreach ($quiz->getExercises() as $exercise) {
			$previousAnswers = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise);
			$trackers = $this->trackStudentAudioPlaybackRepository->findBySessionAndExercise(
				$studentQuizSession,
				$exercise
			);
			if ($previousAnswers->count() > 0) {
				foreach ($previousAnswers as $previousAnswer) {
					$this->answerRepository->remove($previousAnswer);
				}
			}
			if ($trackers->count() > 0) {
				foreach ($trackers as $tracker) {
					$this->trackStudentAudioPlaybackRepository->remove($tracker);
				}
			}
		}

		$studentQuizSession->setStartTime(null);
		$studentQuizSession->setCurrentTime(null);
		$studentQuizSession->setFinishedTime(null);
		$studentQuizSession->setTimesResumed(0);
		$this->studentQuizSessionRepository->update($studentQuizSession);

		$this->persistenceManager->persistAll();

		$this->redirect('studentindex');
	}
}

