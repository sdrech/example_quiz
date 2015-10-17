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

class AdministrationController extends AbstractController {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\SubjectRepository
	 * @Flow\Inject
	 */
	protected $subjectRepository;

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
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizGradeRepository
	 * @Flow\Inject
	 */
	protected $quizGradeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ContentCategoryRepository
	 * @Flow\Inject
	 */
	protected $contentCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizTypeRepository
	 * @Flow\Inject
	 */
	protected $quizTypeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizCategoryRepository
	 * @Flow\Inject
	 */
	protected $quizCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseSkillCategoryRepository
	 * @Flow\Inject
	 */
	protected $exerciseSkillCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseSkillRepository
	 * @Flow\Inject
	 */
	protected $exerciseSkillRepository;

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
	 * @var \_OurBrand_\My\Domain\Model\User
	 */
	protected $currentUser;

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

	/**
	 * @var \_OurBrand_\Quiz\Service\AccessHelper
	 * @Flow\Inject
	 */
	protected $accessHelper;

	/**
	 * Call parent initialize, and abort if user is not administrator.
	 */
	protected function initializeAction() {

		parent::initializeAction();
		if (!$this->currentUser->isAdministrator()) {
			$this->throwStatus(403);
		}
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$quizzes = $this->quizRepository->findAllNotSnapshot();
		$this->view->assign('quizzes', $quizzes);
	}

	/**
	 * @param Quiz $quiz
	 */
	public function cleanSnapshotsAction(Quiz $quiz) {
		$this->quizService->cleanSnapshots($quiz);
		$this->redirect('index');
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function newQuizTypeAction() {

		$subjects = $this->subjectRepository->findAll();

		if ($this->request->hasArgument('show') && $this->request->getArgument('show')) {
			$subjectId = $this->request->getArgument('show');
			$types = $this->quizTypeRepository->findBySubjectSortBySubject($subjectId, 'ASC');
		} else {
			$types = $this->quizTypeRepository->findAllSortBySubject('ASC');
		}

		$this->view->assign('subjects', $subjects);
		$this->view->assign('types', $types);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function newQuizCategoryAction() {

		$subjects = $this->subjectRepository->findAll();

		if ($this->request->hasArgument('show') && $this->request->getArgument('show')) {
			$subjectId = $this->request->getArgument('show');
			$categories = $this->quizCategoryRepository->findByParentCategoryAndSubjectSortBySubject(NULL, $subjectId, 'ASC');

		} else {
			$categories = $this->quizCategoryRepository->findByParentCategory(NULL);
		}

		$this->view->assign('subjects', $subjects);
		$this->view->assign('categories', $categories);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function newSkillAction() {

		$subjects = $this->subjectRepository->findAll();

		if ($this->request->hasArgument('show') && $this->request->getArgument('show')) {
			$subjectId = $this->request->getArgument('show');
			$categories = $this->exerciseSkillCategoryRepository->findBySubjectSortByCategoryAndSubject($subjectId, 'ASC');
		} else {
			$categories = $this->exerciseSkillCategoryRepository->findAllSortByCategory('ASC');
		}

		$this->view->assign('subjects', $subjects);
		$this->view->assign('categories', $categories);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function editQuizTypeAction(\_OurBrand_\Quiz\Domain\Model\QuizType $quizType) {
		$subjects = $this->subjectRepository->findAll();
		$this->view->assign('subjects', $subjects);
		$this->view->assign('quizType', $quizType);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function editQuizCategoryAction(\_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory) {
		$subjects = $this->subjectRepository->findAll();
		$this->view->assign('subjects', $subjects);
		$this->view->assign('quizCategory', $quizCategory);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function editExerciseSkillAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill) {
		$subjects = $this->subjectRepository->findAll();
		$categories = $this->exerciseSkillCategoryRepository->findAllSortByCategory('ASC');
		$this->view->assign('subjects', $subjects);
		$this->view->assign('categories', $categories);
		$this->view->assign('exerciseSkill', $exerciseSkill);
	}

	/**
	 * A place where the editors can type in data so we dont have to.
	 *
	 */
	public function editExerciseSkillCategoryAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory) {
		$subjects = $this->subjectRepository->findAll();
		$this->view->assign('subjects', $subjects);
		$this->view->assign('exerciseSkillCategory', $exerciseSkillCategory);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function addQuizTypeAction(\_OurBrand_\Quiz\Domain\Model\QuizType $quizType) {
		$this->quizTypeRepository->add($quizType);
		$this->redirect('newQuizType');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function addQuizCategoryAction(\_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory) {
		$this->quizCategoryRepository->add($quizCategory);
		$this->redirect('newQuizCategory');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill
	 */
	public function addExerciseSkillAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill) {
		$this->exerciseSkillRepository->add($exerciseSkill);
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function addExerciseSkillCategoryAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory) {
		$this->exerciseSkillCategoryRepository->add($exerciseSkillCategory);
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function updateQuizTypeAction(\_OurBrand_\Quiz\Domain\Model\QuizType $quizType) {
		$this->quizTypeRepository->update($quizType);
		$this->redirect('newQuizType');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function updateQuizCategoryAction(\_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory) {
		$this->quizCategoryRepository->update($quizCategory);
		$this->redirect('newQuizCategory');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill
	 */
	public function updateExerciseSkillAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill) {
		$this->exerciseSkillRepository->update($exerciseSkill);
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function updateExerciseSkillCategoryAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory) {
		$this->exerciseSkillCategoryRepository->update($exerciseSkillCategory);
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill
	 */
	public function removeExerciseSkillAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill) {
		$this->exerciseSkillRepository->remove($exerciseSkill);
		$this->persistenceManager->persistAll();
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function removeExerciseSkillCategoryAction(\_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory) {
		$this->exerciseSkillCategoryRepository->remove($exerciseSkillCategory);
		$this->persistenceManager->persistAll();
		$this->redirect('newSkill');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function removeQuizCategoryAction(\_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory) {
		$this->quizCategoryRepository->remove($quizCategory);
		$this->persistenceManager->persistAll();
		$this->redirect('newQuizCategory');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function removeQuizTypeAction(\_OurBrand_\Quiz\Domain\Model\QuizType $quizType) {
		$this->quizTypeRepository->remove($quizType);
		$this->persistenceManager->persistAll();
		$this->redirect('newQuizType');
	}
}

