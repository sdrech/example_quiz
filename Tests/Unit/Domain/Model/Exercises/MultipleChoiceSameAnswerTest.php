<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceSameAnswerExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoiceQuestion;
use _OurBrand_\Quiz\Domain\Model\Exercises\MultipleChoicePossibleAnswer;

/**
 * Testcase for MultipleChoiceSameAnswerExercise
 */
class MultipleChoiceSameAnswerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function setQuestionsWorks() {
		$question1 = new MultipleChoiceQuestion();
		$question2 = new MultipleChoiceQuestion();

		$questions = new \Doctrine\Common\Collections\ArrayCollection();
		$questions->add($question1);
		$questions->add($question2);
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setQuestions($questions);
		$this->assertEquals(2, $exercise->getMaxScore());
	}

	/**
	 * @test
	 */
	public function setQuestionsFromArrayWorks() {
		$data = array(
			array(
	 			'text' => 'Question text 1',
	 			'hint' => 'Question hint 1',
	 			'possibleAnswers' => array(
					array(
						'text' => 'Answer 1',
						'correctAnswer' => true
					),
					array(
						'text' => 'Answer 2',
						'correctAnswer' => false
					)
	 			)
		 	),
			array(
				'text' => 'Question text 2',
				'hint' => 'Question hint 2',
				'possibleAnswers' => array(
					array(
						'text' => 'Answer 1',
						'correctAnswer' => false
					),
					array(
						'text' => 'Answer 2',
						'correctAnswer' => true
					)
				)
			),
			array(
				'text' => 'Question text 3',
				'hint' => 'Question hint 3',
				'possibleAnswers' => array(
					array(
						'text' => 'Answer 1',
						'correctAnswer' => false
					),
					array(
						'text' => 'Answer 2',
						'correctAnswer' => true
					)
				)
			),
		);

		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setQuestionsFromArray($data);
		$this->assertEquals(3, $exercise->getMaxScore());
	}

	/**
	 * @test
	 */
	public function updateQuestionWorks() {
		$exercise = new MultipleChoiceSameAnswerExercise();

		$question = new MultipleChoiceQuestion();
		$question->setText('Question A');
		$rightAnswer = new MultipleChoicePossibleAnswer();
		$rightAnswer->setNumber(0);
		$rightAnswer->setText('Answer 1');
		$rightAnswer->setIsCorrectAnswer(true);
		$question->addPossibleAnswer($rightAnswer);
		$wrongAnswer = new MultipleChoicePossibleAnswer();
		$wrongAnswer->setNumber(1);
		$wrongAnswer->setText('Answer 2');
		$wrongAnswer->setIsCorrectAnswer(false);
		$question->addPossibleAnswer($wrongAnswer);
		$exercise->addQuestion($question);

		$question->setText('Question B');
		$exercise->updateQuestion($question);

		$this->assertEquals('Question B', $exercise->getQuestions()->first()->getText());
	}

	/**
	 * @test
	 */
	public function removeQuestionWorks() {
		$exercise = new MultipleChoiceSameAnswerExercise();

		$question = new MultipleChoiceQuestion();
		$question->setText('Question A');
		$rightAnswer = new MultipleChoicePossibleAnswer();
		$rightAnswer->setNumber(0);
		$rightAnswer->setText('Answer 1');
		$rightAnswer->setIsCorrectAnswer(true);
		$question->addPossibleAnswer($rightAnswer);
		$wrongAnswer = new MultipleChoicePossibleAnswer();
		$wrongAnswer->setNumber(1);
		$wrongAnswer->setText('Answer 2');
		$wrongAnswer->setIsCorrectAnswer(false);
		$question->addPossibleAnswer($wrongAnswer);
		$exercise->addQuestion($question);

		$exercise->removeQuestion($question);

		$this->assertEquals(0, $exercise->getMaxScore());
	}

	/**
	 * @return array
	 */
	public function isCompletedDataProvider() {
		$answers = array(
			"isCompleted - score 1 with no answer" 		=> array(1, array(), 0),
			"isCompleted - score 1 with empty answer" 	=> array(1, array(0 => ''), 0),
			"isCompleted - score 1 with 1 answer" 		=> array(1, array(0 => '0'), 1),
			"isCompleted - score 2 with no answers" 	=> array(2, array(), 0),
			"isCompleted - score 2 with 1 answer" 		=> array(2, array(0 => '0'), 0),
			"isCompleted - score 2 with 2 answers" 		=> array(2, array(0 => '0', 1 => '1'), 1),
		);
		return $answers;
	}
	/**
	 * @test
	 * @dataProvider isCompletedDataProvider
	 * @param array $maxScore
	 * @param array $answers
	 * @param int $expected
	 */
	public function isCompletedWorks($maxScore, $answers, $expected) {
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setMaxscore($maxScore);
		$this->assertSame($expected, $exercise->isCompleted($answers));
	}

	/**
	 * @return array
	 */
	public function answerDataProvider() {
		$answers = array(
			"calculateScore - wrong structure"				=> array(0, array(-1=>1)),
			"calculateScore - none existing question"		=> array(0, array(3=>1)),
			"calculateScore - none existing answer"			=> array(0, array(1=>5)),
			"calculateScore - no answers" 					=> array(0, array()),
			"calculateScore - all wrong" 					=> array(0, array(0=>1, 1=>1)),
			"calculateScore - all right" 					=> array(2, array(0=>0, 1=>0)),
			"calculateScore - first right, second wrong" 	=> array(1, array(0=>0, 1=>1)),
			"calculateScore - first wrong, second right" 	=> array(1, array(0=>1, 1=>0)),
		);
		return $answers;
	}
	/**
	 * Test two questions. Max score = 2
	 * @test
	 * @dataProvider answerDataProvider
	 * @param int $expected
	 * @param array $answers
	 */
	public function calculateScoreForAnswersWorks($expected, $answers) {
		$exercise = new MultipleChoiceSameAnswerExercise();

		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$rightAnswer = new MultipleChoicePossibleAnswer();
		$rightAnswer->setNumber(0);
		$rightAnswer->setText('Answer');
		$rightAnswer->setIsCorrectAnswer(true);
		$question->addPossibleAnswer($rightAnswer);
		$wrongAnswer = new MultipleChoicePossibleAnswer();
		$wrongAnswer->setNumber(1);
		$wrongAnswer->setText('Answer');
		$wrongAnswer->setIsCorrectAnswer(false);
		$question->addPossibleAnswer($wrongAnswer);
		$exercise->addQuestion($question);

		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$rightAnswer = new MultipleChoicePossibleAnswer();
		$rightAnswer->setNumber(0);
		$rightAnswer->setText('Answer');
		$rightAnswer->setIsCorrectAnswer(true);
		$question->addPossibleAnswer($rightAnswer);
		$rightAnswer = new MultipleChoicePossibleAnswer();
		$rightAnswer->setNumber(1);
		$rightAnswer->setText('Answer');
		$rightAnswer->setIsCorrectAnswer(false);
		$question->addPossibleAnswer($rightAnswer);
		$exercise->addQuestion($question);

		$this->assertSame($expected, $exercise->calculateScoreForAnswers($answers));
	}

	/**
	 * @return array
	 */
	public function readyForCompletionDataProvider() {
		$exercises = array();

		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercises['empty'] = array($exercise, 0);

		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercises['no question'] = array($exercise, 0);

		$question = new MultipleChoiceQuestion();
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->addQuestion($question);
		$exercises['empty question'] = array($exercise, 0);

		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->addQuestion($question);
		$exercises['no answers'] = array($exercise, 0);

		$answer = new MultipleChoicePossibleAnswer();
		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$question->addPossibleAnswer($answer);
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->addQuestion($question);
		$exercises['empty answer'] = array($exercise, 0);

		$answer = new MultipleChoicePossibleAnswer();
		$answer->setText('Answer');
		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$question->addPossibleAnswer($answer);
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->addQuestion($question);
		$exercises['only one answer'] = array($exercise, 0);

		$answer = new MultipleChoicePossibleAnswer();
		$answer->setText('Answer');
		$answer2 = new MultipleChoicePossibleAnswer();
		$answer2->setText('Answer');
		$question = new MultipleChoiceQuestion();
		$question->setText('Question');
		$question->addPossibleAnswer($answer);
		$question->addPossibleAnswer($answer2);
		$exercise = new MultipleChoiceSameAnswerExercise();
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->addQuestion($question);
		$exercises['ready'] = array($exercise, 1);

		return $exercises;
	}

	/**
	 * @dataProvider readyForCompletionDataProvider
	 * @test
	 */
	public function getReadyForCompletionWorks($exercise, $expected) {
		$this->assertSame($expected, $exercise->getReadyForCompletion());
	}
}

