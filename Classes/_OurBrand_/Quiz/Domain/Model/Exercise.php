<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="class", type="string")
 */
class Exercise {

	/**
	 *
	 *
	 * @var int
	 */
	protected $isCompleted = 0;

	/**
	 * Used to indicate whether user has performed an explicit save on this
	 * exercise.
	 *
	 * @var bool
	 */
	protected $isDraft = true;

	/**
	 * @var string
	 * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=240 })
	 */
	protected $title;

	/**
	 * "Vælg færdighed eller emne der testes"
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\ExerciseSkill
	 * @ORM\ManyToOne(inversedBy="exercises")
	 */
	protected $exerciseSkill;

	/**
	 * "Opgavebeskrivelse eller tekst til spørgsmål..."
	 *
	 * @var string
	 * @ORM\Column(length=4096)
	 */
	protected $description;

	/**
	 * "Forklaring på hvorfor svaret (svarene) er som de er..."
	 *
	 * @var string
	 */
	protected $explanation = '';


	/**
	 * "Alternativ hjælpetekst..."
	 *
	 * @var string
	 */
	protected $hint = '';

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Quiz
	 * @ORM\ManyToOne(inversedBy="exercises")
	 */
	protected $quiz;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ExerciseType
	 * @Flow\Transient
	 */
	protected $type;

	/**
	 * @var int
	 */
	protected $number;

	/**
	 * The expected duration of the activity in seconds.
	 *
	 * @var int
	 * @ORM\Column(nullable=true)
	 */
	protected $duration = 0;


	/**
	 * @var int
	 * @todo Make this property Transient, but cannot be done because of existing flow bug
	 * @see http://forge.typo3.org/issues/36734
	 */
	protected $minutes = 0;


	/**
	 * @var int
	 * @todo Make this property Transient, but cannot be done because of existing flow bug
	 * @see http://forge.typo3.org/issues/36734
	 */
	protected $seconds = 0;


	/**
	 * @var int
	 */
	protected $maxScore;


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @ORM\OneToOne(cascade={"persist"})
	 */
	protected $image;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\FileResource
	 * @ORM\OneToOne
	 */
	protected $pdfFile;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\FileResource
	 * @ORM\OneToOne
	 */
	protected $soundFile;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\TextContent
	 * @ORM\OneToOne
	 */
	protected $textContent;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Difficulty
	 * @ORM\ManyToOne(inversedBy="exercises")
	 */
	protected $difficulty;

	/**
	 * When exercise is shown in summary/review context
	 * the answer given can be found here.
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\Answer
	 * @Flow\Transient
	 */
	protected $answer;

	/**
	 * Array containing the settings for allowed/disabled media content types:
	 * - addPicture
	 * - addPdf
	 * - addAudio
	 * - addText
	 *
	 * @var array
	 * @Flow\Transient
	 */
	protected $mediaContents = array(
		'picture' => true,
		'pdf' => true,
		'audio' => true,
		'text' => true
	);

