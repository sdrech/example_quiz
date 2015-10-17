<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use _OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer;
use _OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion;
use TYPO3\Flow\Annotations as Flow;

class MultipleChoiceSameAnswerExerciseController extends AbstractController implements ExerciseControllerInterface
{


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	public function editAction($exercise) {
		$this->editAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	public function editForPortalAction($exercise) {
		$this->editAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	private function editAssignment($exercise){
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());

		// Get possible answers from persistence if questions exist.
		if ($exercise->getQuestions()->count()) {
			$firstQuestion = $exercise->getQuestions()->first();
			if ($firstQuestion->getPossibleAnswers()->count() && $possibleAnswers = $firstQuestion->getPossibleAnswers(
				)
			) {
				$ret = array();
				foreach ($possibleAnswers as $pa) {
					$ret[] = $pa->getText();
				}
				$this->view->assign('possibleAnswers', $ret);
			}
		} else {
			$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
			$this->view->assign('possibleAnswers', $this->loginSession->getData('possibleAnswers-' . $identifier));
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	public function updateAction($exercise) {
		$possibleAnswers = $correctAnswers = $toSetQuestions = array();

		if ($this->request->hasArgument('possibleAnswers')) {
			$possibleAnswers = $this->request->getArgument('possibleAnswers');
		}

		if ($this->request->hasArgument('questions')) {
			$newQuestions = $this->request->getArgument('questions');
			if (!is_array($newQuestions)) {
				$newQuestions = array($newQuestions);
			}

			if ($this->request->hasArgument('correctAnswer')) {
				$correctAnswers = $this->request->getArgument('correctAnswer');
			}

			foreach ($newQuestions as $i => $newQuestionText) {
				$question = array(
					'text' => $newQuestionText,
					'hint' => '',
				);
				foreach ($possibleAnswers as $k => $possibleAnswerText) {
					$possibleAnswer = array(
						'text' => $possibleAnswerText,
						'correctAnswer' => 0
					);
					if (isset($correctAnswers[$i]) && $correctAnswers[$i] == $k) {
						$possibleAnswer['correctAnswer'] = 1;
					}
					$question['possibleAnswers'][] = $possibleAnswer;
				}
				$toSetQuestions[] = $question;
			}

			$exercise->setQuestionsFromArray($toSetQuestions);
		}

		// The possible answers are not saved individually, but only with questions.
		// So if no questions have been saved, we would like to
		// save the possible answers anyways.
		$identifier = $this->persistenceManager->getIdentifierByObject($exercise);
		$this->loginSession->setData('possibleAnswers-' . $identifier, $possibleAnswers);

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
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $newQuestion
	 */
	public function createQuestionAction($exercise, $newQuestion) {
		$exercise->addQuestion($newQuestion);
		$this->exerciseRepository->update($exercise);
		$this->redirect('edit', null, null, array('exercise' => $exercise));
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function updateQuestionAction($exercise, $question) {
		$exercise->updateQuestion($question);
		$this->exerciseRepository->update($exercise);
		$this->redirect('edit', null, null, array('exercise' => $exercise));
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function deleteQuestionAction($exercise, $question) {
		$exercise->removeQuestion($question);
		$this->exerciseRepository->update($exercise);
		$this->redirect('edit', null, null, array('exercise' => $exercise));
	}


	/**
	 * Preview for exercise.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function previewAction($exercise) {
		$this->previewAssignment($exercise);
	}


	/**
	 * Preview for portal exercise.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function previewForPortalAction($exercise) {
		$this->previewAssignment($exercise);
	}


	/**
	 * Main preview by creator of exercise.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	private function previewAssignment($exercise) {
		$possibleAnswers = null;
		$question = $exercise->getQuestions()->first();
		if (is_a($question, '\_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion')) {
			$possibleAnswers = $question->getPossibleAnswers();
		}

		// Select the explanation translation text
		$this->view->assign('explanationTranslateKey', 'exercise.multipleChoice.sameAnswer.explanation.selectOne');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('possibleAnswers', $possibleAnswers);
		$this->view->assign('quiz', $exercise->getQuiz());
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	public function showAction($exercise) {
		$answer = $this->answerRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answer, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$data = unserialize($answer->getAnswerDatas()->first()->getData());
			foreach ($data as $questionNumber => $checkedAnswer) {
				$answerData[$questionNumber] = array('chosenAnswer' => $checkedAnswer);
			}
		}

		// jens : rewritten, this could fail otherwise.
		$possibleAnswers = null;
		$question = $exercise->getQuestions()->first();
		if (is_a($question, '\_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion')) {
			$possibleAnswers = $question->getPossibleAnswers();
		}

		// Select the explanation translation text
		$this->view->assign('explanationTranslateKey', 'exercise.multipleChoice.sameAnswer.explanation.selectOne');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('possibleAnswers', $possibleAnswers);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);
		$this->view->assign('answerData', $answerData);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @throws \Exception
	 */
	public function reviewAction($exercise, $studentQuizSession) {

		$answer = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise)->getFirst();
		$answerData = array();
		$results = array();
		if (is_a($answer, '\_OurBrand_\Quiz\Domain\Model\Answer')) {

			$data = unserialize($answer->getAnswerDatas()->last()->getData());


			$questions = $exercise->getQuestions();
			if ($questions) {
				$qCount = 0;
				foreach ($questions as $question) {
					if ($question) {
						$results[$qCount] = array(
							'text' => $question->getText(),
						);

						$possibleAnswers = $question->getPossibleAnswers();
						$pCount = 0;
						foreach ($possibleAnswers as $possibleAnswer) {
							if (is_a($possibleAnswer,'\_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer')) {
								$checkedAnswer = isset($data[$qCount . '_' . $pCount]) ? $data[$qCount . '_' . $pCount] : null;

								// Jens : rewritten do possible error ( prob. only gonna happen in dev. )
								if (is_numeric($checkedAnswer)) {
									$checkedAnswer = intval($checkedAnswer);
								} elseif (is_string($checkedAnswer)) {
									$checkedAnswer = strtolower(trim($checkedAnswer)) == 'on' ? $qCount . '_' . $pCount : 0;
								}

								$results[$qCount]['posibleAnswers'][] = array(
									'chosenAnswer' => $checkedAnswer == $qCount . '_' . $pCount ? 1 : 0,
									'correct' => ($possibleAnswer->getIsCorrectAnswer() ? 1 : 0),
									'success' => ($possibleAnswer->getIsCorrectAnswer(
										) && $checkedAnswer == $qCount . '_' . $pCount ? 1 : 0),
								);
							}
							$pCount++;
						}
					}
					$qCount++;
				}
			}
		}

		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('possibleAnswers', $exercise->getQuestions()->first()->getPossibleAnswers());
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('answerData', $answerData);
		$this->view->assign('results', $results);
	}

	/**
	 * Persist given answer(s) for the exercise by current StudentQuizSession.
	 * Each correct answer gives a point. Certain questions can give multiple points.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise $exercise
	 */
	public function registerAnswerAction($exercise) {

		$answers = $this->request->hasArgument('question') ? $this->request->getArgument('question') : array();
		$score = $exercise->calculateScoreForAnswers($answers);
		$status = $exercise->isCompleted($answers);

		// Save the score, for the given answers, on this exercise, during this quiz.
		$this->exerciseService->registerAnswer($score, $status, $answers, $exercise, $this->studentQuizSession);
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

}

?>