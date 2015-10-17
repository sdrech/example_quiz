<?php
namespace _OurBrand_\Quiz\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;
use _OurBrand_\Quiz\Domain\Model\ImageResource;

/**
 * @Flow\Entity
 */
class MultipleChoiceQuestion  {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercise
	 * @ORM\ManyToOne
	 */
	protected $exercise;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer>
	 * @ORM\OneToMany(mappedBy="question")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $possibleAnswers;

	/**
	 * @Flow\Transient
	 * @var int
	 */
	protected $numberOfCorrectAnswers = 1;

	/**
	 * @var int
	 */
	protected $number = 0;

	/**
	 * @var string
	 */
	protected $text = '';

	/**
	 * @var string
	 */
	protected $hint = '';


	public function __construct() {
		$this->possibleAnswers = new \Doctrine\Common\Collections\ArrayCollection();
	}


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
	 * @param \Doctrine\Common\Collections\Collection $possibleAnswers
	 */
	public function setPossibleAnswers($possibleAnswers) {
		$this->possibleAnswers = $possibleAnswers;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getPossibleAnswers() {
		return $this->possibleAnswers;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer $possibleAnswer
	 */
	public function addPossibleAnswer($possibleAnswer) {
		$possibleAnswer->setQuestion($this);
		$possibleAnswer->setNumber($this->possibleAnswers->count());
		$this->possibleAnswers->add($possibleAnswer);
	}
	
	/**
	 * @param array $possibleAnswersArray
	 */
	public function setPossibleAnswersFromArray($possibleAnswersArray) {

		$this->possibleAnswers->clear();

		$correctAnswerHasBeenSet = FALSE;
		foreach($possibleAnswersArray as $possibleAnswerData){
			$possibleAnswer = new MultipleChoicePossibleAnswer();
			$possibleAnswer->setText($possibleAnswerData['text']);
			$possibleAnswer->setIsCorrectAnswer($possibleAnswerData['correctAnswer'] ? TRUE : FALSE);
			if($possibleAnswer->getIsCorrectAnswer()){
				$correctAnswerHasBeenSet = TRUE;
			}
			if(isset($possibleAnswerData['new_image']) && is_a($possibleAnswerData['new_image'], '\_OurBrand_\Quiz\Domain\Model\ImageResource')){
				$possibleAnswer->setImage($possibleAnswerData['new_image']);
			}
			

			$this->addPossibleAnswer($possibleAnswer);
		}

		// Ensure there is a correct answer to this question.
		if($this->possibleAnswers->count() && !$correctAnswerHasBeenSet){
			$this->possibleAnswers->first()->setIsCorrectAnswer(TRUE);
		}

	}

	/**
	 * @return MultipleChoiceQuestion $this
	 */
	public function calculateNumberOfCorrectAnswers() {
		$numberOfCorrectAnswers = 0;
		foreach($this->possibleAnswers as $possibleAnswer) {
			if($possibleAnswer->getIsCorrectAnswer()) {
				$numberOfCorrectAnswers++;
			}
		}
		$this->numberOfCorrectAnswers = $numberOfCorrectAnswers;
		return $this;
	}


	/**
	 * @return int
	 */
	public function getNumberOfCorrectAnswers() {
		$this->calculateNumberOfCorrectAnswers();
		return $this->numberOfCorrectAnswers;
	}


	/**
	 * @param string $hint
	 */
	public function setHint($hint) {
		$this->hint = $hint;
	}

	/**
	 * @return string
	 */
	public function getHint() {
		return $this->hint;
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

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer $correctAnswer
	 */
	public function setCorrectAnswer($correctAnswer) {
		$this->correctAnswer = $correctAnswer;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer
	 */
	public function getCorrectAnswer() {
		return $this->correctAnswer;
	}

	/**
	 * Implementation of clone magic method.
	 */
	public function postClone() {
		$tempPossibleAnswers = new ArrayCollection();
		foreach($this->possibleAnswers as $possibleAnswer) {
			$newPossibleAnswer = clone $possibleAnswer;
			$newPossibleAnswer->setQuestion($this);
			$newPossibleAnswer->postClone();
			$tempPossibleAnswers->add($newPossibleAnswer);

		}
		$this->possibleAnswers = $tempPossibleAnswers;
	}
}
?>
