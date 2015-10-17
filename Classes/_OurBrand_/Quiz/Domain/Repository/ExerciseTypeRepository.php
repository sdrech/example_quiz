<?php
namespace _OurBrand_\Quiz\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use _OurBrand_\Quiz\Domain\Model\ExerciseCategory;
use _OurBrand_\Quiz\Domain\Model\ExerciseType;
/**
 * @Flow\Scope("singleton")
 */
class ExerciseTypeRepository {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array $settings
	 */
	public function injectSettings($settings) {
		$this->settings = $settings;
	}


	/**
	 * @var string $objectName
	 * @return \_OurBrand_\Quiz\Domain\Model\ExerciseType|null
	 */
	public function findOneByObjectName($objectName) {

		$categories = array();
		$correctExerciseType = null;
		foreach($this->settings['exercises'] as $exerciseCategoryName => $exerciseTypes) {
			if(!is_array($exerciseTypes)) {
				continue;
			}
			$exerciseCategory = new ExerciseCategory($exerciseCategoryName);
			foreach($exerciseTypes as $exerciseTypeData) {
				$exerciseType = new ExerciseType($exerciseCategory, 'exerciseType.'.$exerciseTypeData['key'].'.title', 'exerciseType.'.$exerciseTypeData['key'].'.description', $exerciseTypeData['class'], $exerciseTypeData['image']);
				$exerciseCategory->addExerciseType($exerciseType);
				if($objectName == $exerciseTypeData['class']) {
					$correctExerciseType = $exerciseType;
				}
			}
			$categories[] = $exerciseCategory;
		}
		return $correctExerciseType;
	}



}
