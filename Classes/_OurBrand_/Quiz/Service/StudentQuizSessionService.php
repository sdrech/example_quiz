<?php
namespace _OurBrand_\Quiz\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * Class StudentQuizSessionService
 *
 * @package _OurBrand_\Quiz\Service
 * @api
 * @Flow\Scope("singleton")
 *
 */
class StudentQuizSessionService {
	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\StudentQuizSessionRepository
	 * @Flow\Inject
	 */
	protected $studentQuizSessionRepository;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function updateProgress($studentQuizSession) {
		$studentQuizSession->setCurrentTime(new \DateTime());
		$this->studentQuizSessionRepository->update($studentQuizSession);
	}
}