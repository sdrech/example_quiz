<?php
namespace _OurBrand_\Quiz\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

use _OurBrand_\Quiz\Domain\Model\Quiz;
use _OurBrand_\Quiz\Domain\Model\QuizSession;
use _OurBrand_\Quiz\Domain\Model\StudentQuizSession;
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarDictationExercise as DictationExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarInsertDictationExercise as InsertDictationExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarDictationSnippet as DictationSnippet;
use _OurBrand_\Quiz\Domain\Model\Exercises\SpellingAndGrammarDictationMarkedText as MarkedText;

/**
 * @Flow\Scope("singleton")
 */
class DictationCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\Inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizSessionRepository
	 * @Flow\Inject
	 */
	protected $quizSessionRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\StudentQuizSessionRepository
	 * @Flow\Inject
	 */
	protected $studentQuizSessionRepository;

	/**
	 * Generate testData
	 *
	 * This command inserts all the required data to start testing the student view of the Dictation exercise
	 *
	 * @return void
	 */
	public function generateCommand() {
		$quiz = new Quiz();
		$quiz->setTitle('Dictation Test 2');
		$quiz->setAuthor('Test Instructor');
		$quiz->setCreator('instructor');
		$quiz->setIsDraft(false);
		$quiz->setType(0);

		$exercise = new DictationExercise();
		$exercise->setTitle('Spelling and Grammer: Text Dictation');
		$exercise->setDescription('Description');

		$snippet = new DictationSnippet();
		$snippet->setText('<span class="textShow">This is a very</span> <span class="textmark">marvellous</span> horse');
		
		$markedText = new MarkedText();
		$markedText->setText('marvellous');
		
		$snippet->addMarkedText($markedText);

		$exercise->addSnippet($snippet);
		$quiz->addExercise($exercise);

		$this->quizRepository->add($quiz);

		//Create QuizSession
		$quizSession = new QuizSession();
		$quizSession->setQuiz($quiz);
		$quizSession->setInstructor('instructor');
		$quizSession->setStudentCanSeeSummaryAndReviewExercises(1);
		$quizSession->setShowGradeOnSummary(1);

		$this->quizSessionRepository->add($quizSession);

		//Create studentQuizSession
		$studentQuizSession = new StudentQuizSession();
		$studentQuizSession->setQuizSession($quizSession);
		$studentQuizSession->setStudent('student');

		$this->studentQuizSessionRepository->add($studentQuizSession);

	}
	
	/**
	 * Generate testData
	 *
	 * This command inserts all the required data to start testing the student view of the Dictation exercise
	 *
	 * @return void
	 */
	public function generateInsertCommand() {
		$quiz = new Quiz();
		$quiz->setTitle('Dictation Insert');
		$quiz->setAuthor('Test Instructor');
		$quiz->setCreator('instructor');
		$quiz->setIsDraft(false);
		$quiz->setType(0);

		$exercise = new InsertDictationExercise();
		$exercise->setTitle('Spelling and Grammer: Insert Dictation');
		$exercise->setDescription('Description');

		$snippet = new DictationSnippet();
		$snippet->setText('<span class="textShow">This is a very</span> <span class="textmark">marvellous</span> horse');
		
		$markedText = new MarkedText();
		$markedText->setText('marvellous');
		
		$snippet->addMarkedText($markedText);

		$exercise->addSnippet($snippet);
		
		$snippet = new DictationSnippet();
		$snippet->setText('<span class="textShow">This is another</span> <span class="textmark">sentence</span> horse');
		
		$markedText = new MarkedText();
		$markedText->setText('sentence');
		
		$snippet->addMarkedText($markedText);

		$exercise->addSnippet($snippet);
		
		$snippet = new DictationSnippet();
		$snippet->setText('<span class="textmark">Newest</span> <span class="textShow">more</span><span class="textmark">text</span>');
		
		$markedText = new MarkedText();
		$markedText->setText('Newest');
		
		$snippet->addMarkedText($markedText);
		
		$markedText = new MarkedText();
		$markedText->setText('text');
		
		$snippet->addMarkedText($markedText);

		$exercise->addSnippet($snippet);
		
		$quiz->addExercise($exercise);

		$this->quizRepository->add($quiz);

		//Create QuizSession
		$quizSession = new QuizSession();
		$quizSession->setQuiz($quiz);
		$quizSession->setInstructor('instructor');
		$quizSession->setStudentCanSeeSummaryAndReviewExercises(1);
		$quizSession->setShowGradeOnSummary(1);

		$this->quizSessionRepository->add($quizSession);

		//Create studentQuizSession
		$studentQuizSession = new StudentQuizSession();
		$studentQuizSession->setQuizSession($quizSession);
		$studentQuizSession->setStudent('student');

		$this->studentQuizSessionRepository->add($studentQuizSession);

	}
}
