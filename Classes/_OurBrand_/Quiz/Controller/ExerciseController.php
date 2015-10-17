<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \_OurBrand_\Quiz\Domain\Model\Quiz;
use TYPO3\Flow\Mvc\Exception\ForwardException;
use TYPO3\Flow\Security\Exception\AccessDeniedException;

class ExerciseController extends AbstractController {


	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var \_OurBrand_\Quiz\Service\ExerciseService
	 * @Flow\Inject
	 */
	protected $exerciseService;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\inject
	 */
	protected $quizRepository;


	/**
	 * Creates fake Quiz and exercise in it
	 * @param string $exerciseName Exercise's class name
	 */
	public function createForPortalAction($exerciseName) {

		$quiz = new \_OurBrand_\Quiz\Domain\Model\Quiz();

		if (!$this->accessHelper->canUserCreateQuiz($this->currentUser, $quiz->getType())) {
			$this->throwStatus(403);
		}

		$quiz->setCreator($this->currentUser->getIdentifier());
		$quiz->setAuthor($this->currentUser->getName());
		$quiz->setPropertiesForPortalQuizWhenCreatesTheQuiz();
		$this->quizRepository->add($quiz);

		$exerciseType = null;
		$exerciseClassName = '\_OurBrand_\Quiz\Domain\Model\Exercises\\' . $exerciseName;

		if (class_exists($exerciseClassName)) {
			$newExercise = $this->objectManager->get($exerciseClassName);
		} else {
			throw new \InvalidArgumentException('Model Class not defined: ' . $exerciseName);
		}

		$quiz->addExercise($newExercise);
		$quiz->touch();

		$newExercise->setType($exerciseType);
		$newExercise->setTitle('');

		$this->quizRepository->update($quiz);
		$this->persistenceManager->persistAll();

		$this->redirect('editForPortal', 'exercise', null, array('exercise' => $newExercise));
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $parentQuiz
	 * @param string $objectName
	 * @throws \InvalidArgumentException
	 */
	public function createAction($parentQuiz, $objectName) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $parentQuiz)) {
			$this->throwStatus(403);
		}


		$exerciseType = null;
		$exerciseClassName = '\_OurBrand_\Quiz\Domain\Model\Exercises\\'.$objectName;

		if (class_exists($exerciseClassName)){
			$newExercise = $this->objectManager->get($exerciseClassName);

		}else{
			throw new \InvalidArgumentException('Model Class not defined: '.$objectName);
		}

		$parentQuiz->addExercise($newExercise);
		$parentQuiz->touch();

		$newExercise->setType($exerciseType);
		$newExercise->setTitle('');

		$this->quizRepository->update($parentQuiz);

		$this->redirect('edit', 'exercise', null, array('exercise' => $newExercise));
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @throws \TYPO3\Flow\Security\Exception\AccessDeniedException
	 * @throws ForwardException
	 */
	public function editAction($exercise) {
		$this->forwardForActions($exercise, 'edit');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @throws \TYPO3\Flow\Security\Exception\AccessDeniedException
	 * @throws ForwardException
	 */
	public function editForPortalAction($exercise) {
		$this->forwardForActions($exercise, 'editForPortal');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function updateExercise($exercise) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $exercise->getQuiz())) {
			$this->throwStatus(403);
		}

		$this->exerciseService->updateExercise($exercise);

		if ($this->request->hasArgument('json')) {
			$this->view->assign('value', array(
					'message' => 'ok',
					'object' => $exercise,
					'readyForCompletion' => $exercise->getQuiz()->getReadyForCompletion()
				));
		} else {
			$this->redirect('edit', null, null, array('exercise' => $exercise));
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function updateSilentAction($exercise) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $exercise->getQuiz())) {
			$this->throwStatus(403);
		}

		$this->exerciseService->updateExercise($exercise);

		if ($this->request->hasArgument('json')){
			$exerciseId = $this->persistenceManager->getIdentifierByObject($exercise);
			$newTitle = $exercise->getTitle();
			if (empty($newTitle)) {
				$newTitle = $this->translateById('quiz.untitled');
			}
			$quiz = $exercise->getQuiz();

			$exercises = array();
			foreach ($quiz->getExercises() as $exercise) {
				$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
				$exercises[$identifier] = $exercise->getReadyForCompletion();
			}

			$this->view->assign('value', array(
					'message' => 'ok',
					'identifier' => $exerciseId,
					'title' => $newTitle,
					'readyForCompletion' => $quiz->getReadyForCompletion(),
					'exercises' => $exercises)
			);
		}else{
			$this->redirect('edit', null, null, array('exercise' => $exercise));
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 */
	public function updateAction(\_OurBrand_\Quiz\Domain\Model\Exercise $exercise) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $exercise->getQuiz())) {
			$this->throwStatus(403);
		}

		$this->exerciseService->updateExercise($exercise);

		$arguments = $this->request->getArguments();

		if (isset($arguments['noUpdate'])==false){
			$this->exerciseRepository->update($exercise);
		}

		if (isset($arguments['noRedirect'])==false){
			$this->redirect('edit', null, null, array('exercise' => $exercise));
		}
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function deleteAction($exercise) {

		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $exercise->getQuiz())) {
			$this->throwStatus(403);
		}

		$number = $exercise->getNumber();
		$quiz = $exercise->getQuiz();

		$prevExercise = null;

		$quiz->removeExercise($exercise);
		$quiz->touch();

		if ($quiz->getExercises()->count() > 0){
			$prevExercise = $quiz->getExerciseByNumber($number > 0 ? $number - 1 : 0);
		}

		$this->quizRepository->update($quiz);
		$this->persistenceManager->persistAll();

		if ($prevExercise){
			$this->redirect('edit', 'exercise', null, array('exercise' => $prevExercise));
		}else{
			$this->redirect('edit', 'quiz', null, array('quiz' => $quiz));
		}
	}

	/**
	 * Shown to student when student is taking test. Redirects to correct controller
	 * according to type of exercise.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @throws \TYPO3\Flow\Mvc\Exception\ForwardException
	 * @throws AccessDeniedException
	 */
	public function showAction($exercise) {

		// There must be a studentQuizSession in current login session!
		if (!$this->studentQuizSession){
			$this->redirect('studentindex', 'quiz');
		}

		if ($this->studentQuizSession->getQuizSession()->getQuiz() != $exercise->getQuiz()){
			$this->throwStatus(403);
		}

		$useController = $this->getControllerNameForExercise($exercise);

		if ($useController !== false){
			$this->forward('show', $useController, null, array('exercise' => $exercise));
		}else{
			throw new ForwardException('Can not forward to correct controller for object of type '.get_class($exercise));
		}
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @throws \TYPO3\Flow\Mvc\Exception\ForwardException
	 * @throws \TYPO3\Flow\Security\Exception\AccessDeniedException
	 */
	public function previewAction($exercise) {
		$this->forwardForActions($exercise, 'preview');
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @throws \TYPO3\Flow\Mvc\Exception\ForwardException
	 * @throws \TYPO3\Flow\Security\Exception\AccessDeniedException
	 */
	public function previewForPortalAction($exercise) {
		$this->forwardForActions($exercise, 'previewForPortal');
	}

	/**
	 * Base function for previewAction/editAction, which makes forward on necessary action and controller
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param string $action
	 * @throws \TYPO3\Flow\Mvc\Exception\ForwardException
	 */
	private function forwardForActions($exercise, $action) {
		if (!$this->accessHelper->canUserEditQuiz($this->currentUser, $exercise->getQuiz())) {
			$this->throwStatus(403);
		}

		$useController = $this->getControllerNameForExercise($exercise);

		if ($useController !== false) {
			$this->forward($action, $useController, null, array('exercise' => $exercise));
		} else {
			throw new ForwardException('Can not forward to correct controller for object of type '.get_class($exercise));
		}
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @throws ForwardException
	 * @throws AccessDeniedException
	 */
	public function reviewAction($exercise, $studentQuizSession) {

		if ($studentQuizSession->getQuizSession()->getQuiz() != $exercise->getQuiz()){
			$this->throwStatus(403);
		}
		// TODO: Access. Can only be viewed by student or responsible instructor.

		$useController = $this->getControllerNameForExercise($exercise);

		if ($useController !== false){
			$this->forward('review', $useController, null, array('exercise' => $exercise, 'studentQuizSession' => $studentQuizSession));
		}else{
			throw new ForwardException('Can not forward to correct controller for object of type '.get_class($exercise));
		}

	}


	/**
	 * Returns working controller name as string
	 * or false if no controller could be found.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return mixed
	 */
	protected function getControllerNameForExercise($exercise) {
		$pageClass = get_class($exercise);

		$useControllerAnnotation = $this->reflectionService->getClassAnnotation($pageClass, '_OurBrand_\Quiz\Annotations\ExerciseUseController');
		$useController= $useControllerAnnotation->controllerName;
		$useControllerClassName = '\_OurBrand_\Quiz\Controller\\'.$useController.'Controller';

		if (class_exists($useControllerClassName)
			&& $this->reflectionService->isClassImplementationOf(
				$useControllerClassName,
				'\_OurBrand_\Quiz\Controller\ExerciseControllerInterface')
		) {
			return $useController;
		}
		return false;
	}
}

