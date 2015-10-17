<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */

use \_OurBrand_\Quiz\Domain\Model\AnswerData;

/**
 * Testcase for AnswerData
 */
class AnswerDataTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function testGetters() {
		$answerData = new AnswerData;

		$answer = $this->getMock('\_OurBrand_\Quiz\Domain\Model\Answer');
		$data = "This is an answerData test";

		$answerData->setAnswer($answer);
		$answerData->setData($data);

		$this->assertSame($answer, $answerData->getAnswer());
		$this->assertSame($data, $answerData->getData());
	}
}
?>