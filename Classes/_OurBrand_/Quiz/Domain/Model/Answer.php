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
 * Represents the answer to an exercise including the score.
 * Each exercise type must register one and exactly one of these
 * for each exercise a student completes. If an exercise needs more precise answer registration
 * the exercise must implement it in its own domain model.
 *
 * @Flow\Entity
 */
class Answer {

	/**
	 * @var \DateTime
	 */
	protected $answerTime;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercise
	 * @ORM\ManyToOne
	 */
	protected $exercise;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\StudentQuizSession
	 * @ORM\ManyToOne
	 */
	protected $studentQuizSession;

	/**
	 * The score for this answer.
	 * @var int
	 */
	protected $score;

	/**
	 * Status of the answer: 1 = done, 0 = not done
	 * "Done" is decided by the amount of answers compared to the maximum score.
	 * @var int
	 */
	protected $status = 0;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\AnswerData>
	 * @ORM\OneToMany(mappedBy="answer")
	 */
	protected $answerDatas;


	public function __construct(){
		$this->answerDatas = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function setStudentQuizSession($studentQuizSession) {
		$this->studentQuizSession = $studentQuizSession;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\StudentQuizSession
	 */
	public function getStudentQuizSession() {
		return $this->studentQuizSession;
	}


	/**
	 * @param \DateTime $answerTime
	 */
	public function setAnswerTime($answerTime) {
		$this->answerTime = $answerTime;
	}

	/**
	 * @return \DateTime
	 */
	public function getAnswerTime() {
		return $this->answerTime;
	}

	/**
	 * @param int $score
	 */
	public function setScore($score) {
		$this->score = $score;
	}

	/**
	 * @return int
	 */
	public function getScore() {
		return $this->score;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $answerDatas
	 */
	public function setAnswerDatas($answerDatas) {
		$this->answerDatas = $answerDatas;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getAnswerDatas() {
		return $this->answerDatas;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\AnswerData $answerData
	 * @return void
	 */
	public function addAnswerData($answerData){
		$answerData->setAnswer($this);
		$this->answerDatas->add($answerData);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\AnswerData $answerData
	 * @return void
	 */
	public function removeAnswerData($answerData){
		if($this->answerDatas->contains($answerData)){
			$this->answerDatas->removeElement($answerData);
		}
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}


}
?>