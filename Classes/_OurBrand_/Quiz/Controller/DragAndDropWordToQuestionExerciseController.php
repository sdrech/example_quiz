<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \_OurBrand_\Quiz\Domain\Model\Quiz;


class DragAndDropWordToQuestionExerciseController extends AbstractController implements ExerciseControllerInterface {


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
	 * @return void
	 */
	public function editAction($exercise) {
		$this->editAssignment($exercise);
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
	 * @return void
	 */
	public function editForPortalAction($exercise) {
		$this->editAssignment($exercise);
	}


	/**
	 * Main functionality for Edit Actions 
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
	 * @return void
	 */
	private function editAssignment($exercise) {
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
	 * @return void
	 */
	public function updateAction($exercise) {
		$newQuestions = $answers = $extraWords = array();
		if ($this->request->hasArgument('questions')) {
			$newQuestions = $this->request->getArgument('questions');
		}
		if ($this->request->hasArgument('answers')) {
			$answers = $this->request->getArgument('answers');
		}
		if ($this->request->hasArgument('extraWords')) {
			$extraWords = $this->request->getArgument('extraWords');
		}

		$questions = array();
		foreach ($newQuestions as $i => $questionText) {
			$obj = array(
				'question' => $questionText
			);

			$obj['answer'] = $answers[$i];
			$questions[] = $obj;
		}
		$exercise->setQuestionsFromArray($questions);
		$exercise->setExtraWordsFromArray($extraWords);
		$exercise->randomize();

		// Remove the possible session values in preview:
		$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
		$this->loginSession->setData('answers-' . $identifier, null);

		if(!$this->request->hasArgument('json')) {
			$this->forward('update', 'exercise', null, array('exercise' => $exercise));
		}
		$this->forward('updateSilent', 'exercise', null, array('exercise' => $exercise, 'json' => $this->request->getArgument('json')));
	}



	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
     * @return void
	 */
	public function showAction($exercise) {
		// get previous saved answers
		$answer = $this->answerRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answer, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$data = unserialize($answer->getAnswerDatas()->first()->getData());
			foreach ($data as $questionNumber => $checkedAnswer) {
				$answerData[$questionNumber] = $checkedAnswer;
			}
		}

		$this->view->assign('explanationTranslateKey', 'exercise.draganddrop.wordToQuestions.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('answerData', $answerData);
		$this->view->assign('answers', $this->generatedAnswers($exercise, $answerData));
	}


	/**
	 * Generate answers for exercise: in initial order or in previously saved in session order
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise $exercise
	 * @param array $answerData Previous answered questions
	 * @return array
	 */
	protected function generatedAnswers($exercise, $answerData = array()) {
		$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
		$answers = $this->loginSession->getData('answers-' . $identifier);
		if (is_null($answers)) {
			$allAnswers = $exercise->getAllAnswers();

			$answers = array();
			foreach ($allAnswers as $value) {
				if (!in_array($value->getAnswer(), $answerData)) {
					$answers[] = $value->getAnswer();
				}
			}

			$this->loginSession->setData('answers-' . $identifier, $answers);
		}

		return $answers;
	}


	/**
	 * Preview the exercise as Instructor
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function previewAction($exercise) {
		$this->previewAssignment($exercise);
	}


	/**
	 * Preview for portal the exercise as Instructor
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function previewForPortalAction($exercise) {
		$this->previewAssignment($exercise);
	}


	/**
	 * Main preview functionality
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	private function previewAssignment($exercise) {
		$this->view->assign('explanationTranslateKey', 'exercise.draganddrop.wordToQuestions.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());

		$allAnswers = $exercise->getAllAnswers();

		$answers = array();
		foreach ($allAnswers as $value) {
			$answers[] = $value->getAnswer();
		}
		$this->view->assign('answerData', array());
		$this->view->assign('answers', $answers);
	}

	/**
	 * Register answer.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
     * @return void
	 */
	public function registerAnswerAction($exercise) {
		$answers = $this->request->hasArgument('question') ? $this->request->getArgument('question') : array();
		$score = $exercise->calculateScoreForAnswers($answers);
		$status = $exercise->isCompleted($answers);

		$orderedAnswers = $this->request->hasArgument('answers') ? $this->request->getArgument('answers') : array();
		$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
		$this->loginSession->setData('answers-' . $identifier, array_unique($orderedAnswers));

		// Save the score, for the given answers, on this exercise, during this quiz.
		$this->exerciseService->registerAnswer($score, $status, $answers, $exercise, $this->studentQuizSession);
		$this->studentQuizSessionService->updateProgress($this->studentQuizSession);

		$this->view->assign('value', array('timeRemaining' => $this->studentQuizSession->getTimeRemaining()));

		if(!$this->isJson) {
			$this->redirect('studentnavigate', 'quiz', $this->request->hasArgument('goto')?$this->request->getArgument('goto'):'next');
		}
	}

}

?>