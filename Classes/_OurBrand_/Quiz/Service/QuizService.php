<?php
namespace _OurBrand_\Quiz\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * Class QuizService
 *
 * @package _OurBrand_\Quiz\Service
 * @api
 * @Flow\Scope("singleton")
 *
 */
class QuizService {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\StudentQuizSessionRepository
	 * @Flow\inject
	 */
	protected $studentQuizSessionRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizGradeRepository
	 * @Flow\inject
	 */
	protected $quizGradeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizSessionRepository
	 * @Flow\inject
	 */
	protected $quizSessionRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\Inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var int Minimum amount of seconds to elapse between writes to liveStatisticsFile
	 */
	protected $liveStatisticsFileUpdateInterval = 2;


	/**
	 * Creates a quiz session and student quiz session objects for each
	 * student.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizAssignment $quizAssignment
	 * @param $quizSession
	 *
	 * @return \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 *
	 * @api
	 */
	public function assignQuizToStudents($quizAssignment, $quizSession="") {
		$newQuizSessionFlag = empty($quizSession) ? true : false;

		// Step 1: Validate parameters.
		if (
			$quizAssignment->getQuiz() == null
			|| $quizAssignment->getStudentAssignments()->count() < 1
			|| $quizAssignment->getInstructorIdentifier() == ''
		) {
			throw new \InvalidArgumentException('Error in QuizAssignment properties.', 1384944001);
		}

		// If quiz is a snapshot, find the original and use that instead. Will make the next operations more consistent
		if ($quizAssignment->getQuiz()->getSnapshotOf() instanceof \_OurBrand_\Quiz\Domain\Model\Quiz) {
			$quizAssignment->setQuiz($quizAssignment->getQuiz()->getSnapshotOf());
		}

		// Step 2: Find out which snapshot of quiz we can use.
		$snapshot = $this->quizRepository->findLatestValidSnapshot($quizAssignment->getQuiz());

		if (!($snapshot instanceof \_OurBrand_\Quiz\Domain\Model\Quiz)) {
			$snapshot = $this->makeSnapshotAndPersist($quizAssignment->getQuiz());
		}

		if (!($snapshot instanceof \_OurBrand_\Quiz\Domain\Model\Quiz)) {
			throw new \RuntimeException('Snapshot not found and could not be made for Quiz!', 1396342271);
		}

		if ($newQuizSessionFlag) {
			// insert new record into QuizSession model
			$quiz = is_null($quizSession) ? $quizAssignment->getQuiz() : $snapshot;
			$quizSession = new \_OurBrand_\Quiz\Domain\Model\QuizSession();
			$quizSession->setQuiz($quiz);
			$quizSession->setInstructor($quizAssignment->getInstructorIdentifier());
			$quizSession->setContext($quizAssignment->getContext());
			$quizSession->setTeamIdentifier($quizAssignment->getTeamIdentifier());
			$quizSession->setShowGradeOnSummary($quizAssignment->getShowGradeOnSummary());
			$quizSession->setShowTimer($quizAssignment->getShowTimer());
			$quizSession->setIsDemo($quizAssignment->getIsDemo());
			if ($quizAssignment->getIsDemo()) {
				// Set the start time at once, so we know when it is removed by garbage collector
				$quizSession->setStartTime(new \TYPO3\Flow\Utility\Now());
			}
		} else {
			// iupdate existent record in QuizSession model
			if (!is_a($quizSession, '\_OurBrand_\Quiz\Domain\Model\QuizSession')) {
				$quizSession = $this->persistenceManager->getIdentifierByObject($quizSession);
			}
			$quizSession->setContext($quizAssignment->getContext());
			$quizSession->setShowGradeOnSummary($quizAssignment->getShowGradeOnSummary());
			$quizSession->setShowTimer($quizAssignment->getShowTimer());

			// remove all studentAssignments (StundetQuizSessions) to insert updated ones
			$quizSessionId = $this->persistenceManager->getIdentifierByObject($quizSession);
			$this->studentQuizSessionRepository->removeAllByQuizSession($quizSessionId);
		}

		if ($quizAssignment->getAssignmentType() == \_OurBrand_\Quiz\Domain\Model\QuizAssignment::ASSIGNMENT_AT_HOME) {
			$this->startQuizSession($quizSession);
		}

		foreach ($quizAssignment->getStudentAssignments() as $studentAssignment) {
			// insert new records about assignments for each student
			$studentQuizSession = new \_OurBrand_\Quiz\Domain\Model\StudentQuizSession();
			$studentQuizSession->setQuizSession($quizSession);
			$studentQuizSession->setStudent($studentAssignment->getStudentIdentifier());
			$studentQuizSession->setExtratime($studentAssignment->getExtraTime());
			$quizSession->addStudentQuizSession($studentQuizSession);
		}
		if ($newQuizSessionFlag) {
			$this->quizSessionRepository->add($quizSession);
		} else {
			$this->quizSessionRepository->update($quizSession);
		}

		$this->persistenceManager->persistAll();
		return $quizSession;
	}

