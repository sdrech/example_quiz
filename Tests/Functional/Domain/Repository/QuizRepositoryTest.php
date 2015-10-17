<?php
namespace _OurBrand_\Quiz\Tests\Functional\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;

/**
 */
class QuizRepositoryTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = true;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\My\Domain\Model\User
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $filterOptions;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->quizRepository = $this->objectManager->get('_OurBrand_\Quiz\Domain\Repository\QuizRepository');

		$user = $this->getMock('\_OurBrand_\My\Domain\Model\User', array('getIdentifier'));
		$user->expects($this->any())->method('getIdentifier')->will($this->returnValue('tester'));
		$this->user = $user;

		// Set up filter test data
		$this->setUpSubjects();
		$this->setUpGradeLevels();
		$this->setUpCategories();
		$this->setUpExercise();

		$this->setUpQuiz();
	}

	public function setUpSubjects() {
		$subjectRepository = $this->objectManager->get('_OurBrand_\Quiz\Domain\Repository\SubjectRepository');
		$subject = new \_OurBrand_\Quiz\Domain\Model\Subject;
		$subject->setTitle('Math');
		$subjectRepository->add($subject);
		$this->filterOptions['subjects'][$subject->getTitle()] = $subject;

		$subject = new \_OurBrand_\Quiz\Domain\Model\Subject;
		$subject->setTitle('English');
		$subjectRepository->add($subject);
		$this->filterOptions['subjects'][$subject->getTitle()] = $subject;

		$this->persistenceManager->persistAll();
	}

	
	public function setUpExercise() {
		$exerciseRepository = $this->objectManager->get('_OurBrand_\Quiz\Domain\Repository\ExerciseRepository');
		$exercise = new \_OurBrand_\Quiz\Domain\Model\Exercise();
		$exercise->setTitle('Test Exercise');
		$this->exercises['1'] = $exercise;
		
		$exerciseRepository->add($exercise);
		$this->persistenceManager->persistAll();
		
	}

	public function setUpQuiz() {
		$quiz = new \_OurBrand_\Quiz\Domain\Model\Quiz();
		$quiz->setAuthor('Tester');
		$quiz->setIsDraft(false);
		$quiz->setIsDeleted(false);
		$quiz->setCreator('tester');
		$quiz->setType(0);
		$quiz->setTitle('Test Quiz');

		$subject = $this->filterOptions['subjects']['Math'];
		$quiz->addSubject($subject);
		$level1 = $this->filterOptions['teamLevels']['4'];
		$quiz->addLevel($level1);
		$level2 = $this->filterOptions['teamLevels']['5'];
		$quiz->addLevel($level2);
		$category = $this->filterOptions['categories']['Geometry'];
		$quiz->setCategory($category);
		
		$quiz->addExercise($this->exercises['1']);
		
		$this->quizRepository->add($quiz);
		
		$quiz = new \_OurBrand_\Quiz\Domain\Model\Quiz();
		$quiz->setAuthor('Tester');
		$quiz->setIsDraft(false);
		$quiz->setIsDeleted(false);
		$quiz->setCreator('tester');
		$quiz->setType(0);
		$quiz->setTitle('Test Quiz2');

		$subject = $this->filterOptions['subjects']['Math'];
		$quiz->addSubject($subject);
		$level1 = $this->filterOptions['teamLevels']['4'];
		$quiz->addLevel($level1);
		$level2 = $this->filterOptions['teamLevels']['5'];
		$quiz->addLevel($level2);
		$category = $this->filterOptions['categories']['Geometry'];
		$quiz->setCategory($category);
		
		$this->quizRepository->add($quiz);

		$this->persistenceManager->persistAll();
	}

}
