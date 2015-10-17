<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

interface ExerciseControllerInterface {


	/**
	 * [Instructor]: Display the form that allows for customization of the exercise
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function editAction($exercise);


	/**
	 * [Instructor]: Update the exercise and persist into the database
	 *
	 * Note: Always end the function with:
	 * if(!$request->hasArgument('json')) {
	 * 		$controller->forward('update', 'exercise', null, array('exercise' => $exercise));
	 * }
	 * $controller->forward('updateSilent', 'exercise', null, array(
	 * 		'exercise' => $exercise,
	 * 		'json' => $request->getArgument('json')
	 * ));
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function updateAction($exercise);


	/**
	 * [Instructor] When editing.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function previewAction($exercise);


	/**
	 * [Student] Shown to student when student is taking test.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function showAction($exercise);


	/**
	 * [Student] Persist the given answer into the database.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function registerAnswerAction($exercise);


	/**
	 * [Instructor/Student] Review given answer(s).
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function reviewAction($exercise, $studentQuizSession);


}

?>