	/**
	 * Start a quizSession, which means the whole team.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @return int|string
	 */
	public function startQuizSession(\_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession) {
		$quizSession->setStartTime(new \TYPO3\Flow\Utility\Now());
		$this->quizSessionRepository->update($quizSession);
		$out = $this->writeCommandFile($quizSession);
		return $out;
	}

	/**
	 * Pause a quizSession, which means the whole team.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @return int|string
	 */
	public function pauseQuizSession(\_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession) {
		foreach ($quizSession->getStudentQuizSessions() as $studentQuizSession) {
			$this->pauseStudentQuizSession($studentQuizSession, FALSE);
		}

		$quizSession->setPauseTime(new \TYPO3\Flow\Utility\Now());
		$this->quizSessionRepository->update($quizSession);
		$out = $this->writeCommandFile($quizSession);
		return $out;
	}

	/**
	 * Resume a quizSession, which means the whole team.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @return int|string
	 */
	public function resumeQuizSession(\_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession) {
		foreach ($quizSession->getStudentQuizSessions() as $studentQuizSession) {
			$this->resumeStudentQuizSession($studentQuizSession, FALSE);
		}

		$quizSession->setPauseTime(null);
		$this->quizSessionRepository->update($quizSession);
		$out = $this->writeCommandFile($quizSession);
		return $out;
	}

	/**
	 * Stop a quizSession, which means the whole team.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @return int|string
	 */
	public function stopQuizSession(\_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession) {
		foreach ($quizSession->getStudentQuizSessions() as $studentQuizSession) {
			$this->stopStudentQuizSession($studentQuizSession, FALSE);
		}

		$quizSession->setStopTime(new \TYPO3\Flow\Utility\Now());
		$this->quizSessionRepository->update($quizSession);
		$out = $this->writeCommandFile($quizSession);
		return $out;
	}

	/**
	 * Pause a studentQuizSession, which means the just one student.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param bool $writeCommandFile
	 * @return int|string
	 */
	public function pauseStudentQuizSession(\_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession, $writeCommandFile = TRUE) {
		$out = 1;
		$studentQuizSession->setPauseTime(new \TYPO3\Flow\Utility\Now());
		$this->studentQuizSessionRepository->update($studentQuizSession);
		if ($writeCommandFile) {
			$out = $this->writeCommandFile($studentQuizSession->getQuizSession());
		}
		return $out;
	}

	/**
	 * Resume a studentQuizSession, which means the just one student.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param bool $writeCommandFile
	 * @return int|string
	 */
	public function resumeStudentQuizSession(\_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession, $writeCommandFile = TRUE) {
		$out = 1;
		$pausedAt = $studentQuizSession->getPauseTime()->getTimestamp();

		$extraTime = time() - $pausedAt;
		$studentQuizSession->addToPauseDuration($extraTime);
		$studentQuizSession->setPauseTime(null);
		$this->studentQuizSessionRepository->update($studentQuizSession);
		if ($writeCommandFile) {
			$out = $this->writeCommandFile($studentQuizSession->getQuizSession());
		}
		return $out;
	}

	/**
	 * Stop a studentQuizSession, which means the just one student.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param bool $writeCommandFile
	 * @return int|string
	 */
	public function stopStudentQuizSession(\_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession, $writeCommandFile = TRUE) {
		$out = 1;
		$studentQuizSession->setStopTime(new \TYPO3\Flow\Utility\Now());
		$this->studentQuizSessionRepository->update($studentQuizSession);
		if ($writeCommandFile) {
			$out = $this->writeCommandFile($studentQuizSession->getQuizSession());
		}
		return $out;
	}


	/**
	 * $resultsData should be array with following keys and values:
	 * 't' in seconds (currently not used)
	 * 's' integer score
	 *
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param array $resultsData
	 */
	public function addResultToQuizSessionResultsStatistics($studentQuizSession, $exercise, $resultsData) {

		$studentQuizSessionIdentifier = $this->persistenceManager->getIdentifierByObject($studentQuizSession);
		$exerciseIdentifier = $this->persistenceManager->getIdentifierByObject($exercise);

		$quizSession = $studentQuizSession->getQuizSession();
		$quizSession->addResultToResultsStatistics($studentQuizSessionIdentifier, $exerciseIdentifier, $resultsData);
		$this->quizSessionRepository->update($quizSession);

		$this->writeLiveStatisticsFile($quizSession);
	}

}

