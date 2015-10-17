<?php
namespace _OurBrand_\Quiz\Tests\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use _OurBrand_\Quiz\Domain\Model\Quiz;
use _OurBrand_\Quiz\Service\QuizService;

class QuizServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \_OurBrand_\Quiz\Service\QuizService
	 */
	protected $quizService;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->quizService = new QuizService();

	}


	/**
	 * @test
	 * @todo: make work with repository injection in quizservice
	 */
	public function makeSnapshotWorks() {
		$quiz = new Quiz();
		$snapshot = new Quiz();
		//$snapshot = $this->quizService->makeSnapshotAndPersist($quiz);
		$this->assertNotSame($quiz, $snapshot);
		//$this->assertSame($quiz, $snapshot->getSnapshotOf());

	}


	/**
	 * @test
	 */
	public function makeCopyWorks() {
		$quiz = new Quiz();
		$copy = $this->quizService->makeCopy($quiz);
		$this->assertNotSame($quiz, $copy);
		$this->assertSame($quiz, $copy->getCopyOf());

	}


	/**
	 * @test
	 */
	public function testCleanSnapshots() {
/*
		$mockQuery = $this->getMock('TYPO3\Flow\Persistence\QueryResultInterface');
		$mockQuery->expects($this->once())->method('count')->will($this->returnValue(0));
		$mockRepository = $this->getMock('_OurBrand_\Quiz\Domain\Repository\QuizSessionRepository');
		$mockRepository->expects($this->once())->method('findByQuiz')->with($this->anything())->will($this->returnValue($mockQuery));
		$this->inject($this->quizService, 'quizSessionRepository', $mockRepository);
*/
		$quiz = new Quiz();

		// Should clean 0 snapshots and return value should be -1 (as there is not snapshots)
		$result = $this->quizService->cleanSnapshots($quiz);
		$this->assertSame(-1, $result);



	}
}