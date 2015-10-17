<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

interface ExerciseInterface {

	/**
	 * Calculate the score for the given answers.
	 *
	 * @param array $answers
	 * @return int $score
	 */
	public function calculateScoreForAnswers($answers);

	/**
	 * Find out if the [Student] has completed the exercise
	 * (aka. filled out enough for an score to be generated)
	 *
	 * @param array $answers
	 * @return int
	 */
	public function isCompleted($answers);

	/**
	 * Check if all required fields are filled out by the [Instructor]
	 *
	 * Note: This function should always call getExerciseReadyForCompletion()
	 * to validate the extended Exercise
	 *
	 * @return int $ready (0 = not ready, 1 = ready for completion, 2 = completed)
	 */
	public function getReadyForCompletion();


	/**
	 * Must implement the functionality of php standard __clone method,
	 * that is any objects should be copied and re-referenced.
	 * ImageResource and FileResource must NOT be php cloned.
	 * Use ->copy() instead.
	 *
	 * @return void
	 */
	 public function postClone();

}