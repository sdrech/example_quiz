<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \_OurBrand_\Quiz\Utility\Utility;


class MatchPictureInPictureExerciseController extends AbstractController implements ExerciseControllerInterface {


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function editAction($exercise) {
		$this->editAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function editForPortalAction($exercise) {
		$this->editAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	private function editAssignment($exercise) {
		$exercise->setMediaContent('picture', false); // remove the standard add picture link in the top of the exercise

		$this->view->assign('mainImage', $exercise->getMainImage());
		$this->view->assign('existingShapes', $exercise->getShapesAsJson());
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function updateAction($exercise) {

		if ($this->request->hasArgument('draggableShapeData')) {

			$shapes = array();

			foreach ($this->request->getArgument('draggableShapeData') as $shape) {

				// Insert the image resource in the array
				$shape['imageObj'] = $this->persistenceManager->getObjectByIdentifier(
					$shape['uuid'],
					'\_OurBrand_\Quiz\Domain\Model\ImageResource'
				);

				$shapes[] = $shape;
			}

			$exercise->addShapesFromArray($shapes);
		}

		if ($this->request->hasArgument('mainImage')) {

			$imageObj = $this->persistenceManager->getObjectByIdentifier(
				$this->request->getArgument('mainImage'),
				'\_OurBrand_\Quiz\Domain\Model\ImageResource'
			);

			if ($imageObj !== null) {
				$exercise->addMainImage($imageObj);
			}

		}

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
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function previewAction($exercise) {
		$this->previewAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function previewForPortalAction($exercise) {
		$this->previewAssignment($exercise);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	private function previewAssignment($exercise) {
		$shapes = $exercise->getRandomShapes();

		$this->view->assign('shapes', $shapes);
		$this->view->assign('solvedShapes', json_encode(array())); // there are no already answered data in preview
		$this->view->assign('explanationTranslateKey', 'exerciseType.matchPictureInPicture.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());

	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise $exercise
	 * @return void
	 */
	public function showAction($exercise) {

		// get previous saved answers
		$answers = $this->answerRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answers, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$answerData = unserialize($answers->getAnswerDatas()->first()->getData());
		}

		$shapes = $exercise->getRandomShapes();

		$this->view->assign('shapes', $shapes);
		$this->view->assign('solvedShapes', json_encode($answerData));
		$this->view->assign('explanationTranslateKey', 'exerciseType.matchPictureInPicture.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $this->studentQuizSession);

	}


	/**
	 * Register answer.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function registerAnswerAction($exercise) {

		$answers = $this->request->hasArgument('solvedShapes') ? $this->request->getArgument('solvedShapes') : array();
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

	/**
	 * Review answers.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function reviewAction($exercise, $studentQuizSession) {

		$randomShapes = $exercise->getRandomShapes();

		$utility = new Utility();

		// Create array with correctAnswers in same format as $answerData.
		$correctAnswers = array();
		foreach ($randomShapes as $shape) {
			$correctAnswers[$utility->getSaltedString(
				$this->persistenceManager->getIdentifierByObject($shape)
			)] = $this->persistenceManager->getIdentifierByObject($shape);
		}

		// get previous saved answers
		$answers = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise)->getFirst();

		$answerData = array();
		if (is_a($answers, '\_OurBrand_\Quiz\Domain\Model\Answer')) {
			$answerData = unserialize($answers->getAnswerDatas()->first()->getData());

			$userAnswersStatus = array(
				'correct' => array(),
				'false' => array()
			);

			// save the correct answer here as well
			foreach ($answerData as $saltedUuid => $uuid) {
				if (isset($correctAnswers[$saltedUuid]) && $correctAnswers[$saltedUuid] === $uuid) {
					$userAnswersStatus['correct'][] = $uuid;
				}
				else {
					$userAnswersStatus['false'][] = $uuid;
				}

			}

		}

		$this->view->assign('shapes', $randomShapes);
		$this->view->assign('userAnswers', json_encode($answerData));
		$this->view->assign('userAnswersStatus', json_encode($userAnswersStatus));
		$this->view->assign('correctAnswers', json_encode($correctAnswers));


		$this->view->assign('explanationTranslateKey', 'exerciseType.matchPictureInPicture.explanation');
		$this->view->assign('currentExercise', $exercise);
		$this->view->assign('quiz', $exercise->getQuiz());
		$this->view->assign('session', $studentQuizSession);
	}
}
