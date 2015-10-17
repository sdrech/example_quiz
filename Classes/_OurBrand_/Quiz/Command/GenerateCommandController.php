<?php
namespace _OurBrand_\Quiz\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use _OurBrand_\Quiz\Domain\Model\Subject;
use _OurBrand_\Quiz\Domain\Model\TeamLevel;
use _OurBrand_\Quiz\Domain\Model\Grade;
use _OurBrand_\Quiz\Domain\Model\Tag;
use _OurBrand_\Quiz\Domain\Model\Category;
use _OurBrand_\Quiz\Domain\Model\WordClass;
use _OurBrand_\Quiz\Domain\Model\ErrorType;
use _OurBrand_\Quiz\Domain\Model\ExamType;
use _OurBrand_\Quiz\Domain\Model\ContentCategory;

/**
 * @Flow\Scope("singleton")
 */
class GenerateCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\SubjectRepository
	 * @Flow\Inject
	 */
	protected $subjectRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseSkillCategoryRepository
	 * @Flow\Inject
	 */
	protected $exerciseSkillCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseSkillRepository
	 * @Flow\Inject
	 */
	protected $exerciseSkillRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ContentCategoryRepository
	 * @Flow\Inject
	 */
	protected $contentCategoryRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TeamLevelRepository
	 * @Flow\Inject
	 */
	protected $teamLevelRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\GradeRepository
	 * @Flow\Inject
	 */
	protected $gradeRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TagRepository
	 * @Flow\Inject
	 */
	protected $tagRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\WordClassRepository
	 * @Flow\Inject
	 */
	protected $wordClassRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\DifficultyRepository
	 * @Flow\Inject
	 */
	protected $difficultyRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ErrorTypeRepository
	 * @Flow\Inject
	 */
	protected $errorTypeRepository;


	/**
	 * Generate new subjects
	 *
	 * This command inserts new Subject records into the database.
	 */
	public function subjectsCommand() {

		$subjects = $this->generateSubjectsData();


		if (is_array($subjects) && count($subjects) > 0) {

			$oldSubjects = $this->subjectRepository->findAll();
			$oldNotInNew = array();
			foreach ($oldSubjects as $oldSubject) {

				$i = 0;
				foreach($subjects as $subject){
					if(trim(strtolower($subject['title']))==trim(strtolower($oldSubject->getTitle()))){
						$i++;
					}
				}
				if($i===0){
					$oldNotInNew[] = $oldSubject;
				}
			}
			if(is_array($oldNotInNew)&&count($oldNotInNew)>0){
				foreach($oldNotInNew as $oldSubject){
					$this->outputLine('Subject with name "%s" is in database, but is NOT in the generate list',array($oldSubject->getTitle()));
				}
			}

			foreach ($subjects as $subject) {

				if (isset($subject['title']) == false) {
					continue;
				}

				$title = trim($subject['title']);

				if ($title !== '' && $this->subjectRepository->countByTitle($title) == 0) {

					$isLanguage = intval($subject['isLanguage']);

					$subject = new Subject();
					$subject->setTitle($title);
					$subject->setIsLanguage($isLanguage);
					// @TODO : translations?

					$this->subjectRepository->add($subject);

					$this->outputLine('Subject with name "%s" added to the database.',array($title));
				} else {
					$this->outputLine('Subject with name "%s" found, skipping..', array($title));
				}
			}
		}
	}

	/**
	 * Generate a new ContentCategory
	 *
	 * This command inserts a new ContentCategory record into the database. Mostly used for development while management
	 * tools for content categories are not yet available
	 *
	 * @param string $translationId The TranslationId of the content category to generate
	 * @return void
	 */
	public function contentCategoryCommand($translationId) {
		$contentCategory = new ContentCategory($translationId);

		$this->contentCategoryRepository->add($contentCategory);

		$this->outputLine('ContentCategory with title "%s" added to the database.', array($translationId));
	}

	/**
	 * Generate a new TeamLevel
	 *
	 * This command inserts a new TeamLevel record into the database. Mostly used for development while management
	 * tools for levels are not yet available
	 *
	 * @param string $name The name of the level to generate
	 * @return void
	 */
	public function levelCommand($name, $level) {
		$teamLevel = new TeamLevel($name, $level);

		$this->teamLevelRepository->add($teamLevel);

		$this->outputLine('TeamLevel with name "%s" added to the database.', array($name));
	}

	/**
	 * Generate a new Grade
	 *
	 * This command inserts a new Grade record into the database. Mostly used for development while management
	 * tools for grades are not yet available
	 *
	 * @param string $value The value of the grade to generate
	 * @param int $sorting The sorting of the grade
	 * @return void
	 */
	public function gradeCommand($value, $sorting) {
		$grade = new Grade($value, $sorting);
		$this->gradeRepository->add($grade);

		$this->outputLine('Grade with value "%s" added to the database.', array($value));
	}

	/**
	 * Generate a new Tag
	 *
	 * This command inserts a new Tag record into the database. Mostly used for development while management
	 * tools for tags are not yet available
	 *
	 * @param string $value The value of the tag to generate
	 * @return void
	 */
	public function tagCommand($value) {
		$tag = new Tag($value);

		$this->tagRepository->add($tag);

		$this->outputLine('Tag with value "%s" added to the database.', array($value));
	}

	/**
	 * Generate a new Word Class
	 *
	 * This command inserts a new Word Class record into the database. Mostly used for development while management
	 * tools for word classes are not yet available
	 *
	 * @param $title
	 * @param $sorting
	 * @param $bitwise
	 * @param $language
	 * @return void
	 */
	public function wordClassCommand($title, $sorting, $bitwise, $language) {
		$wordClass = new WordClass($title, $sorting, $bitwise, $language);

		$this->wordClassRepository->add($wordClass);

		$this->outputLine('Word Class with name "%s" added to the database.', array($title));
	}

	/**
	 * Generate a new Difficulty
	 *
	 * This command inserts a new Difficulty record into the database. Mostly used for development while management
	 * tools for difficulties are not yet available
	 *
	 * @param $title
	 * @param $sorting
	 * @param $bitwise
	 * @param $language
	 * @return void
	 */
	public function difficultyCommand($title, $sorting, $bitwise, $language) {
		$difficulty = new \_OurBrand_\Quiz\Domain\Model\Difficulty();
		$difficulty->setTitle($title);
		$difficulty->setSorting($sorting);
		$difficulty->setBitwise($bitwise);
		$difficulty->setLanguage($language);

		$this->difficultyRepository->add($difficulty);

		$this->outputLine('Difficulty with name "%s" added to the database.', array($title));
	}

	/**
	 * Generate a new Error Type
	 *
	 * This command inserts a new Error Type record into the database. Mostly used for development while management
	 * tools for error types is not yet available
	 *
	 * @param $title
	 * @param $description
	 * @return void
	 */
	public function errorTypeCommand($title, $description) {
		$errorType = new ErrorType($title, $description);

		$this->errorTypeRepository->add($errorType);

		$this->outputLine('Error Type with title "%s" added to the database.', array($title));
	}

	/**
	 * Generate testData
	 *
	 * This command inserts new TeamLevel and Subject records into the database. Mostly used for development while management
	 * tools for levels are not yet available
	 *
	 * @return void
	 */
	public function testDataCommand() {
		$data = $this->generateTestdata();

		foreach ($data as $type => $values) {
			switch ($type) {
				case "contentCategories":
					foreach ($values as $key => $value) {
						$this->contentCategoryCommand($value);
					}
					break;
				case "levels":
					foreach ($values as $key => $value) {
						$this->levelCommand($value, $key);
					}
					break;
				case "grades":
					foreach ($values as $sorting => $value) {
						$this->gradeCommand($value, $sorting);
					}
					break;
				case "tags":
					foreach ($values as $value) {
						$this->tagCommand($value);
					}
					break;
				case "exerciseSkillCategory":
					foreach ($values as $value) {
						$this->exerciseSkillCategoryCommand($value);
					}
					break;
				case "wordClasses":
					foreach ($values as $value) {
						$this->wordClassCommand($value[0], $value[1], $value[2], 0);
					}
					break;
				case "difficulty":
					foreach ($values as $value) {
						$this->difficultyCommand($value[0], $value[1], $value[2], 0);
					}
					break;
				case "errorTypes":
					foreach ($values as $value) {
						$this->errorTypeCommand($value[0], $value[1]);
					}
			}
		}

		$this->outputLine('All test data added to the database');
	}

	/**
	 * @return array
	 */
	public function generateTestdata() {
		$data = array(
			"contentCategories" => array("Spelling", "ReadingTest", "ReadingAndLanguage", "ListeningComprehension", "ReadingComprehension", "LanguageAndLanguageUsage"),
			"levels" => array("1st Grade", "2nd Grade", "3th Grade", "4th Grade", "5th Grade", "6th Grade", "7th Grade", "8th Grade", "9th Grade", "10th Grade"),
			"grades" => array("12", "10", "07", "04", "02", "00", "-03"),
			"tags" => array("Domain Driven Design", "TYPO3", "FLOW", "PHP", "jQuery", "AJAX"),
			"wordClasses" => array(
				array("Verb", 0, 1),
				array("Noun", 1, 2),
				array("Adverb", 2, 4),
				array("Preposition", 3, 8),
				array("Adjective", 4, 16),
				array("Pronoun", 5, 32),
				array("Conjunction", 6, 64),
				array("Determiner", 7, 128),
				array("Exclamation", 8, 256),
			),
			"difficulty" => array(
				array("Easy", 0, 1),
				array("Medium", 1, 2),
				array("Hard", 2, 4),
			),
			"errorTypes" => array(
				array("Error Type A", "Description of Error Type A"),
				array("Error Type B", "Description of Error Type B"),
				array("Error Type C", "Description of Error Type C"),
				array("Error Type D", "Description of Error Type D")
			)
		);

		return $data;
	}

	/**
	 * Generate subjects
	 *
	 * @TODO : translations?
	 * @return array
	 */
	public function generateSubjectsData() {

		$data = array(
			array(
				'title' => 'Arts',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Biology',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Danish',
				"isLanguage" => 1,
			),
			array(
				'title' => 'English',
				"isLanguage" => 1,
			),
			array(
				'title' => 'Physics / Chemistry',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Geography',
				"isLanguage" => 0,
			),
			array(
				'title' => 'History',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Domestic knowledge',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Needlework',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Sport Science',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Christian studies',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Mathematics',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Nature & Technology',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Social Studies',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Woodwork',
				"isLanguage" => 0,
			),
			array(
				'title' => 'Interdisciplinary',
				"isLanguage" => 0,
			),
		);

		return $data;
	}
}

?>