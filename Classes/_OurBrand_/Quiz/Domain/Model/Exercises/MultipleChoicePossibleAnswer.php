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
class MultipleChoicePossibleAnswer {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion
	 * @ORM\ManyToOne
	 */
	protected $question;

	/**
	 * @var bool
	 */
	protected $isCorrectAnswer = FALSE;

	/**
	 * @var int
	 */
	protected $number = 0;


	/**
	 * @var string
	 */
	protected $text = '';
	
	
	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @ORM\ManyToOne(cascade={"persist"})
	 */
	protected $image;


	public function __construct(){

	}



	/**
	 * @param int $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @return int
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @param boolean $isCorrectAnswer
	 */
	public function setIsCorrectAnswer($isCorrectAnswer) {
		$this->isCorrectAnswer = $isCorrectAnswer;
	}

	/**
	 * @return boolean
	 */
	public function getIsCorrectAnswer() {
		return $this->isCorrectAnswer;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function setQuestion($question) {
		$this->question = $question;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion
	 */
	public function getQuestion() {
		return $this->question;
	}
	
	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Implementation of clone magic method.
	 */
	public function postClone() {
		if (!is_null($this->image)) $this->image = $this->image->copy();
	}
}
?>