	public function __construct() {
		$this->title = '';
		$this->description = '';
		$this->number = 0;
		$this->maxScore = 0;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param int $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @return int
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $quiz
	 */
	public function setQuiz($quiz) {
		$this->quiz = $quiz;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	public function getQuiz() {
		return $this->quiz;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseType $type
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ExerciseType
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $hint
	 */
	public function setHint($hint) {
		$this->hint = $hint;
	}

	/**
	 * @return string
	 */
	public function getHint() {
		return $this->hint;
	}

	/**
	 * @param mixed $duration
	 */
	public function setDuration($duration) {
		$this->duration = $duration;
	}

	/**
	 * @return mixed
	 */
	public function getDuration() {
		return $this->duration;
	}

	/**
	 * @param string $explanation
	 */
	public function setExplanation($explanation) {
		$this->explanation = $explanation;
	}

	/**
	 * @return string
	 */
	public function getExplanation() {
		return $this->explanation;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkill $exerciseSkill
	 */
	public function setExerciseSkill($exerciseSkill) {
		$this->exerciseSkill = $exerciseSkill;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ExerciseSkill
	 */
	public function getExerciseSkill() {
		return $this->exerciseSkill;
	}

	/**
	 * @param mixed $minutes
	 */
	public function setMinutes($minutes) {
		$this->minutes = $minutes;
	}

	/**
	 * @return mixed
	 */
	public function getMinutes() {
		return $this->minutes;
	}

	/**
	 * @param int $seconds
	 */
	public function setSeconds($seconds) {
		$this->seconds = $seconds;
	}

	/**
	 * @return int
	 */
	public function getSeconds() {
		return $this->seconds;
	}

	/**
	 * @param boolean $isDraft
	 */
	public function setIsDraft($isDraft) {
		$this->isDraft = $isDraft;
	}

	/**
	 * @return boolean
	 */
	public function getIsDraft() {
		return $this->isDraft;
	}

	/**
	 * @param int $maxScore
	 */
	public function setMaxScore($maxScore) {
		$this->maxScore = $maxScore;
	}

	/**
	 * @return int
	 */
	public function getMaxScore() {
		return $this->maxScore;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Answer $answer
	 */
	public function setAnswer($answer) {
		$this->answer = $answer;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Answer
	 */
	public function getAnswer() {
		return $this->answer;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $soundFile
	 */
	public function setSoundFile($soundFile) {
		$this->soundFile = $soundFile;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\FileResource
	 */
	public function getSoundFile() {
		return $this->soundFile;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $pdfFile
	 */
	public function setPdfFile($pdfFile) {
		$this->pdfFile = $pdfFile;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\FileResource
	 */
	public function getPdfFile() {
		return $this->pdfFile;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Difficulty $difficulty
	 */
	public function setDifficulty($difficulty) {
		$this->difficulty = $difficulty;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Difficulty
	 */
	public function getDifficulty() {
		return $this->difficulty;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\TextContent $textContent
	 */
	public function setTextContent($textContent) {
		$this->textContent = $textContent;
		return $this;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\TextContent
	 */
	public function getTextContent() {
		return $this->textContent;
	}
	
	/**
	 * Get copyright info from exercise
	 * 
	 * @return array
	 */
	public function getCopyRightInfo() {
		$result = array();
		
		if ($this->soundFile !== null && strlen(trim($this->soundFile->getDescription()))) {
			$result[] = array(
				'text' => $this->soundFile->getDescription(),
				'type' => 'audio',
				'exercise' => ($this->number + 1)
			);
		}
		
		if ($this->pdfFile !== null && strlen(trim($this->pdfFile->getDescription()))) {
			$result[] = array(
				'text' => $this->pdfFile->getDescription(),
				'type' => 'pdf',
				'exercise' => ($this->number + 1)
			);
		}
		
		if ($this->image !== null && strlen(trim($this->image->getCopyright()))) {
			$result[] = array(
				'text' => $this->image->getCopyright(),
				'type' => 'image',
				'exercise' => ($this->number + 1)
			);
		}
		
		if ($this->textContent !== null && strlen(trim($this->textContent->getCopyright()))) {
			$result[] = array(
				'text' => $this->textContent->getCopyright(),
				'type' => 'text',
				'exercise' => ($this->number + 1)
			);
		}
		
		return $result;
	}

	/**
	 * Implementation of custom clone method since
	 * native php __clone raises error in Flow.
	 * Override in exercises which have additional references to objects.
	 */
	public function postClone() {

		if (is_object($this->image)) {
			$this->image = $this->image->copy();
		}

		if (is_object($this->pdfFile)) {
			$this->pdfFile = $this->pdfFile->copy();
		}

		if (is_object($this->soundFile)) {
			$this->soundFile = $this->soundFile->copy();
		}
	}

	/**
	 * Check if all required fields are filled out by the [Instructor]
	 *
	 * @return int $ready (0 = not ready, 1 = ready for completion, 2 = completed)
	 */
	public function getExerciseReadyForCompletion() {
		$ready = 1;

		if (empty($this->title)) {
			$ready = 0;
		}

		return $ready;
	}

	/**
	 * Set the single mediaContent item in the array
	 * @var string $item (picture/pdf/audio/text)
	 * @var boolean $value
	 */
	public function setMediaContent($item, $value) {
		if (array_key_exists($item, $this->mediaContents)) {
			$this->mediaContents[$item] = $value;
		}
	}

	/**
	 * Return the array with mediaContents
	 * @return array
	 */
	public function getMediaContents() {
		return $this->mediaContents;
	}

}

