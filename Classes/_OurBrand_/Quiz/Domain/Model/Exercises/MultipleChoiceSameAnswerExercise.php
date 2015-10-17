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
use TYPO3\Flow\Core\Migrations\Manager;

/**
 * @Flow\Entity
 * @_OurBrand_\ExerciseUseController("MultipleChoiceSameAnswerExercise")
 */
class MultipleChoiceSameAnswerExercise
	extends \_OurBrand_\Quiz\Domain\Model\Exercise
	implements \_OurBrand_\Quiz\Domain\Model\ExerciseInterface {

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion>
	 * @ORM\OneToMany(mappedBy="exercise")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $questions;

	public function __construct() {
		$this->questions = new \Doctrine\Common\Collections\ArrayCollection();
		parent::__construct();
	}


	/**
	 * @param \Doctrine\Common\Collections\Collection $questions
	 */
	public function setQuestions($questions) {
		$this->questions = $questions;
		$this->maxScore = $this->questions->count();
	}


	/**
	 * Set questions for this exercise from data array.
	 * All questions will be overwritten.
	 * Data array must be in this format:
	 * <code>
	 * $newQuestions = array(
	 *  array(
	 *    'text' => 'Question text',
	 *    'hint' => 'Question hint',
	 *    'possibleAnswers' => array(
	 *
	 *    )
	 *   )
	 * );
	 * </code>
	 *
	 * @param array $newQuestions
	 */
	public function setQuestionsFromArray($newQuestions) {

		$this->questions->clear();

		for($i = 0; $i < count($newQuestions); $i++) {
			if(!empty($newQuestions[$i]['text'])){
				$question = new MultipleChoiceQuestion();
				$question->setText($newQuestions[$i]['text']);
				$question->setHint($newQuestions[$i]['hint']);
				$question->setPossibleAnswersFromArray($newQuestions[$i]['possibleAnswers']);
				$this->addQuestion($question);
			}
		}
	}


	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getQuestions() {
		return $this->questions;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function addQuestion($question) {
		$question->setExercise($this);
		$question->setNumber($this->questions->count());
		$this->questions->add($question);
		$this->maxScore = $this->questions->count();
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function updateQuestion($question) {
		if($this->questions->contains($question)){
			$this->questions->set($this->questions->indexOf($question), $question);
		}
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion $question
	 */
	public function removeQuestion($question) {
		$this->questions->removeElement($question);
		$this->maxScore = $this->questions->count();
	}


	/**
	 * Implementation of clone magic method.
	 */
	public function postClone() {
		// Clone common parent objects.
		parent::postClone();

		$tempQuestions = new ArrayCollection();
		foreach ($this->questions as $question) {
			$newQuestion = clone $question;
			$newQuestion->postClone();
			$newQuestion->setExercise($this);
			$tempQuestions->add($newQuestion);
		}
		$this->questions = $tempQuestions;
	}

	/**
	 * @param array $answers
	 * @return int $score
	 */
	public function calculateScoreForAnswers($answers) {
		$score = 0;

		// Validate the answers, each correct answer gives a point.
		if(is_array($answers)){
			foreach($answers as $questionIndex => $checkedChoiceIndex){
				// Safety check
				if($questionIndex < 0 || $checkedChoiceIndex < 0){
					continue;
				}
				$question = $this->getQuestions()->get((int)$questionIndex);
				// Safety check
				if(!$question){
					continue;
				}
				$possibleAnswer = $question->getPossibleAnswers()->get((int)$checkedChoiceIndex);
				// Safety check
				if(!$possibleAnswer) {
					continue;
				}

				// Add one to the score if check is correct
				if($possibleAnswer->getIsCorrectAnswer()){
					$score++;
				}
			}
		}
		return $score;
	}

	/**
	 * @param array $answers
	 * @return int
	 */
	public function isCompleted($answers = array()) {
		return ($this->getMaxScore() == count(array_filter($answers, 'strlen')) ? 1 : 0);
	}

	/**
	 * Check if all required fields are filled out by the [Instructor]
	 *
	 * Note: This function should always call getExerciseReadyForCompletion()
	 * to validate the extended Exercise
	 *
	 * @return int $ready (0 = not ready, 1 = ready for completion, 2 = completed)
	 */
	public function getReadyForCompletion() {
		$ready = $this->getExerciseReadyForCompletion();

		//Is there a question
		if(count($this->questions) == 0) {
			$ready = 0;
		} else {
			foreach($this->questions as $question) {
				//Is the question filled out
				$questionText = $question->getText();
				if(empty($questionText)) {
					$ready= 0;
				} else {
					$possibleAnswers = $question->getPossibleAnswers();
					//Does it have atleast 2 possible answers
					if(count($possibleAnswers) < 2) {
						$ready = 0;
					} else {
						foreach($possibleAnswers as $possibleAnswer) {
							//Does each answer have a text
							$answerText = $possibleAnswer->getText();
							if(empty($answerText)) {
								$ready= 0;
							}
						}
					}
				}
			}
		}
		return $ready;
	}
}
?>