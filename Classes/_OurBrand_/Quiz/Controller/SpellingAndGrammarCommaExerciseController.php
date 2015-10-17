<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


class SpellingAndGrammarCommaExerciseController extends AbstractController implements ExerciseControllerInterface {


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	public function editAction($exercise) {
		$this->editAssignment($exercise);
	}



	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	public function editForPortalAction($exercise) {
		$this->editAssignment($exercise);
	}


	/**
	 * Main functionality for Edit Actions
	 * 	
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	private function editAssignment($exercise) {
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	public function updateAction($exercise) {
		$questions = $questionsWithComma = $questionsWithoutComma = array();
		if ($this->request->hasArgument('questionWithComma')) {
			$questionsWithComma = $this->request->getArgument('questionWithComma');
		}
		if ($this->request->hasArgument('questionWithoutComma')) {
			$questionsWithoutComma = $this->request->getArgument('questionWithoutComma');
		}

		foreach ($questionsWithComma as $i => $questionText) {
			$questions[] = array(
				'questionWithComma' => $questionText,
				'questionWithoutComma' => $questionsWithoutComma[$i]
			);
		}

		$exercise->setQuestionsFromArray($questions);

		if ($this->request->hasArgument('quiz')) {
			$this->updateQuizBeforeUpdatingExercise($exercise->getQuiz(), $this->request->getArgument('quiz'));
		}

		if (!$this->request->hasArgument('json')) {
			$this->forward('update', 'exercise', null, array('exercise' => $exercise));
		}

		$this->forward(
			'updateSilent',
			'exercise',
			null,
			array('exercise' => $exercise, 'json' => $this->request->getArgument('json'))
		);
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	public function showAction($exercise) {
		// get previous saved answers
		$answers = $this->answerRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answers, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$data = unserialize($answers->getAnswerDatas()->first()->getData());
			$mode = $data['mode'];
			foreach ($data['answers'] as $questionNumber => $checkedAnswer) {
				$answerData[$questionNumber] = $checkedAnswer;
			}
		} else {
			$mode = 'withComma';
			$answerData = $exercise->getAnswers();
		}

		$this->view->assign('explanationTranslateKey', 'exercise.SpellingAndGrammar.Comma.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('mode', $mode);
		$this->view->assign('answers', $answerData);
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
	 * Preview the exercise as Instructor
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function previewForPortalAction($exercise) {
		$this->previewAssignment($exercise);
	}


	/**
	 * Preview the exercise as Instructor
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	private function previewAssignment($exercise) {
		$mode = 'withComma';
		$answerData = $exercise->getAnswers();

		$this->view->assign('explanationTranslateKey', 'exercise.SpellingAndGrammar.Comma.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('mode', $mode);
		$this->view->assign('answers', $answerData);
	}


	/**
	 * Register answer.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise $exercise
	 * @return void
	 */
	public function registerAnswerAction($exercise) {
		$answers = $this->request->hasArgument('answers') ? $this->request->getArgument('answers') : array();
		$mode = $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : '';
		$answer = array('answers' => $answers, 'mode' => $mode);
		$score = $exercise->calculateScoreForAnswers($answer);
		$status = $exercise->isCompleted($answer);

		// Save the score, for the given answers, on this exercise, during this quiz.
		$this->exerciseService->registerAnswer($score, $status, $answer, $exercise, $this->studentQuizSession);
		$this->studentQuizSessionService->updateProgress($this->studentQuizSession);

		$this->view->assign('value', array('timeRemaining' => $this->studentQuizSession->getTimeRemaining()));

		if (!$this->isJson) {
			$this->redirect(
				'studentnavigate',
				'quiz',
				$this->request->hasArgument('goto') ? $this->request->getArgument('goto') : 'next'
			);
		}
	}


	/**
	 * Review answers.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @return void
	 */
	public function reviewAction($exercise, $studentQuizSession) {
		// get previous saved answers
		$answers = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answers, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$data = unserialize($answers->getAnswerDatas()->first()->getData());
			$mode = $data['mode'];
			$questions = $exercise->getQuestions();
			foreach ($data['answers'] as $questionNumber => $answer) {
				foreach ($questions as $question) {
					if ($question->getNumber() === $questionNumber) {
						switch ($mode) {
							case 'withoutComma':
								$correct = $question->getQuestionWithoutComma();
								break;
							case 'withComma':
								$correct = $question->getQuestionWithComma();
								break;
						}
					}
				}

				if (strpos($correct, ',') === strpos($answer, ',')) {
					$answer = str_replace(
						',',
						'{{success_box}}',
						$answer
					);
				} else {
					// Mark the spot on the answer where the WRONG comma is placed with {{a}}
					$answerArray = explode(" ", $answer);
					foreach ($answerArray as $index => $word) {
						if (strpos($word, ',') !== false) {
							$answerArray[$index] = str_replace(',', '{{a}}', $word);
						}
					}

					// Mark the spot on the answer where the CORRECT comma must be placed with {{c}}
					$correctArray = explode(" ", $correct);
					foreach ($correctArray as $index => $word) {
						if (strpos($word, ',') !== false) {
							$answerArray[$index] = str_replace(',', '{{c}}', $word);
						}
					}
					// Replace placeholders
					$marks = array("{{a}}", "{{c}}");
					$replacements = array(
						'{{error_box}}',
						'{{success_box}}'
					);
					$answer = str_replace($marks, $replacements, implode(" ", $answerArray));
				}

				$answerData[$questionNumber] = $answer;
			}
		}


		$this->view->assign('explanationTranslateKey', 'exercise.SpellingAndGrammar.Comma.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('mode', $mode);
		$this->view->assign('answers', $answerData);
	}
}

?>