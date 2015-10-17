<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion;
use _OurBrand_\Quiz\Domain\Model\Exercise;

/**
 * Testcase for SpellingAndGrammarCommaQuestion
 */

class SpellingAndGrammarCommaQuestionTest extends \TYPO3\Flow\Tests\UnitTestCase {
	public function testGetExercise() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$exercise = new Exercise();
		$this->assertNull($sng->setExercise($exercise));
		$this->assertSame($exercise, $sng->getExercise());
	}

	public function testQuestionWithComma() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$question = 'aaa';
		$this->assertNull($sng->setQuestionWithComma($question));
		$this->assertSame($question, $sng->getQuestionWithComma());
	}

	public function testQuestionWithCommaWithSpace() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$question = ' aaa';
		$this->assertNull($sng->setQuestionWithComma($question));
		$this->assertSame('aaa', $sng->getQuestionWithComma());
	}

	public function testQuestionWithoutComma() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$question = 'aaa';
		$this->assertNull($sng->setQuestionWithoutComma($question));
		$this->assertSame($question, $sng->getQuestionWithoutComma());
	}

	public function testQuestionWithoutCommaWithSpace() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$question = ' aaa ';
		$this->assertNull($sng->setQuestionWithoutComma($question));
		$this->assertSame('aaa', $sng->getQuestionWithoutComma());
	}

	public function testGetNumber() {
		$sng = new SpellingAndGrammarCommaQuestion();
		$number = 1;
		$this->assertNull($sng->setNumber($number));
		$this->assertSame($number, $sng->getNumber());
	}
}