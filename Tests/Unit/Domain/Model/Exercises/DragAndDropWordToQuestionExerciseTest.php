<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropWordToQuestionExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\DragAndDropQuestion;

/**
 * Testcase for DragAndDropWordToQuestionExercise
 */

class DragAndDropWordToQuestionExerciseTest extends \TYPO3\Flow\Tests\UnitTestCase {
	/**
	 * @return array
	 */
	public function isCompletedDataProvider() {
		$answers = array(
			'isCompleted - score 1 with empty array' => array(1, array(), 0),
			'isCompleted - score 1 with 0 answer' => array(1, array(''), 0),
			'isCompleted - score 1 with 1 answer' => array(1, array('a'), 1),
			'isCompleted - score 2 with 0 answer' => array(2, array('',''), 0),
			'isCompleted - score 2 with 1 answer' => array(2, array('a', ''), 0),
			'isCompleted - score 2 with 1 answer' => array(2, array('', 'b'), 0),
			'isCompleted - score 2 with 2 answer' => array(2, array('a','b'), 1),
		);
		return $answers;
	}

	/**
	 * @dataProvider isCompletedDataProvider
	 * @param array $maxScore
	 * @param array $answers
	 * @param int $expected
	 */
	public function testIsComplete($maxScore, $answers, $expected) {
		$exercise = new DragAndDropWordToQuestionExercise();
		$exercise->setMaxScore($maxScore);
		$this->assertSame($expected, $exercise->isCompleted($answers));
	}

	public function testAddQuestion() {
		$exercise = new DragAndDropWordToQuestionExercise();
		$question = new DragAndDropQuestion();
		$question->setQuestion('qqqq');
		$question->setAnswer('fffffff');
		$this->assertNull($exercise->addQuestion($question));
	}

	/**
	 * @return array
	 */
	public function questionDataProvider() {
		$answers = array(
			'empty array' => array(array(
				array('question' => '', 'answer' => '')
			)),
			'empty answer' => array(array(
				array('question' => 'question1', 'answer' => '')
			)),
			'empty question' => array(array(
				array('question' => '', 'answer' => 'answer1')
			)),
			'1 question' => array(array(
				array('question' => 'q1', 'answer' => 'a1')
			)),
			'2 questions' => array(array(
				array('question' => 'q1', 'answer' => 'a1'),
				array('question' => 'q2', 'answer' => 'a2')
			))
		);
		return $answers;
	}

	/**
	 * @dataProvider questionDataProvider
	 * @param array $data
	 */
	public function testGetQuestions($data) {
		$exercise = new DragAndDropWordToQuestionExercise();
		$questions = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($data as $row) {
			$question = new DragAndDropQuestion();
			$question->setQuestion($row['question']);
			$question->setAnswer($row['answer']);
			$questions->add($question);
		}
		$this->assertNull($exercise->setQuestions($questions));
		$this->assertSame($questions, $exercise->getQuestions());
	}

	/**
	 * @dataProvider questionDataProvider
	 * @param array $data
	 */
	public function testGetQuestionsFromArray($data) {
		$exercise = new DragAndDropWordToQuestionExercise();
		$this->assertNull($exercise->setQuestionsFromArray($data));
		$questions = $exercise->getQuestions();
		$num = 0;
		foreach ($data as $key => $row) {
			$this->assertSame($exercise, $questions[$key]->getExercise());
			$this->assertSame($row['question'], $questions[$key]->getQuestion());
			$this->assertSame($row['answer'], $questions[$key]->getAnswer());
			$this->assertSame($num, $questions[$key]->getNumber());
			$this->assertSame($num, $questions[$key]->getSorting());
			$num++;
		}
	}

	/**
	 * @return array
	 */
	public function extraWordsDataProvider() {
		$answers = array(
			'0 extra words' => array(array('')),
			'1 extra words' => array(array('a')),
			'2 extra words' => array(array('a', 'b')),
		);
		return $answers;
	}

	/**
	 * @dataProvider extraWordsDataProvider
	 * @param array $data
	 */
	public function testSetExtraWordsFromArray($data) {
		$exercise = new DragAndDropWordToQuestionExercise();
		$this->assertNull($exercise->setExtraWordsFromArray($data));
		$questions = $exercise->getQuestions();
		$num = 0;
		foreach ($data as $key => $row) {
			$this->assertSame($exercise, $questions[$key]->getExercise());
			$this->assertSame(null, $questions[$key]->getQuestion());
			$this->assertSame($row, $questions[$key]->getAnswer());
			$this->assertSame($num, $questions[$key]->getNumber());
			$this->assertSame($num, $questions[$key]->getSorting());
			$num++;
		}

		$this->assertEquals(count($data), count($exercise->getExtraWords()));
	}
}