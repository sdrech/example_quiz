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
class AnswerRepository extends Repository {

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $session
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findBySessionAndExercise($session, $exercise) {

		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(

				$query->equals('studentQuizSession', $session),
				$query->equals('exercise', $exercise)
			)
		);
		return $query->execute();
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $session
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findBySession($session) {

		$query = $this->createQuery();
		$query->matching(
			$query->equals('studentQuizSession', $session)
		);
		return $query->execute();
	}


}
?>