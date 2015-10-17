<?php
namespace _OurBrand_\Quiz\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A helper to determine if access to quizzes, views, etc.
 * is allowed.
 *
 * @Flow\Scope("singleton")
 */
class AccessHelper {

	const EXCEPTION_ILLEGAL_TYPE = 1399026352;
	const EXCEPTION_ILLEGAL_SUBJECT = 1399026353;
	const EXCEPTION_ILLEGAL_LEVEL = 1399026354;


	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\SubjectRepository
	 * @Flow\Inject
	 */
	protected $subjectRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TeamLevelRepository
	 * @Flow\Inject
	 */
	protected $teamLevelRepository;


	public function canStudentShowQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		return false;
	}


	public function canInstructorListQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		return false;
	}

	/**
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 * @param int $quizType
	 *
	 * @return bool
	 */
	public function canUserCreateQuiz(\_OurBrand_\My\Domain\Model\User $user, $quizType) {
		if($user->isWorker() || $user->isAdministrator()) {
			return true;
		}

		return ($user->isInstructor() && $this->userHasSubscriptionToQuizType($user, $quizType));
	}

	/**
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz   $quiz
	 *
	 * @return bool
	 */
	public function canUserEditQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		if ($user->isWorker()) {
			return true;
		}

		return ($user->isInstructor() && $user->getIdentifier() == $quiz->getCreator());

	}


	/**
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz   $quiz
	 *
	 * @return bool
	 */
	public function canUserPreviewQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		return $this->canUserEditQuiz($user, $quiz);
	}


	public function canStudentReviewQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		// Criteria:
		return false;
	}


	public function canUserReviewQuiz(\_OurBrand_\My\Domain\Model\User $user, \_OurBrand_\Quiz\Domain\Model\Quiz $quiz) {
		return false;
	}

}
