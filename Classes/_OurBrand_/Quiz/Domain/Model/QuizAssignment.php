<?php
namespace _OurBrand_\Quiz\Domain\Model;

/**
 * Class QuizAssignment
 *
 * Plain old PHP object to hold settings needed to assign quiz
 * to one or more students. Used as a data transfer object between
 * assign quiz function in My and the QuizMATE core logic.
 *
 * @package _OurBrand_\Quiz\Domain\Model
 */
class QuizAssignment {


	const SESSION_REAL = 0;
	const SESSION_TRAINING = 1;

	const ASSIGNMENT_IN_CLASS = 0;
	const ASSIGNMENT_AT_HOME = 1;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	protected $quiz;

	/**
	 * @var string
	 */
	protected $instructorIdentifier;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\StudentQuizAssignment>
	 */
	protected $studentAssignments;

	/**
	 * @var bool
	 */
	protected $showGradeOnSummary = FALSE;

	/**
	 * @var bool
	 */
	protected $showTimer = FALSE;


	/**
	 * @var int
	 */
	protected $context = 0;


	/**
	 * @var int
	 */
	protected $assignmentType = 0;

	/**
	 * @var string
	 */
	protected $teamIdentifier = '';

	/**
	 * identifies if this assignment is a demo
	 * @var bool
	 */
	protected $isDemo = false;

	/**
	 *
	 */
	public function __construct() {
		$this->studentAssignments = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * @param string $instructorIdentifier
	 */
	public function setInstructorIdentifier($instructorIdentifier) {
		$this->instructorIdentifier = $instructorIdentifier;
	}

	/**
	 * @return string
	 */
	public function getInstructorIdentifier() {
		return $this->instructorIdentifier;
	}

	/**
	 * @param StudentQuizAssignment $studentAssignment
	 */
	public function addStudentAssignment(StudentQuizAssignment $studentAssignment) {
		$studentAssignment->setQuizAssignment($this);
		$this->studentAssignments->add($studentAssignment);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function setQuiz($quiz) {
		$this->quiz = $quiz;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	public function getQuiz() {
		return $this->quiz;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $studentAssignments
	 */
	public function setStudentAssignments($studentAssignments) {
		$this->studentAssignments = $studentAssignments;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getStudentAssignments() {
		return $this->studentAssignments;
	}

	/**
	 * @param boolean $showGradeOnSummary
	 */
	public function setShowGradeOnSummary($showGradeOnSummary) {
		$this->showGradeOnSummary = $showGradeOnSummary;
	}

	/**
	 * @return boolean
	 */
	public function getShowGradeOnSummary() {
		return $this->showGradeOnSummary;
	}

	/**
	 * @param boolean $showTimer
	 */
	public function setShowTimer($showTimer) {
		$this->showTimer = $showTimer;
	}

	/**
	 * @return boolean
	 */
	public function getShowTimer() {
		return $this->showTimer;
	}

	/**
	 *
	 *
	 * @param int $context Can be one of SESSION_REAL or SESSION_TRAINING
	 */
	public function setContext($context) {
		$this->context = $context;
	}

	/**
	 * @return int
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @param int $assignmentType
	 */
	public function setAssignmentType($assignmentType) {
		$this->assignmentType = $assignmentType;
	}

	/**
	 * @return int
	 */
	public function getAssignmentType() {
		return $this->assignmentType;
	}



	/**
	 * @param string $teamIdentifier
	 */
	public function setTeamIdentifier($teamIdentifier) {
		$this->teamIdentifier = $teamIdentifier;
	}

	/**
	 * @return string
	 */
	public function getTeamIdentifier() {
		return $this->teamIdentifier;
	}

	/**
	 * @param bool $isDemo
	 */
	public function setIsDemo($isDemo) {
		$this->isDemo = $isDemo;
	}

	/**
	 * @return bool
	 */
	public function getIsDemo() {
		return $this->isDemo;
	}





}