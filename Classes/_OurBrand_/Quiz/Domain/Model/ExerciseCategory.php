<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;


/**
 * @Flow\Entity
 */
class ExerciseCategory {

	/**
	 * @var int
	 */
	protected $sortorder;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $exerciseTypes = array();

	/**
	 * @param string $name
	 * @param int $sortorder
	 */
	public function __construct($name, $sortorder = 0) {
		$this->name = $name;
		$this->sortorder = $sortorder;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getSortorder() {
		return $this->sortorder;
	}

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ExerciseType
	 */
	public function addExerciseType($exerciseType) {
		$this->exerciseTypes[] = $exerciseType;
	}

	/**
	 * @return array
	 */
	public function getExerciseTypes() {
		return $this->exerciseTypes;
	}

}
?>