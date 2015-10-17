<?php
namespace _OurBrand_\Quiz\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;
use _OurBrand_\Quiz\Domain\Model\Answer;

/**
 * @Flow\Entity
 */
class MultipleChoiceAnswer extends Answer{

	/**
	 * The MultipleChoiceQuestion this answer relates to.
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion
	 * @ORM\ManyToOne
	 */
	protected $question;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer
	 * @ORM\ManyToOne
	 */
	protected $possibleAnswer;


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer $possibleAnswer
	 */
	public function setPossibleAnswer($possibleAnswer) {
		$this->possibleAnswer = $possibleAnswer;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer
	 */
	public function getPossibleAnswer() {
		return $this->possibleAnswer;
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

}
?>