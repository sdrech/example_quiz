<?php
namespace _OurBrand_\Quiz\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;

/**
 * @Flow\Entity
 */
class SpellingAndGrammarCommaQuestion {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercise
	 * @ORM\ManyToOne
	 */
	protected $exercise;

	/**
	 * @var string
	 */
	protected $questionWithComma = '';

	/**
	 * @var string
	 */
	protected $questionWithoutComma = '';

	/**
	 * @var int
	 */
	protected $number = 0;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function setExercise($exercise) {
		$this->exercise = $exercise;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Exercise
	 */
	public function getExercise() {
		return $this->exercise;
	}

	/**
	 * @param string $questionWithComma
	 */
	public function setQuestionWithComma($questionWithComma) {
		$this->questionWithComma = trim($questionWithComma);
	}

	/**
	 * @return string
	 */
	public function getQuestionWithComma() {
		return $this->questionWithComma;
	}

	/**
	 * @param string $questionWithoutComma
	 */
	public function setQuestionWithoutComma($questionWithoutComma) {
		$this->questionWithoutComma = trim($questionWithoutComma);
	}

	/**
	 * @return string
	 */
	public function getQuestionWithoutComma() {
		return $this->questionWithoutComma;
	}

	/**
	 * @param mixed $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @return mixed
	 */
	public function getNumber() {
		return $this->number;
	}
}
?>
