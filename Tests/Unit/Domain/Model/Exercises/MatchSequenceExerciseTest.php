<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\MatchSequenceExercise;

/**
 * Testcase for MatchSequenceExercise
 */

class MatchSequenceExerciseTest extends \TYPO3\Flow\Tests\UnitTestCase {
	/**
	 * @return array
	 */
	public function setPhrasesDataProvider() {
		$texts = array(
			'empty all' => array(
				array()
			),
			'one phrase' => array(
				array('aaaa')
			),
			'two phrases' => array(
				array('aaaa', 'bbbbbb')
			),
			'three phrases' => array(
				array('aaaa', 'bbbbbb', 'ccccc')
			),
		);

		return $texts;
	}

	/**
	 * @dataProvider setPhrasesDataProvider
	 * @param array $originalPhrases
	 */
	public function testSetPhrasesFromArray($originalPhrases) {
		$exercise = new MatchSequenceExercise();
		$this->assertNull($exercise->setPhrasesFromArray($originalPhrases));
		$phrases = $exercise->getPhrases();
		$num = 0;
		foreach ($phrases as $phrase) {
			$this->assertSame($exercise, $phrase->getExercise());
			$this->assertSame($originalPhrases[$num], $phrase->getPhrase());
			$this->assertSame($num, $phrase->getNumber());
			$this->assertSame($num, $phrase->getSorting());
			$num++;
		}
	}

	/**
	 * @return array
	 */
	public function setRandomizeDataProvider() {
		$texts = array(
			'10 phrases' => array(
				array('aaaa', 'bbbbbb', 'ccccc', 'd', 'fsgdf', 'f', 'g', 'h', 'j', 'i')
			),
		);

		return $texts;
	}

	/**
	 * @dataProvider setRandomizeDataProvider
	 * @param array $originalPhrases
	 */
	public function testRandomize($originalPhrases) {
		$exercise = new MatchSequenceExercise();
		$this->assertNull($exercise->setPhrasesFromArray($originalPhrases));
		$exercise->randomize();
		$phrases = $exercise->getPhrases();
		$sortings = array();
		$allTheSame = true;
		foreach ($phrases as $phrase) {
			if ($phrase->getSorting() != $phrase->getNumber()) $allTheSame = false;
			$sortings[$phrase->getSorting()] = 1;
		}
		// count of result phrases is the same as original phrases, we have no phrases with the same sorting number
		$this->assertSame(count($sortings), count($originalPhrases));
		// sorting numbers differ from just order numbers
		$this->assertFalse($allTheSame);
	}

	/**
	 * @dataProvider setRandomizeDataProvider
	 * @param array $originalPhrases
	 */
	public function testPreOrderAnswers($originalPhrases) {
		$exercise = new MatchSequenceExercise();
		$this->assertNull($exercise->setPhrasesFromArray($originalPhrases));
		$exercise->randomize();
		$phrases = $exercise->getPhrases();
		$answers = $exercise->getPreorderedAnswers();
		// testing that phrases from getPreorderedAnswers in sorting orders
		foreach ($phrases as $phrase) {
			$this->assertSame($answers[$phrase->getSorting()], $phrase->getPhrase());
		}
		$i = 0;
		// testing that getPreorderedAnswers generate array exactly in index order (I mean, used ksort())
		foreach ($answers as $key => $value) {
			$this->assertSame($i++, $key);
		}
	}

	/**
	* @return array
	*/
	public function calculateScoreDataProvider() {
		$texts = array(
			'empty all' => array(
				array(),
				0
			),
			'one empty phrase' => array(
				array(''),
				0
			),
			'one correct phrase' => array(
				array('a'),
				1
			),
			'one wrong phrase' => array(
				array('a3'),
				0
			),
			'two correct phrase' => array(
				array('a', 'b'),
				2
			),
			'1 correct and 1 not' => array(
				array('a', 'с'),
				1
			),
			'3 correct' => array(
				array('a', 'b', 'c'),
				3
			),
			'3 correct in wrong order' => array(
				array('c', 'a', 'b'),
				0
			),
			'3 correct in wrong order 2' => array(
				array('c', 'b', 'a'),
				1
			),
		);

		return $texts;
	}

	/**
	 * @dataProvider calculateScoreDataProvider
	 * @param array $data
	 * @param integer $expected
	 */
	public function testCalculateScore($data, $expected) {
		$exercise = new MatchSequenceExercise();
		$arr = array('a', 'b', 'c', 'd', 'e', 'f');
		$exercise->setPhrasesFromArray($arr);

		$this->assertSame($expected, $exercise->calculateScoreForAnswers($data));
	}

