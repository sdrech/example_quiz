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
 * @_OurBrand_\ExerciseUseController("MatchSequenceExercise")
 */
class MatchSequenceExercise extends \_OurBrand_\Quiz\Domain\Model\Exercise implements \_OurBrand_\Quiz\Domain\Model\ExerciseInterface {
	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequencePhrase>
	 * @ORM\OneToMany(mappedBy="exercise")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $phrases;

	public function __construct() {
		$this->phrases = new \Doctrine\Common\Collections\ArrayCollection();
		parent::__construct();
	}

	/**
	 * Set phrases for this exercise from data array.
	 * All phrases will be overwritten.
	 * Data array must be in this format:
	 * <code>
	 * $phrases = array('phrase1', 'phrase2');
	 * </code>
	 *
	 * @param array $phrases
	 */
	public function setPhrasesFromArray($phrases) {
		$this->phrases->clear();

		foreach ($phrases as $phraseData) {
			$phrase = new \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequencePhrase;
			$phrase->setPhrase($phraseData);
			$this->addPhrase($phrase);
		}
	}

	/**
	 * Adds phrase to list
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequencePhrase $phrase
	 */
	protected function addPhrase($phrase) {
		$phrase->setExercise($this);
		$phrase->setNumber($this->phrases->count());
		$phrase->setSorting($this->phrases->count());
		$this->phrases->add($phrase);
		$this->setMaxScore($this->phrases->count());
	}

	/**
	 * Gets phrases list
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MatchSequencePhrase>
	 */
	public function getPhrases() {
		return $this->phrases;
	}

	/**
	 * Generate all answer in random order
	 */
	public function randomize() {
		$phrases = array_keys($this->phrases->toArray());
		shuffle($phrases);

		foreach ($phrases as $index => $phrase) {
			$this->phrases[$index]->setSorting($phrase);
		}
	}

	/**
	 * Return phrases in initial orders
	 * @return array
	 */
	public function getPreorderedAnswers() {
		$answers = array();
		foreach ($this->phrases as $phrase) {
			$answers[$phrase->getSorting()] = $phrase->getPhrase();
		}
		ksort($answers);

		return $answers;
	}

	/**
	 * Calculate the score for the given answers.
	 *
	 * @param array $answers
	 * @return int $score
	 */
	public function calculateScoreForAnswers($answers) {
		$score = 0;

		// Validate the answers, each correct answer gives a point.
		if (is_array($answers)) {
			//Loop each question
			foreach ($answers as $phraseIndex => $phraseText) {
				// Safety check
				if ($phraseIndex < 0 || empty($phraseText)) {
					continue;
				}

				$phrase = $this->getPhrases()->get((int)$phraseIndex);
				// Safety check
				if (!$phrase) {
					continue;
				}
				$answer = $phrase->getPhrase();
				// Safety check
				if (!$answer) {
					continue;
				}

				// Add one to the score if check is correct
				if ($answer == $phraseText) {
					$score++;
				}
			}
		}

		return $score;
	}

	/**
	 * Find out if the [Student] has completed the exercise
	 * (aka. filled out enough for an score to be generated)
	 *
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

		//Is there a phrases
		if (count($this->phrases) == 0) {
			$ready = 0;
		} else {
			foreach ($this->phrases as $phrase) {
				//Is the question/answer filled out
				$text = $phrase->getPhrase();
				if (mb_strlen(trim($text)) == 0) {
					$ready = 0;
					break;
				}
			}
			// check for doubles
			$all = array();
			foreach ($this->phrases as $phrase) {
				if (in_array($phrase->getPhrase(), $all)) {
					$ready = 0;
					break;
				} else {
					$all[] = trim($phrase->getPhrase());
				}
			}
		}

		return $ready;
	}

	/**
	 * @inheritdoc
	 */
	public function postClone() {
		parent::postClone();
		$phrases = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($this->phrases as $phrase) {
			$newPhrase = clone $phrase;
			$newPhrase->setExercise($this);
			$phrases->add($newPhrase);
		}
		$this->phrases = $phrases;
	}
}

?>