<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

	/*                                                                        *
	 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".*
	 *                                                                        *
	 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercise;
use _OurBrand_\Quiz\Domain\Model\ExerciseCategory;
use _OurBrand_\Quiz\Domain\Model\ExerciseType;
use _OurBrand_\Quiz\Domain\Model\Quiz;

/**
 * Testcase for Exercise
 */
class ExerciseTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function testGetters() {
		$exerciseCategory = new ExerciseCategory('TestCategory', 1);
		$exerciseType = new ExerciseType($exerciseCategory, 'TestExerciseTypeKey', 'TestExerciseTypeDesc', 'TestExerciseTypeObject');

		$quiz = $this->getMock('\_OurBrand_\Quiz\Domain\Model\Quiz');
		$image = $this->getMock('\_OurBrand_\Quiz\Domain\Model\ImageResource');
		$pdfFile = $this->getMock('\_OurBrand_\Quiz\Domain\Model\FileResource');
		$soundFile = $this->getMock('\_OurBrand_\Quiz\Domain\Model\FileResource');
		$textContent = $this->getMock('\_OurBrand_\Quiz\Domain\Model\TextContent');
		$mediaContents = array('picture' => false, 'pdf' => false, 'audio' => false, 'text' => false);
		$answer = $this->getMock('\_OurBrand_\Quiz\Domain\Model\Answer');
		$skill = $this->getMock('\_OurBrand_\Quiz\Domain\Model\ExerciseSkill');
		$hint = 'This is a hint test';
		$explanation = 'This is an explanation hint';
		$minutes = 15;
		$seconds = 30;
		$difficulty = $this->getMock('\_OurBrand_\Quiz\Domain\Model\Difficulty');

		$exercise = new Exercise();
		$exercise->setQuiz($quiz);
		$exercise->setTitle('Title');
		$exercise->setDescription('Description');
		$exercise->setType($exerciseType);
		$exercise->setDuration(300);
		$exercise->setMaxScore(5);
		$exercise->setImage($image);
		$exercise->setPdfFile($pdfFile);
		$exercise->setSoundFile($soundFile);
		$exercise->setTextContent($textContent);
		foreach ($mediaContents as $key => $value) {
			$exercise->setMediaContent($key, $value);
		}
		$exercise->setAnswer($answer);
		$exercise->setExerciseSkill($skill);
		$exercise->setIsDraft(true);
		$exercise->setHint($hint);
		$exercise->setExplanation($explanation);
		$exercise->setMinutes($minutes);
		$exercise->setSeconds($seconds);
		$exercise->setDifficulty($difficulty);

		$this->assertEquals($quiz, $exercise->getQuiz());
		$this->assertEquals('Title', $exercise->getTitle());
		$this->assertEquals('Description', $exercise->getDescription());
		$this->assertEquals($exerciseType, $exercise->getType());
		$this->assertEquals(300, $exercise->getDuration());
		$this->assertEquals(5, $exercise->getMaxScore());
		$this->assertEquals($image, $exercise->getImage());
		$this->assertEquals($pdfFile, $exercise->getPdfFile());
		$this->assertEquals($soundFile, $exercise->getSoundFile());
		$this->assertEquals($textContent, $exercise->getTextContent());
		$this->assertEquals($mediaContents, $exercise->getMediaContents());
		$this->assertEquals($answer, $exercise->getAnswer());
		$this->assertEquals($skill, $exercise->getExerciseSkill());
		$this->assertEquals(true, $exercise->getIsDraft());
		$this->assertEquals($hint, $exercise->getHint());
		$this->assertEquals($explanation, $exercise->getExplanation());
		$this->assertEquals($minutes, $exercise->getMinutes());
		$this->assertEquals($seconds, $exercise->getSeconds());
		$this->assertEquals($difficulty, $exercise->getDifficulty());
	}


}
