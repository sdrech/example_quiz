<?php
namespace _OurBrand_\Quiz\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use _OurBrand_\Quiz\Domain\Model\ExerciseCategory;
use _OurBrand_\Quiz\Domain\Model\ExerciseType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class ExerciseCategoryRepository extends Repository {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param $settings
	 */
	public function injectSettings($settings) {
		$this->settings = $settings;
	}


	/**
	 * @return array
	 */
	public function findAll() {

		$categories = array();
		foreach($this->settings['exercises'] as $exerciseCategoryName => $exerciseTypes) {
			if(!is_array($exerciseTypes)) {
				continue;
			}
			$exerciseCategory = new ExerciseCategory($exerciseCategoryName);
			foreach($exerciseTypes as $exerciseTypeData) {
				$exerciseType = new ExerciseType($exerciseCategory, 'exerciseType.'.$exerciseTypeData['key'].'.title', 'exerciseType.'.$exerciseTypeData['key'].'.description', $exerciseTypeData['class'], $exerciseTypeData['image']);
				$exerciseCategory->addExerciseType($exerciseType);
			}
			$categories[] = $exerciseCategory;
		}
		return $categories;
	}

}
?>