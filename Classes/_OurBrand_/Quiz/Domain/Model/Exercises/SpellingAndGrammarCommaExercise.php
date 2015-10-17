<?php
namespace _OurBrand_\Quiz\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;

/**
 * @Flow\Entity
 * @_OurBrand_\ExerciseUseController("SpellingAndGrammarCommaExercise")
 */
class SpellingAndGrammarCommaExercise extends \_OurBrand_\Quiz\Domain\Model\Exercise implements \_OurBrand_\Quiz\Domain\Model\ExerciseInterface {

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion>
	 * @ORM\OneToMany(mappedBy="exercise")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $questions;


	public function __construct() {
		$this->questions = new \Doctrine\Common\Collections\ArrayCollection();
		parent::__construct();
	}


	/**
	 * Settings of questions
	 * @param \Doctrine\Common\Collections\Collection $questions
	 */
	public function setQuestions($questions) {
		$this->questions = $questions;
		$this->maxScore = count($this->questions);
	}


	/**
	 * Set questions for this exercise from data array.
	 * All questions will be overwritten.
	 * Data array must be in this format:
	 * <code>
	 * $newQuestions = array(
	 *  array(
	 *    'questionWithComma' => 'Question11'
	 *    'questionWithoutComma' => 'Question12'
	 *   ),
	 *  array(
	 *    'questionWithComma' => 'Question21',
	 *    'questionWithoutComma' => 'Question22'
	 *   )
	 * );
	 * </code>
	 *
	 * @param array $newQuestions
	 */
	public function setQuestionsFromArray($newQuestions) {
		$this->questions->clear();

		foreach ($newQuestions as $questionData) {
			$question = new \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion();
			$question->setQuestionWithComma($questionData['questionWithComma']);
			$question->setQuestionWithoutComma($questionData['questionWithoutComma']);
			$this->addQuestion($question);
		}
	}


	/**
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion>
	 */
	public function getQuestions() {
		return $this->questions;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion $question
	 */
	public function addQuestion($question){
		$question->setExercise($this);
		$question->setNumber(count($this->questions));
		$this->questions->add($question);
		$this->maxScore = count($this->questions);
	}


	/**
	 * Get list of answers
	 * @return array
	 */
	public function getAnswers() {
		$result = array();
		foreach ($this->questions as $question) {
			$result[] = str_replace(',', '', $question->getQuestionWithComma());
		}

		return $result;
	}


	/**
	 * Find out if the [Student] has completed the exercise
	 * (aka. filled out enough for an score to be generated)
	 *
	 * @param array $questions
	 * @return int
	 */
	public function isCompleted($questions = array()) {
		$count = 0;
		if (!in_array($questions['mode'], array('withComma', 'withoutComma'))) return 0;
		foreach ($questions['answers'] as $values) {
			if ($values != '') {
				$count++;
			}
		}
		return ($this->getMaxScore() == $count ? 1 : 0);
	}


	/**
	 * Calculate the score for the given questions.
	 *
	 * @param array $questions
	 * @return int $score
	 */
	public function calculateScoreForAnswers($questions) {
		if (!in_array($questions['mode'], array('withComma', 'withoutComma'))) return 0;
		$score = 0;
		$mode = $questions['mode'];
		$answers = $questions['answers'];

		// Validate the answers, each correct answer gives a point.
		if (is_array($answers)) {
			//Loop each question
			foreach($answers as $questionIndex => $answerText) {
				// Safety check
				if($questionIndex < 0 || empty($answerText)) {
					continue;
				}

				$question = $this->getQuestions()->get((int)$questionIndex);
				// Safety check
				if(!$question) {
					continue;
				}
				switch ($mode) {
					case 'withComma':
						$answer = $question->getQuestionWithComma();
						break;

					case 'withoutComma':
						$answer = $question->getQuestionWithoutComma();
						break;
				}

				// Add one to the score if check is correct
				if ($answer == $answerText) {
					$score++;
				}
			}
		}

		return $score;
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
			foreach ($this->questions as $question) {
				$questionWithComma = $question->getQuestionWithComma();
				$questionWithoutComma = $question->getQuestionWithoutComma();

				if (empty($questionWithComma) || empty($questionWithoutComma)) {
					$ready = 0;
					break;
				}

				if (str_replace(',', '', $questionWithComma) !== str_replace(',', '', $questionWithoutComma)) {
					$ready = 0;
					break;
				}
			}
		}

		return $ready;
	}

	/**
	 * Implementation of clone magic method.
	 */
	public function postClone() {
		// Clone common parent objects.
		parent::postClone();

		$tempQuestions = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($this->questions as $question) {
			$newQuestion = clone $question;
			$newQuestion->setExercise($this);
			$tempQuestions->add($newQuestion);
		}
		$this->questions = $tempQuestions;
	}
}
?>