<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */

use \_OurBrand_\Quiz\Domain\Model\Answer;

/**
 * Testcase for Answer
 */
class AnswerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function testGetters() {
		$answer = new Answer;

		$exercise = $this->getMock('\_OurBrand_\Quiz\Domain\Model\Exercise');
		$studentQuizSession = $this->getMock('\_OurBrand_\Quiz\Domain\Model\StudentQuizSession');
		$answerTime = $this->getMock('\DateTime');
		$score = 3;
		$answerData1 = $this->getMock('\_OurBrand_\Quiz\Domain\Model\AnswerData');
		$answerData2 = $this->getMock('\_OurBrand_\Quiz\Domain\Model\AnswerData');
		$answer->addAnswerData($answerData1);
		$answer->addAnswerData($answerData2);
		$answerDatas = $answer->getAnswerDatas();
		$status = 1;

		$answer->setExercise($exercise);
		$answer->setStudentQuizSession($studentQuizSession);
		$answer->setAnswerTime($answerTime);
		$answer->setScore($score);
		$answer->setAnswerDatas($answerDatas);
		$answer->setStatus($status);

		$this->assertSame($exercise, $answer->getExercise());
		$this->assertSame($studentQuizSession, $answer->getStudentQuizSession());
		$this->assertSame($answerTime, $answer->getAnswerTime());
		$this->assertSame($score, $answer->getScore());
		$this->assertSame($answerDatas, $answer->getAnswerDatas());
		$this->assertSame($status, $answer->getStatus());

		$answer->removeAnswerData($answerData2);
		$this->assertEquals(1, count($answer->getAnswerDatas()));
	}
}
?>