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
class QuizRepository extends Repository {
	
	/**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * Removes an object from this repository.
	 * First we must remove all references to this object.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object) {

		if ($object instanceof \_OurBrand_\Quiz\Domain\Model\Quiz) {

			$copies = $this->findByCopyOf($object);
			/** @var \_OurBrand_\Quiz\Domain\Model\Quiz $copy */
			foreach ($copies as $copy) {

				$copy->setCopyOf(null);
				$copy->setWasCopyOf($object->getTitle());
				$this->update($copy);
			}

			$snapshots = $object->getSnapshots();
			/** @var \_OurBrand_\Quiz\Domain\Model\Quiz $snapshot */
			foreach ($snapshots as $snapshot) {

				// Todo: Check if snapshot is assigned. If assigned
				// throw exception.
				$snapshot->setSnapshotOf(null);
				$this->remove($snapshot);
			}
		}

		parent::remove($object);
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 *
	 * @return \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	public function findLatestValidSnapshot($quiz) {

		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('snapshotOf', $quiz)
			)
		);
		$query->setOrderings(array('snapshotTimestamp' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING));
		return $query->execute()->getFirst();

	}


	/**
	 * @param string $user
	 * @param string $subject
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findAllByUser($user, $subject = null) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('creator', $user),
				$query->equals('snapshotOf', null),
				$query->equals('isDraft', false),
				$query->equals('isDeleted', false),
				$query->equals('subject', $subject)
			)
		);
		return $query->execute();
	}

	/**
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findAllQuiz() {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('Quiz', true),
				$query->equals('isDeleted', false),
				$query->equals('snapshotOf', null)
			)
		);
		$query->setOrderings(array('title' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING));
		return $query->execute();
	}

	/**
	 *
	 * @param string $subject
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findAllPublic($subject = null) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('snapshotOf', null),
				$query->equals('isDeleted', false),
				$query->equals('isDraft', false),
				$query->equals('subject', $subject)
			)
		);
		return $query->execute();
	}

	/**
	 * Interface implemented function
	 *
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 * @param string $sortColumn
	 * @param string $sortOrder
	 * @param array $columns
	 * @param array $filters
	 * @param int $limit
	 * @param int $start
	 * @param string $searchQuery
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findAllAvailableByArguments(
		$user,
		$sortColumn = 'title',
		$sortOrder = 'ASC',
		$columns = array(),
		$filters = array(),
		$limit = 10,
		$start = 0,
		$searchQuery = '',
		$type = 0
	) {

		$conn = $this->entityManager->getConnection();
		//$query = $conn->createQueryBuilder();
		$query = $this->entityManager->createQueryBuilder();

		if ($user->isWorker()) {
			$expression = $query->expr()->eq('q.Quiz', 1);
		} else {
			$expression = $query->expr()->andX(
				$query->expr()->eq('q.Quiz', 1),
				$query->expr()->eq('q.isDraft', 0)
			);
		}

		$query->select('q.persistence_object_identifier')
			->from('_OurBrand__quiz_domain_model_quiz', 'q')
			->where(
				$query->expr()->andX(
					$query->expr()->eq('q.type', intval($type)),
					$query->expr()->eq('q.isDeleted', 0),
					$query->expr()->isNull('q.snapshotOf'),
					$query->expr()->orX(
						$query->expr()->like('q.creator', $query->expr()->literal($user->getIdentifier())),
						$expression
					)
				)
			)
			->orderBy('q.title', ($sortOrder == 'ASC' ? 'ASC' : 'DESC'))
			->groupBy('q.persistence_object_identifier')
			->setFirstResult( $start )
			->setMaxResults( $limit );
		if (strlen(trim($searchQuery))) {
			$query->andWhere(
				$query->expr()->like('q.title', $query->expr()->literal('%' . addslashes($searchQuery) . '%'))
			);
		}
		
		if (!empty($filters)) {
			foreach ($filters as $type => $filter) {
				if (count($filter)) {
					switch ($type) {
						case "subjects":
							$contains = array();
							foreach ($filter as $data) {
								$contains[] = $data['id'];
							}

							$query->leftJoin(
								'_OurBrand__quiz_domain_model_quiz_subjects_join',
								'quiz_subjects',
								'ON',
								'quiz_subjects.quiz_quiz = q.persistence_object_identifier'
							);
							$query->andWhere(
								$query->expr()->in('quiz_subjects.quiz_subject', $contains)
							);
							break;
						case "teamLevels":
							$contains = array();
							foreach ($filter as $data) {
								$contains[] = $data['id'];
							}
							$query->leftJoin(
								'_OurBrand__quiz_domain_model_quiz_levels_join',
								'quiz_levels',
								'ON',
								'quiz_levels.quiz_quiz = q.persistence_object_identifier'
							);
							$query->andWhere(
								$query->expr()->in('quiz_levels.quiz_teamlevel', $contains)
							);
							break;
						case "examTypes":
							$contains = array();
							foreach ($filter as $data) {
								$contains[] = $data['id'];
							}
							$query->andWhere(
								$query->expr()->in('q.examtype', $contains)
							);
							break;
						case "categories":
							$contains = array();
							foreach ($filter as $data) {
								$contains[] = $data['id'];
							}
							$query->andWhere(
								$query->expr()->in('q.quizcategory', $contains)
							);
							break;
					}
				}
			}
		}
		
		if ($sortColumn == 'exercises') {
			$query->addSelect('COUNT( e.persistence_object_identifier ) AS num');
			$query->leftJoin('_OurBrand__quiz_domain_model_exercise', 'e', 'ON', 'e.quiz = q.persistence_object_identifier');
			$query->orderBy('num', ($sortOrder == 'ASC' ? 'ASC' : 'DESC'));
			$query->addOrderBy('q.title', 'ASC');
		}
		//echo $query->getDql();
		// the following does not work (or unreliable at best) with dql and joins
		// setFirstResult( $start )
		// setMaxResults( $limit );
		return $conn->fetchAll($query->getDql() . ' LIMIT ' . intval($start) . ' , ' . intval($limit) );
	}


	/**
	 *
	 */
	public function findAllNotSnapshot() {
		$query = $this->createQuery();
		$query->matching(
			$query->equals('snapshotOf', null)
		);
		$query->setOrderings(array('lastEdited' => 'DESC'));
		return $query->execute();
	}

	/**
	 * Return all the subjects available after filtering by a list of quiz filters
	 *
	 * @param string $option
	 * @param array $filters
	 * @param int $type
	 * @param \_OurBrand_\My\Domain\Model\User $user
	 * @return array
	 */
	public function findFilterOptionsByFilters($option, $filters, $type, $user) {
		// Don't filter on the options we want
		unset($filters[$option]);

		/** @var $conn \Doctrine\DBAL\Connection * */
		$conn = $this->entityManager->getConnection();

		/** @var $queryBuilder \Doctrine\DBAL\Query\QueryBuilder * */
		$queryBuilder = $conn->createQueryBuilder();

		$queryBuilder->select($option . '.persistence_object_identifier as identifier');
		switch ($option) {
			case "examTypes":
				$queryBuilder
					->from('_OurBrand__quiz_domain_model_examtype', $option)
					->join(
						$option,
						'_OurBrand__quiz_domain_model_quiz',
						'quiz',
						'quiz.examtype = ' . $option . '.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_levels_join',
						'quiz_levels',
						'quiz_levels.quiz_quiz = quiz.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_subjects_join',
						'quiz_subjects',
						'quiz_subjects.quiz_quiz = quiz.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quizcategory',
						'category',
						'category.persistence_object_identifier = quiz.quizcategory'
					);
				break;
			case "subjects":
				$queryBuilder
					->from('_OurBrand__quiz_domain_model_subject', $option)
					->join(
						$option,
						'_OurBrand__quiz_domain_model_quiz_subjects_join',
						'quiz_subjects',
						'quiz_subjects.quiz_subject = ' . $option . '.persistence_object_identifier'
					)
					->join(
						'quiz_subjects',
						'_OurBrand__quiz_domain_model_quiz',
						'quiz',
						'quiz.persistence_object_identifier = quiz_subjects.quiz_quiz'
					)
					/*
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_examtype',
						'examtype',
						'examtype.persistence_object_identifier = quiz.examtype'
					)
					*/
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_levels_join',
						'quiz_levels',
						'quiz_levels.quiz_quiz = quiz.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quizcategory',
						'category',
						'category.persistence_object_identifier = quiz.quizcategory'
					);
				break;
			case "teamLevels":
				$queryBuilder
					->from('_OurBrand__quiz_domain_model_teamlevel', $option)
					->join(
						$option,
						'_OurBrand__quiz_domain_model_quiz_levels_join',
						'quiz_levels',
						'quiz_levels.quiz_teamlevel = ' . $option . '.persistence_object_identifier'
					)
					->join(
						'quiz_levels',
						'_OurBrand__quiz_domain_model_quiz',
						'quiz',
						'quiz.persistence_object_identifier = quiz_levels.quiz_quiz'
					)
					/*
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_examtype',
						'examtype',
						'examtype.persistence_object_identifier = quiz.examtype'
					)
					*/
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_subjects_join',
						'quiz_subjects',
						'quiz_subjects.quiz_quiz = quiz.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quizcategory',
						'category',
						'category.persistence_object_identifier = quiz.quizcategory'
					);
				break;
			case "categories":
				$queryBuilder
					->from('_OurBrand__quiz_domain_model_quizcategory', $option)
					->join(
						$option,
						'_OurBrand__quiz_domain_model_quiz',
						'quiz',
						'quiz.quizcategory = ' . $option . '.persistence_object_identifier'
					)
					/*
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_examtype',
						'examtype',
						'examtype.persistence_object_identifier = quiz.examtype'
					)
					*/
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_levels_join',
						'quiz_levels',
						'quiz_levels.quiz_quiz = quiz.persistence_object_identifier'
					)
					->leftJoin(
						'quiz',
						'_OurBrand__quiz_domain_model_quiz_subjects_join',
						'quiz_subjects',
						'quiz_subjects.quiz_quiz = quiz.persistence_object_identifier'
					);
				break;
		}

		if ($user->isWorker()) {
			$expression = $queryBuilder->expr()->eq('quiz.Quiz', 1);
		} else {
			$expression = $queryBuilder->expr()->andX(
				$queryBuilder->expr()->eq('quiz.Quiz', 1),
				$queryBuilder->expr()->eq('quiz.isDraft', 0)
			);
		}

		$constraints = array();
		$constraints[] = $queryBuilder->expr()->andX(
			$queryBuilder->expr()->orX(
				$queryBuilder->expr()->eq('quiz.creator', '"' . $user->getIdentifier() . '"'),
				$expression
			)
		);

		$constraints[] = $queryBuilder->expr()->andX(
			$queryBuilder->expr()->isNull('quiz.snapshotOf'),
			$queryBuilder->expr()->eq('quiz.type', intval($type)),
			$queryBuilder->expr()->eq('quiz.isDeleted', 0)
		);

		$expression = $queryBuilder->expr()->andX();
		foreach ($constraints as $expr) {
			$expression->add($expr);
		}

		$queryBuilder->where($expression);
		$queryBuilder = $this->applyFilters($queryBuilder, $filters);
		$queryBuilder->groupBy('identifier');
		$queryBuilder->orderBy('identifier', 'ASC');

		$data = $conn->fetchAll($queryBuilder->getSQL());
		$results = array();
		foreach ($data as $row) {
			$results[] = $row['identifier'];
		}

		return $results;
	}

	/**
	 * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
	 * @param array $filters
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function applyFilters($queryBuilder, $filters) {
		if (!empty($filters)) {
			foreach ($filters as $type => $data) {
				if($type != 'searchQuery') {
					$values = array();
					foreach($data as $list) {
						$values[] = $list['id'];
					}
				}
				switch ($type) {
					case "examTypes":
						if (!empty($values)) {
							$queryBuilder->andWhere(
								'examtype.persistence_object_identifier IN ("' . implode('","', array_values($values)) . '")'
							);
						}
						break;
					case "subjects":
						if (!empty($values)) {
							$queryBuilder->andWhere(
								'quiz_subjects.quiz_subject IN ("' . implode('","', array_values($values)) . '")'
							);
						}
						break;
					case "teamLevels":
						if (!empty($values)) {
							$queryBuilder->andWhere(
								'quiz_levels.quiz_teamlevel IN ("' . implode('","', array_values($values)) . '")'
							);
						}
						break;
					case "categories":
						if (!empty($values)) {
							$queryBuilder->andWhere('quiz.quizcategory IN ("' . implode('","', array_values($values)) . '")');
						}
						break;
					case "searchQuery":
						if (strlen(trim($data))) {
							$queryBuilder->andWhere('quiz.title LIKE "%' . addslashes($data) . '%"');
						}
						break;
				}
			}
		}

		return $queryBuilder;
	}
}

