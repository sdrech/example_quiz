<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercise;
use _OurBrand_\Quiz\Domain\Model\Quiz;

/**
 * Testcase for Quiz
 */
class QuizTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 *
	 * $this->getMock('\_OurBrand_\Quiz\Domain\Model\')
	 */
	public function testGetters() {
		$level = $this->getMock('\_OurBrand_\Quiz\Domain\Model\TeamLevel', array(), array('Test teamlevel', 1));
		$imageResource = $this->getMock('\_OurBrand_\Quiz\Domain\Model\ImageResource');
		$lastEdited = new \DateTime();

		$quiz = new \_OurBrand_\Quiz\Domain\Model\Quiz;

		$quiz->setType(1);
		$quiz->setDuration(300);
		$quiz->setTitle('Quiz Title');
		$quiz->setWasCopyOf('Original Title');
		$quiz->setAuthor('Tester');

		$quiz->setBannerImage($imageResource);
		$quiz->setIntroduction('Introduction test');
		$quiz->setDescription('Description test');
		$quiz->setCreator('Creator');
		$quiz->setIsLanguage(1);
		$quiz->setLastEdited($lastEdited);
		$quiz->setSharing(1);
		$quiz->setIsDraft(1);
		$quiz->setIsDeleted(0);

		$this->assertEquals(1, $quiz->getType());
		$this->assertEquals(300, $quiz->getDuration());
		$this->assertEquals('Quiz Title', $quiz->getTitle());
		$this->assertEquals('Original Title', $quiz->getWasCopyOf());
		$this->assertEquals('Tester', $quiz->getAuthor());

		$this->assertEquals($imageResource, $quiz->getBannerImage());
		$this->assertEquals('Introduction test', $quiz->getIntroduction());
		$this->assertEquals('Description test', $quiz->getDescription());
		$this->assertEquals('Creator', $quiz->getCreator());
		$this->assertEquals(1, $quiz->getIsLanguage());
		$this->assertEquals($lastEdited->getTimestamp(), $quiz->getLastEdited()->getTimestamp());
		$this->assertEquals(1, $quiz->getSharing());
		$this->assertEquals(1, $quiz->getIsDraft());
		$this->assertEquals(0, $quiz->getIsDeleted());

	}

	/**
	 * @test
	 */
	public function exerciseCanBeAddedToQuizAndIsNumberedCorrectly() {
		$quiz = new Quiz();
		$exercise = new Exercise();
		$quiz->addExercise($exercise);
		$exercise = new Exercise();
		$quiz->addExercise($exercise);

		$this->assertSame(2, $quiz->getExercises()->count());
		$this->assertSame(1, $exercise->getNumber());
	}


	/**
	 * @test
	 */
	public function prevExerciseWorks() {
		$quiz = new Quiz();
		$exercise1 = new Exercise();
		$exercise1->setTitle('firstExercise');
		$quiz->addExercise($exercise1);
		$exercise2 = new Exercise();
		$exercise2->setTitle('secondExercise');
		$quiz->addExercise($exercise2);

		$prevExercise = $quiz->findPrevExercise($exercise2);
		$this->assertSame('firstExercise', $prevExercise->getTitle());

		$nullExercise = $quiz->findPrevExercise($exercise1);
		$this->assertSame(false, $nullExercise);

	}


	/**
	 * @test
	 */
	public function nextExerciseWorks() {
		$quiz = new Quiz();
		$exercise1 = new Exercise();
		$exercise1->setTitle('firstExercise');
		$quiz->addExercise($exercise1);
		$exercise2 = new Exercise();
		$exercise2->setTitle('secondExercise');
		$quiz->addExercise($exercise2);

		$nextExercise = $quiz->findNextExercise($exercise1);
		$this->assertSame('secondExercise', $nextExercise->getTitle());

		$nullExercise = $quiz->findNextExercise($exercise2);
		$this->assertSame(false, $nullExercise);
	}

	/**
	 * @test
	 */
	public function clonedQuizContainsSameNumberOfExercisesWhichAreClones() {
		$quiz = $this->makeQuizWithExercises(200);
		$this->assertSame(200, $quiz->getExercises()->count());
		$newQuiz = clone $quiz;
		$newQuiz->postClone();
		$this->assertSame(200, $newQuiz->getExercises()->count());

		// Sanity check that assertSame can compare objects.
		$this->assertSame($quiz->getExercises()->first(), $quiz->getExercises()->first());
		$this->assertNotSame($quiz->getExercises()->get(0), $quiz->getExercises()->get(1));

		// Must not be the same ie. cloned.
		$this->assertNotSame($quiz->getExercises()->first(), $newQuiz->getExercises()->first());
	}

	public function testQuizCanCalculateDuration() {
		$quiz = $this->makeQuizWithExercises(10);
		$quiz->setType(2);
		$quiz->setDuration(123);
		foreach($quiz->getExercises() as $i => $exercise) {
			$exercise->setDuration($i);
		}

		// 0+1+2+3+4+5+6+7+8+9 = 45
		$this->assertSame((45*60), $quiz->calculateDuration()->getDuration());
	}

	public function testQuizCanCalculateDurationExam() {
		$quiz = $this->makeQuizWithExercises(10);
		$quiz->setType(0);
		$quiz->setDuration(123);
		foreach($quiz->getExercises() as $i => $exercise) {
			$exercise->setDuration($i);
		}

		$this->assertSame(123, $quiz->calculateDuration()->getDuration());
	}

	/**
	 * @param $numberOfExercises
	 *
	 * @return Quiz
	 */
	private function makeQuizWithExercises($numberOfExercises) {
		$quiz = new Quiz();
		for($i=0; $i<$numberOfExercises; $i++) {
			$exercise = new Exercise();
			$exercise->setTitle('Exercise: '.$i);
			$quiz->addExercise($exercise);
		}
		return $quiz;
	}
}