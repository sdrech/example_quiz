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

/**
 * @Flow\Entity
 * @_OurBrand_\ExerciseUseController("DragAndDropWordToQuestionExercise")
 */
class DragAndDropWordToQuestionExercise extends \_OurBrand_\Quiz\Domain\Model\Exercise implements \_OurBrand_\Quiz\Domain\Model\ExerciseInterface {

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion>
	 * @ORM\OneToMany(mappedBy="exercise")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $questions;


	public function __construct(){
		$this->questions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->allAnswers = new \Doctrine\Common\Collections\ArrayCollection();
		parent::__construct();
	}


	/**
	 * @param \Doctrine\Common\Collections\Collection $questions
	 */
	public function setQuestions($questions) {
		$this->questions = $questions;
		$this->maxScore = count($this->getRealQuestions());
	}


	/**
	 * Set questions for this exercise from data array.
	 * All questions will be overwritten.
	 * Data array must be in this format:
	 * <code>
	 * $newQuestions = array(
	 *  array(
	 *    'question' => 'Question',
	 *    'answer' => 'Answer'
	 *   )
	 * );
	 * </code>
	 *
	 * @param array $newQuestions
	 */
	public function setQuestionsFromArray($newQuestions) {
		$this->questions->clear();

		foreach ($newQuestions as $questionData) {
			$question = new \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion();
			$question->setQuestion($questionData['question']);
			$question->setAnswer($questionData['answer']);
			$this->addQuestion($question);
		}
	}


	/**
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion>
	 */
	public function getQuestions() {
			return $this->questions;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion $question
	 */
	public function addQuestion($question){
		$question->setExercise($this);
		$question->setNumber($this->questions->count());
		$question->setSorting($this->questions->count());
		$this->questions->add($question);
		$this->maxScore = count($this->getRealQuestions());
	}


	/**
	 * Generate extraWords from array
	 * @param array $extraWords
	 */
	/**
	 * Set extraWords for this exercise from data array.
	 * All extraWords will be overwritten.
	 * Data array must be in this format:
	 * <code>
	 * $newQuestions = array(
	 *  'word1', 'word2', ...
	 * );
	 * </code>
	 *
	 * @param array $newQuestions
	 */
	public function setExtraWordsFromArray($extraWords) {
		foreach ($extraWords as $questionWord) {
			$question = new \_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion();
			$question->setQuestion(null);
			$question->setAnswer($questionWord);
			$this->addQuestion($question);
		}
	}


	/**
	 * Getting real questions, not all rows from table. Because we save extraWords and Questions in the same table.
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion>
	 */
	public function getRealQuestions() {
		$answers = array();
		foreach ($this->questions as $question) {
			if (!is_null($question->getQuestion())) $answers[] = $question;
		}
		return $answers;
	}


	/**
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion>
	 */
	public function getExtraWords() {
		$words = array();
		foreach ($this->questions as $question) {
			if (is_null($question->getQuestion())) $words[] = $question;
		}
		return $words;
	}


	/**
	 * Generate all answer in random order
	 */
	public function randomize() {
		$answers = array_keys($this->questions->toArray());
		shuffle($answers);

		foreach ($answers as $index => $value) {
			$this->questions[$index]->setSorting($value);
		}
	}


	/**
	 * Return all answers in needed order
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion>
	 */
	public function getAllAnswers() {
		$answers = array();
		foreach ($this->questions as $index => $question) {
			$answers[$index] = $question->getSorting();
		}
		asort($answers);

		$questions = array();
		foreach (array_keys($answers) as $key) {
			$questions[] = $this->questions[$key];
		}

		return $questions;
	}


	/**
	 * @param array $answers
	 * @return int $score
	 */
	public function calculateScoreForAnswers($answers) {
		$score = 0;

		// Validate the answers, each correct answer gives a point.
		if(is_array($answers)) {
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
				$answer = $question->getAnswer();
				// Safety check
				if (!$answer) {
					continue;
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
	 * @param array $answers
	 * @return int
	 */
	public function isCompleted($answers = array()) {
		$count = 0;
		foreach ($answers as $values) {
			if ($values != '') {
				$count++;
			}
		}
		return ($this->getMaxScore() == $count ? 1 : 0);
	}


	public function getReadyForCompletion() {
		$ready = $this->getExerciseReadyForCompletion();

		//Is there a question
		if(count($this->questions) == 0) {
			$ready = 0;
		} else {
			foreach ($this->getRealQuestions() as $question) {
				//Is the question/answer filled out
				$questionText = $question->getQuestion();
				$answer = $question->getAnswer();

				if(empty($questionText) || empty($answer)) {
					$ready= 0;
					break;
				}
			}
			// check for doubles
			$answers = array();
			foreach ($this->questions as $question) {
				if (in_array($question->getAnswer(), $answers)) {
					$ready = 0;
					break;
				} else {
					$answers[] = $question->getAnswer();
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

		$tempQuestions = new ArrayCollection();
		foreach ($this->questions as $question) {
			$newQuestion = clone $question;
			$newQuestion->setExercise($this);
			$tempQuestions->add($newQuestion);
		}
		$this->questions = $tempQuestions;
	}
}
?>