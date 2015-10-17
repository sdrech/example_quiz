<?php
namespace _OurBrand_\Quiz\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class StudentQuizSessionRepository extends Repository {

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @param string $sortBy
	 * @param string $order
	 * @param array $filters
	 * @param int $limit
	 * @param int $start
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findTableData($quizSession, $sortBy, $order = 'DESC', $filters = array(), $start = 0, $limit = 10) {
		if ($sortBy == 'grade' || $sortBy == 'results') {
			$sortBy = 'score';
		} elseif ($sortBy == 'statusStudentSession') {
			$sortBy = 'finishedTime';
		}

		$sortOrder = \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING;
		if ($order == 'DESC') {
			$sortOrder = \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING;
		}

		$query = $this->createQuery();
		$query->matching( $query->equals('quizSession', $quizSession) );
		$query->setOrderings(array($sortBy => $sortOrder, 'finishedTime' => 'DESC'));
		if ($limit > 0) {
			$query->setLimit($limit);
			$query->setOffset($start);
		}

		return $query->execute();
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizSession $quizSession
	 * @param string $student
	 */
	public function findOneByQuizSessionAndStudent($quizSession, $student) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('quizSession', $quizSession),
				$query->equals('student', $student)
			)
		);

		return $query->execute()->getFirst();
	}

	/**
	 * @param string $quizSessionId
	 * @param void
	 */
	public function removeAllByQuizSession($quizSessionId) {
		$dql = "DELETE FROM \_OurBrand_\Quiz\Domain\Model\StudentQuizSession as sqs
				WHERE sqs.quizSession = '" . $quizSessionId ."'";
		$query = $this->entityManager->createQuery($dql);

		return $query->execute();
	}
}
?>