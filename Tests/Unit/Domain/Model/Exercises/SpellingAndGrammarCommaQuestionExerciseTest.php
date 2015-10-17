<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarCommaQuestion;

/**
 * Testcase for SpellingAndGrammarCommaExercise
 */

class SpellingAndGrammarCommaExerciseTest extends \TYPO3\Flow\Tests\UnitTestCase {
	/**
	 * @return array
	 */
	public function questionDataProvider() {
		$questions = array(
			'empty array' => array(
				array('questionWithComma' => '', 'questionWithoutComma' => '')
			),
			'withComma only' => array(
				array('questionWithComma' => 'asdasds', 'questionWithoutComma' => '')
			),
			'withoutComma only' => array(
				array('questionWithComma' => '', 'questionWithoutComma' => 'f')
			),
			'full question' => array(
				array('questionWithComma' => 'question1', 'questionWithoutComma' => 'question2')
			)
		);
		return $questions;
	}


	/**
	 * @dataProvider questionDataProvider
	 * @param array $data
	 */
	public function testAddQuestion($data) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$question = new SpellingAndGrammarCommaQuestion();
		$question->setQuestionWithComma($data['questionWithComma']);
		$question->setQuestionWithoutComma($data['questionWithoutComma']);
		$this->assertNull($exercise->addQuestion($question));
		$this->assertSame($question, $exercise->getQuestions()->first());
	}


	/**
	 * @return array
	 */
	public function questionsDataProvider() {
		$questions = array(
			'empty array' => array(array(
				array('questionWithComma' => '', 'questionWithoutComma' => '')
			)),
			'withComma only' => array(array(
				array('questionWithComma' => 'asdasds', 'questionWithoutComma' => '')
			)),
			'withoutComma only' => array(array(
				array('questionWithComma' => '', 'questionWithoutComma' => 'f')
			)),
			'1 full question' => array(array(
				array('questionWithComma' => 'question1', 'questionWithoutComma' => 'question2')
			)),
			'1 full question and 1 partly question' => array(array(
				array('questionWithComma' => 'question1', 'questionWithoutComma' => 'question2'),
				array('questionWithComma' => '', 'questionWithoutComma' => 'question4')
			)),
			'2 full questions' => array(array(
				array('questionWithComma' => 'question1', 'questionWithoutComma' => 'question2'),
				array('questionWithComma' => 'question3', 'questionWithoutComma' => 'question4')
			)),
			'partly question in between' => array(array(
				array('questionWithComma' => 'question1', 'questionWithoutComma' => 'question2'),
				array('questionWithComma' => 'question3', 'questionWithoutComma' => ''),
				array('questionWithComma' => 'question5', 'questionWithoutComma' => 'question6')
			)),
		);
		return $questions;
	}


	/**
	 * @dataProvider questionsDataProvider
	 * @param array $data
	 */
	public function testSetQuestions($data) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$questions = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($data as $row) {
			$question = new SpellingAndGrammarCommaQuestion();
			$question->setQuestionWithComma($row['questionWithComma']);
			$question->setQuestionWithoutComma($row['questionWithoutComma']);
			$questions->add($question);
		}
		$this->assertNull($exercise->setQuestions($questions));
		$this->assertSame($questions, $exercise->getQuestions());
	}


	/**
	 * @dataProvider questionsDataProvider
	 * @param array $data
	 */
	public function testSetQuestionsFromArray($data) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$this->assertNull($exercise->setQuestionsFromArray($data));
		$questions = $exercise->getQuestions();
		$num = 0;
		$this->assertSame(count($data), count($questions));
		foreach ($data as $key => $row) {
			$this->assertSame($exercise, $questions[$key]->getExercise());
			$this->assertSame($row['questionWithComma'], $questions[$key]->getQuestionWithComma());
			$this->assertSame($row['questionWithoutComma'], $questions[$key]->getQuestionWithoutComma());
			$this->assertSame($num, $questions[$key]->getNumber());
			$num++;
		}
	}


	/**
	 * @return array
	 */
	public function answersDataProvider() {
		$questions = array(
			'1 question' => array(
				array(array('questionWithComma' => 'aaa', 'questionWithoutComma' => 'aaa'))
			),
			'2 questions' => array(
				array(
					array('questionWithComma' => 'q, w, e, r', 'questionWithoutComma' => 'q, w e r'),
					array('questionWithComma' => 'q, w, e, r', 'questionWithoutComma' => 'q, w e r'),
				),
			)
		);
		return $questions;
	}


	/**
	 * @dataProvider answersDataProvider
	 * @param array $data
	 */
	public function testGetAnswers($questions) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$exercise->setQuestionsFromArray($questions);
		$answers = $exercise->getAnswers();
		foreach ($questions as $i => $question) {
			$this->assertSame(str_replace(',', '', $question['questionWithComma']), $answers[$i]);
		}
	}


	/**
	 * @return array
	 */
	public function readyForCompletionDataProvider() {
		$exercises = array(
			'empty' => array(
				array(),
				array(),
				0
			),
			'no description' => array(
				array('title' => 'Title', 'description' => ''),
				array(),
				0
			),
			'no question' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(),
				0
			),
			'empty question' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => '', 'questionWithoutComma' => '')
				),
				0
			),
			'only questionWithComma is presents' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => 'a', 'questionWithoutComma' => '')
				),
				0
			),
			'only questionWithoutComma is presents' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => '', 'questionWithoutComma' => 'a')
				),
				0
			),
			'1 question' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => 'aaa bbb, ccc', 'questionWithoutComma' => 'aaa bbb ccc')
				),
				1
			),
			'incorrect questions' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => 'aaa bbb ccc', 'questionWithoutComma' => 'dddd')
				),
				0
			),
			'1 full question and 1 partly question' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => 'aaa', 'questionWithoutComma' => 'aaa'),
					array('questionWithComma' => 'fddfdfdf', 'questionWithoutComma' => '')
				),
				0
			),
			'2 full questions' => array(
				array('title' => 'Title', 'description' => 'Description'),
				array(
					array('questionWithComma' => 'aaa, rrr, yyy', 'questionWithoutComma' => 'aaa rrr, yyy'),
					array('questionWithComma' => 'eee rrr ttt', 'questionWithoutComma' => 'eee rrr ttt')
				),
				1
			),
		);
		return $exercises;
	}


	/**
	 * @dataProvider readyForCompletionDataProvider
	 * @param array $data1
	 * @param array $data2
	 * @param integer $expected
	 */
	public function testGetReadyForCompletion($data1, $data2, $expected) {
		$exercise = new SpellingAndGrammarCommaExercise();
		if (!empty($data1)) {
			$exercise->setTitle($data1['title']);
			$exercise->setDescription($data1['description']);
		}

		if (!empty($data2)) {
			foreach ($data2 as $row) {
				$question = new SpellingAndGrammarCommaQuestion();
				$question->setQuestionWithComma($row['questionWithComma']);
				$question->setQuestionWithoutComma($row['questionWithoutComma']);
				$exercise->addQuestion($question);
			}
		}

		$this->assertSame($expected, $exercise->getReadyForCompletion());
	}


	/**
	 * @return array
	 */
	public function isCompletedDataProvider() {
		$questions = array(
			'no mode' => array(
				'',
				array('a', 'b', 'c'),
				3,
				0
			),
			'no answers' => array(
				'withComma',
				array(),
				3,
				0
			),
			'little answers' => array(
				'withComma',
				array('a'),
				3,
				0
			),
			'all is ok' => array(
				'withComma',
				array('a', 'b', 'c'),
				3,
				1
			)
		);
		return $questions;
	}


	/**
	 * @dataProvider isCompletedDataProvider
	 * @param string $mode withComma or withoutComma
	 * @param array $answers
	 * @param integer $maxScore
	 * @param integer $expected
	 */
	public function testIsCompleted($mode, $answers, $maxScore, $expected) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$exercise->setMaxScore($maxScore);
		$answers = array('answers' => $answers, 'mode' => $mode);
		$this->assertSame($expected, $exercise->isCompleted($answers));
	}


	/**
	 * @return array
	 */
	public function calculateScoreForAnswersDataProvider() {
		$questions = array(
			'no mode' => array(
				'',
				array('a', 'b', 'c'),
				0
			),
			'no answers' => array(
				'withComma',
				array(),
				0
			),
			'little answers' => array(
				'withComma',
				array('a'),
				0
			),
			'1 answer' => array(
				'withComma',
				array('q, w, e'),
				1
			),
			'2 answers' => array(
				'withoutComma',
				array('q, w e', 'qqq www eee'),
				2
			),
			'2 answers' => array(
				'withoutComma',
				array('q, w e', 'qqq www eee', ''),
				2
			),
			'4 answers' => array(
				'withoutComma',
				array('q, w e', 'qqq www eee', 'sdfsdf', 'dgfffd'),
				2
			)
		);
		return $questions;
	}


	/**
	 * @dataProvider calculateScoreForAnswersDataProvider
	 * @param string $mode withComma or withoutComma
	 * @param array $answers
	 * @param integer $expected
	 */
	public function testCalculateScoreForAnswers($mode, $answers, $expected) {
		$exercise = new SpellingAndGrammarCommaExercise();
		$data = array(
			array(
				'questionWithComma' => 'q, w, e',
				'questionWithoutComma' => 'q, w e'
			),
			array(
				'questionWithComma' => 'qqq www, eee',
				'questionWithoutComma' => 'qqq www eee'
			),
			array(
				'questionWithComma' => 'rrr, ttt eee www',
				'questionWithoutComma' => 'rrr ttt eee www'
			)
		);
		$exercise->setQuestionsFromArray($data);
		$answers = array('answers' => $answers, 'mode' => $mode);
		$this->assertSame($expected, $exercise->calculateScoreForAnswers($answers));
	}
}