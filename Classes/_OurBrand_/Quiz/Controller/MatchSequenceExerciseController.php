<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


class MatchSequenceExerciseController extends AbstractController implements ExerciseControllerInterface {

	/**
	 * @see ExerciseControllerInterface::editAction();
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @return void
	 */
	public function editAction($exercise) {
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
	}

	/**
	 * @see ExerciseControllerInterface::updateAction();
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @return void
	 */
	public function updateAction($exercise) {
		$phrases = array();
		if ($this->request->hasArgument('phrase')) {
			$phrases = $this->request->getArgument('phrase');
		}
		$exercise->setPhrasesFromArray($phrases);
		$exercise->randomize();

		if(!$this->request->hasArgument('json')) {
			$this->forward('update', 'exercise', null, array('exercise' => $exercise));
		}
		$this->forward('updateSilent', 'exercise', null, array('exercise' => $exercise, 'json' => $this->request->getArgument('json')));
	}

	/**
	 * @see ExerciseControllerInterface::previewAction();
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @return void
	 */
	public function previewAction($exercise) {
		$answerData = array();
		$k = count($this->generatedAnswers($exercise, $answerData));
		$answerData = array_fill(0, $k, '');

		$this->view->assign('explanationTranslateKey', 'exercise.match.sequence.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('answerData', $answerData);
		$this->view->assign('answers', $this->generatedAnswers($exercise, $answerData));
	}

	/**
	 * @see ExerciseControllerInterface::showAction();
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
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
		} else {
			$k = count($this->generatedAnswers($exercise, $answerData));
			$answerData = array_fill(0, $k, '');
		}

		$this->view->assign('explanationTranslateKey', 'exercise.match.sequence.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('answerData', $answerData);
		$this->view->assign('answers', $this->generatedAnswers($exercise, $answerData));
	}


	/**
	 * Generate answers for exercise: in initial order or in previously saved in session order
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @param array $answerData Previous answered questions
	 * @return array
	 */
	protected function generatedAnswers($exercise, $answerData = array()) {
		$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
		$answers = $this->loginSession->getData('answers-' . $identifier);
		if (is_null($answers)) {
			$answers = $exercise->getPreorderedAnswers();
		}

		$parsedAnswers = array();
		foreach ($answers as $value) {
			if (!in_array($value, $answerData)) {
				$parsedAnswers[] = $value;
			}
		}

		$this->loginSession->setData('answers-' . $identifier, $parsedAnswers);

		return $parsedAnswers;
	}

	/**
	 * @see ExerciseControllerInterface::registerAnswerAction();
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @return void
	 */
	public function registerAnswerAction($exercise) {
		$answers = $this->request->hasArgument('answeredPhrases') ? $this->request->getArgument('answeredPhrases') : array();
		$score = $exercise->calculateScoreForAnswers($answers);
		$status = $exercise->isCompleted($answers);

		$orderedAnswers = $this->request->hasArgument('basePhrases') ? $this->request->getArgument('basePhrases') : array();
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

	/**
	 * @see ExerciseControllerInterface::reviewAction();
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @return void
	 */
	public function reviewAction($exercise, $studentQuizSession) {
		// get previous saved answers
		$answer = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answer, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$answerData = unserialize($answer->getAnswerDatas()->first()->getData());
		} else {
			$k = count($this->generatedAnswers($exercise, $answerData));
			$answerData = array_fill(0, $k, '');
		}

		if ($exercise->calculateScoreForAnswers($answerData) == $exercise->getMaxScore()) {
			$this->view->assign('isPerfectlyMade', 1);
		} else {
			$this->view->assign('isPerfectlyMade', 0);
		}
		$this->view->assign('explanationTranslateKey', 'exercise.match.sequence.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('answerData', $answerData);
		$this->view->assign('allAnswers', $exercise->getPhrases());
		$this->view->assign('answers', $this->generatedAnswers($exercise, $answerData));
	}
}
?>