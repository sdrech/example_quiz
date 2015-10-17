<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;


class ExerciseType {

	/**
	 * Title of the object
	 * @var string
	 */
	protected $titleKey;

	/**
	 * DescriptionKey of the object
	 * @var string
	 */
	protected $descriptionKey;

	/**
	 * Name of the controller belonging to the exercise.
	 * The createAction function of this controller when selecting this ExerciseType
	 * @var string
	 */
	protected $objectName;

	/**
	 * Link to the image belonging to the exercise (relative to UIPath)
	 * @var string
	 */
	protected $imageLink;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ExerciseCategory
	 */
	protected $exerciseCategory;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseCategory $exerciseCategory
	 * @param string $titleKey
	 * @param string $descriptionKey
	 * @param string $objectName
	 * @param string $imageLink
	 */
	public function __construct($exerciseCategory, $titleKey, $descriptionKey, $objectName, $imageLink = '') {
		$this->exerciseCategory = $exerciseCategory;
		$this->titleKey = $titleKey;
		$this->descriptionKey = $descriptionKey;
		$this->objectName = $objectName;
		$this->imageLink = $imageLink;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ExerciseCategory
	 */
	public function getExerciseCategory() {
		return $this->exerciseCategory;
	}


	/**
	 * @return string
	 */
	public function getDescriptionKey() {
		return $this->descriptionKey;
	}

	/**
	 * @return string
	 */
	public function getImageLink() {
		return $this->imageLink;
	}

	/**
	 * @return string
	 */
	public function getObjectName() {
		return $this->objectName;
	}

	/**
	 * @return string
	 */
	public function getTitleKey() {
		return $this->titleKey;
	}
}