	/**
	 * @return array
	 */
	public function calculateScore2DataProvider() {
		$texts = array(
			'empty all' => array(
				array(),
				0
			),
			'one empty phrase' => array(
				array(''),
				0
			),
			'one correct phrase' => array(
				array('a'),
				1
			),
			'one wrong phrase' => array(
				array('a3'),
				0
			),
			'two correct phrase' => array(
				array('a', 'b'),
				1
			),
			'1 correct and 1 not' => array(
				array('a', 'с'),
				1
			),
			'3 correct' => array(
				array('a', 'b', 'c'),
				2
			),
			'3 correct in wrong order' => array(
				array('c', 'a', 'b'),
				0
			),
			'3 correct in wrong order 2' => array(
				array('a', 'c', 'b'),
				1
			),
			'too much data' => array(
				array('a', 'c', 'b', 'f', 'h', 't', 'd', 'y'),
				1
			)
		);

		return $texts;
	}

	/**
	 * @dataProvider calculateScore2DataProvider
	 * @param array $data
	 * @param integer $expected
	 */
	public function testCalculateScore2($data, $expected) {
		$exercise = new MatchSequenceExercise();
		$arr = array('a', '', 'c', 'd', 'e', 'f');
		$exercise->setPhrasesFromArray($arr);

		$this->assertSame($expected, $exercise->calculateScoreForAnswers($data));
	}

	/**
	 * @return array
	 */
	public function isCompletedDataProvider() {
		$texts = array(
			'empty all' => array(
				array(),
				0
			),
			'one empty phrase' => array(
				array(''),
				0
			),
			'one correct phrase' => array(
				array('a'),
				0
			),
			'one wrong phrase' => array(
				array('a3'),
				0
			),
			'two correct phrase' => array(
				array('a', 'b'),
				0
			),
			'1 correct and 1 not' => array(
				array('a', 'с'),
				0
			),
			'3 correct' => array(
				array('a', 'b', 'c'),
				0
			),
			'6 correct' => array(
				array('a', 'b', 'c', 'd', 'e', 'f'),
				1
			),
			'6 correct in wrong order' => array(
				array('a', 'b', 'd', 'c', 'e', 'f'),
				1
			),
		);
		return $texts;
	}

	/**
	 * @dataProvider isCompletedDataProvider
	 * @param array $data
	 * @param integer $expected
	 */
	public function testIsCompleted($data, $expected) {
		$exercise = new MatchSequenceExercise();
		$arr = array('a', 'b', 'c', 'd', 'e', 'f');
		$exercise->setPhrasesFromArray($arr);
		$exercise->setDescription('sdfdsfds');
		$exercise->setTitle('dfgdfgfd');

		$this->assertSame($expected, $exercise->isCompleted($data));
	}

	/**
	 * @return array
	 */
	public function totalDataProvider() {
		$texts = array(
			'empty all' => array(
				array(),
				0,
				0
			),
			'one phrase' => array(
				array('a'),
				1,
				1
			),
			'two phrases' => array(
				array('a', 'b'),
				1,
				2
			),
			'two phrases with one empty' => array(
				array('a', ''),
				0,
				2
			),
			'3 phrases with doubler' => array(
				array('a', 'b', 'a'),
				0,
				3
			),
		);
		return $texts;
	}

	/**
	 * @dataProvider totalDataProvider
	 * @param array $data
	 * @param integer $isReadyForCompletion
	 * @param integer $maxScore
	 */
	public function testReadyFroCompetitionAndMaxScoreAndGetData($data, $isReadyForCompletion, $maxScore) {
		$exercise = new MatchSequenceExercise();
		$exercise->setPhrasesFromArray($data);
		$exercise->setDescription('sdfdsfds');
		$exercise->setTitle('dfgdfgfd');
		$this->assertSame($isReadyForCompletion, $exercise->getReadyForCompletion());
		$this->assertSame($maxScore, $exercise->getMaxScore());
	}

	/**
	 * @return array
	 */
	public function postCloneDataProvider() {
		$texts = array(
			'empty all' => array(
				array(),
				0,
				0
			),
			'one phrase' => array(
				array('a'),
				1,
				1
			),
			'two phrases' => array(
				array('a', 'b'),
				1,
				2
			),
			'two phrases with one empty' => array(
				array('a', ''),
				0,
				2
			),
			'3 phrases with doubler' => array(
				array('a', 'b', 'a'),
				0,
				3
			),
		);
		return $texts;
	}
}