<?php
namespace _OurBrand_\Quiz\Domain\Model;

/**
 * Class StudentQuizAssignment
 *
 * Plain old PHP object to hold settings needed to assign quiz to student. Used as a data transfer object between
 * assign quiz function in My and the QuizMATE core logic.
 *
 * @package _OurBrand_\Quiz\Domain\Model
 */
class StudentQuizAssignment {


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\QuizAssignment
	 */
	protected $quizAssignment = null;

	/**
	 * @var string
	 */
	protected $studentIdentifier = '';

	/**
	 * @var int Extra seconds for this student.
	 */
	protected $extraTime = 0;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizAssignment $quizAssignment
	 */
	public function setQuizAssignment($quizAssignment) {
		$this->quizAssignment = $quizAssignment;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\QuizAssignment
	 */
	public function getQuizAssignment() {
		return $this->quizAssignment;
	}

	/**
	 * @param string $studentIdentifier
	 */
	public function setStudentIdentifier($studentIdentifier) {
		$this->studentIdentifier = $studentIdentifier;
	}

	/**
	 * @return string
	 */
	public function getStudentIdentifier() {
		return $this->studentIdentifier;
	}

	/**
	 * @param int $extraTime
	 */
	public function setExtraTime($extraTime) {
		if($extraTime < 0) {
			$extraTime = 0;
		}
		$this->extraTime = $extraTime;
	}

	/**
	 * @return int
	 */
	public function getExtraTime() {
		return $this->extraTime;
	}



}