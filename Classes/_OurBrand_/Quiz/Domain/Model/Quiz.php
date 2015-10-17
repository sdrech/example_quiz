<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a quiz in its entirety.
 *
 * @Flow\Entity
 * @ORM\Table(indexes={@ORM\Index(name="is_isDraft_idx",columns={"quiz","isdraft"})})
 */
class Quiz {

	const EXAM_TYPE = 0;
	const TEST_TYPE = 1;
	const POA_TYPE = 2;

	/**
     * Products, this is not a "type" as much as it is a overall category / product, a QuizType is something else.
     *
	 * 0: Quiz / Exam (EXAM_TYPE)
	 * 1: Test (TEST_TYPE)
	 * 2: Part of activity/IWB (POA_TYPE)
	 *
     * @TODO: REFACTOR THIS
	 * @var int
	 * @ORM\Column(nullable=TRUE)
	 */
	protected $type = self::EXAM_TYPE;

	/**
	 * @var string
	 * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=240 })
	 */
	protected $title = '';

	/**
	 * @var string
	 * @Flow\Validate(type="StringLength", options={ "minimum"=2 })
	 */
	protected $author;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Subject>
	 * @ORM\ManyToMany(cascade={"detach"})
	 * @ORM\OrderBy({"title" = "ASC"})
	 */
	protected $subjects;

	/**
	 * "School Class" / Team Level
	 *
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\TeamLevel>
	 * @ORM\ManyToMany(cascade={"detach"})
	 */
	protected $levels;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @ORM\OneToOne(cascade={"persist"})
	 */
	protected $bannerImage;

	/**
	 * "Introduction description for the Quiz"
	 *
	 * @var string
	 */
	protected $introduction = '';

	/**
	 * "Description for the Quiz"
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $creator = '';

	/**
	 * @var bool
	 */
	protected $isDraft = true;

	/**
	 * @var bool
	 */
	protected $isDeleted = false;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercise>
	 * @ORM\OneToMany(mappedBy="quiz", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $exercises;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Tag>
	 * @ORM\ManyToMany(cascade={"persist"})
	 * @ORM\OrderBy({"value" = "ASC"})
	 */
	protected $tags;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\KnowledgePrerequisite>
	 * @ORM\OneToMany(mappedBy="quiz")
	 * @ORM\OrderBy({"number" = "ASC"})
	 */
	protected $knowledgePrerequisites;

	/**
	 * ContentCategory (only when at least one subject has isLanguageSubject = 1)
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\ContentCategory
	 * @ORM\ManyToOne
	 */
	protected $contentCategory;

	/**
	 * Category, this is categories that only the editors can see.
     * The footer will display three selects, depending on configuration.
     * The "skill" select have these categories attached.
	 *
	 * @var \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory
	 * @ORM\ManyToOne(inversedBy="quizzes")
	 */
	protected $exerciseSkillCategory;

    /**
     *
     * @var \_OurBrand_\Quiz\Domain\Model\QuizCategory
     * @ORM\ManyToOne(inversedBy="quizzes")
     */
	protected $quizCategory;

    /**
     *
     * @var \_OurBrand_\Quiz\Domain\Model\QuizType
     * @ORM\ManyToOne(inversedBy="quizzes")
     */
	protected $quizType;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Quiz
	 * @ORM\ManyToOne
	 */
	protected $copyOf = null;

	/**
	 * If the copy is deleted then this variable will be set to the title of the
	 * original.
	 * @var string
	 */
	protected $wasCopyOf = '';


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Quiz
	 * @ORM\ManyToOne(inversedBy="snapshots")
	 */
	protected $snapshotOf = null;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Quiz>
	 * @ORM\OneToMany(mappedBy="snapshotOf")
	 * @ORM\OrderBy({"snapshotTimestamp" = "DESC"})
	 */
	protected $snapshots;


	/**
	 * @var \DateTime
	 * @ORM\Column(nullable=TRUE)
	 */
	protected $snapshotTimestamp;


	/**
	 * @var bool
	 */
	protected $isLatestSnapshot = FALSE;

	/**
	 * @var \DateTime
	 */
	protected $lastEdited = null;

	/**
	 * "Er sprogfaglig"
	 * @var boolean
	 */
	protected $isLanguage = false;

	/**
	 * Sharing options: 0 = private, 1 = public, 2 = instructors at my school/institution, 3 = custom (se AccessRights table)
	 * @var int
	 */
	protected $sharing = 0;

	/**
	 * Duration of this quiz in seconds. Estimated by instructor.
	 * When taking quiz with timer on then student must finish
	 * before duration is reached. In seconds.
	 * Calculated by duration for each Exercise, or manually set if $this->type = 0 (Exam)
	 *
	 * @var int
	 */
	protected $duration = 0;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Transient
	 */
	protected $persistenceManager;


	public function __construct() {
		$this->bannerImage = null;
		$this->exercises = new \Doctrine\Common\Collections\ArrayCollection();
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->knowledgePrerequisites = new \Doctrine\Common\Collections\ArrayCollection();
		$this->subjects = new \Doctrine\Common\Collections\ArrayCollection();
		$this->levels = new \Doctrine\Common\Collections\ArrayCollection();
		$this->snapshots = new \Doctrine\Common\Collections\ArrayCollection();
		$this->lastEdited = new \DateTime();
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
	 * @param string $wasCopyOf
	 */
	public function setWasCopyOf($wasCopyOf) {
		$this->wasCopyOf = $wasCopyOf;
	}

	/**
	 * @return string
	 */
	public function getWasCopyOf() {
		return $this->wasCopyOf;
	}

	/**
	 * @param string
	 */
	public function setAuthor($author) {
		$this->author = $author;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $subjects
	 */
	public function setSubjects($subjects) {
		$this->subjects = $subjects;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSubjects() {
		return $this->subjects;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Subject $subject
	 */
	public function addSubject($subject) {
		if (!$this->subjects->contains($subject)) {
			$this->subjects->add($subject);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Subject $subject
	 */
	public function removeSubject($subject) {
		if ($this->subjects->contains($subject)) {
			$this->subjects->removeElement($subject);
		}
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $levels
	 */
	public function setLevels($levels) {
		$this->levels = $levels;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getLevels() {
		return $this->levels;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\TeamLevel $level
	 */
	public function addLevel($level) {
		if (!$this->levels->contains($level)) {
			$this->levels->add($level);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\TeamLevel $level
	 */
	public function removeLevel($level) {
		if ($this->levels->contains($level)) {
			$this->levels->removeElement($level);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $bannerImage
	 */
	public function setBannerImage($bannerImage) {
		$this->bannerImage = $bannerImage;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	public function getBannerImage() {
		return $this->bannerImage;
	}

	/**
	 * @param string $introduction
	 */
	public function setIntroduction($introduction) {
		$this->introduction = $introduction;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIntroduction() {
		return $this->introduction;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $copyOf
	 */
	public function setCopyOf($copyOf) {
		$this->copyOf = $copyOf;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	public function getCopyOf() {
		return $this->copyOf;
	}

	/**
	 * @param string $creator
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * @return string
	 */
	public function getCreator() {
		return $this->creator;
	}


	/**
	 * @param boolean $isLanguage
	 */
	public function setIsLanguage($isLanguage) {
		$this->isLanguage = $isLanguage;
	}

	/**
	 * @return boolean
	 */
	public function getIsLanguage() {
		return $this->isLanguage;
	}


	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getQuizTypeExam() {
		return self::EXAM_TYPE;
	}

	/**
	 * @return int
	 */
	public function getQuizTypeTest() {
		return self::TEST_TYPE;
	}

	/**
	 * @return int
	 */
	public function getQuizTypeTraining() {
		return self::POA_TYPE; 
	}

	/**
	 * @param \DateTime $lastEdited
	 */
	public function setLastEdited($lastEdited) {
		$this->lastEdited = $lastEdited;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastEdited() {
		return $this->lastEdited;
	}

	/**
	 * Sets lastEdited to current time.
	 */
	public function touch() {
		$this->lastEdited = new \DateTime();
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function addExercise($exercise) {
		// Ensure integrity.
		$exercise->setQuiz($this);

		$exercise->setNumber($this->getExercises()->count());
		$this->exercises->add($exercise);
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return void
	 */
	public function removeExercise($exercise) {
		$this->exercises->removeElement($exercise);

		// Renumber the remaining items.
		$number = 0;
		foreach ($this->exercises as $exercise) {
			$exercise->setNumber($number++);
		}
	}


	/**
	 * TODO: Retire? Is copy of getExercises->get($number);
	 * @param int
	 * @return mixed
	 */
	public function getExerciseByNumber($number = 0) {
		foreach ($this->exercises as $exercise) {
			if ($exercise->getNumber() === $number) {
				return $exercise;
			}
		}
		return null;
	}


	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return mixed
	 */
	public function findNextExercise($exercise) {
		if (count($this->exercises) > 1 && ($nextExercise = $this->exercises->get($this->exercises->indexOf($exercise) + 1))) {
			return $nextExercise;
		} else {
			return false;
		}
	}

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return mixed
	 */
	public function findPrevExercise($exercise) {
		if (count($this->exercises) > 1 && ($prevExercise = $this->exercises->get($this->exercises->indexOf($exercise) - 1))) {
			return $prevExercise;
		} else {
			return false;
		}
	}

	/**
	 * @param int $previousIndex Where the exercise was positioned.
	 * @param int $newIndex The new position for the exercise.
	 * @return void
	 * @todo implement
	 */
	public function reOrderExercises($previousIndex, $newIndex) {

	}


	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getExercises() {
		return $this->exercises;
	}


	/**
	 * @param int $sharing
	 */
	public function setSharing($sharing) {
		$this->sharing = $sharing;
	}

	/**
	 * @return boolean
	 */
	public function getSharing() {
		return $this->sharing;
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
	 * @param boolean $isDeleted
	 */
	public function setIsDeleted($isDeleted) {
		$this->isDeleted = $isDeleted;
	}

	/**
	 * @return boolean
	 */
	public function getIsDeleted() {
		return $this->isDeleted;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Tag $newTag
	 */
	public function addTag($newTag) {
		$isExists = false;
		foreach ($this->tags as $tag) {
			if ($this->persistenceManager->getIdentifierByObject($newTag) == $this->persistenceManager->getIdentifierByObject($tag)) {
				$isExists = true;
				break;
			}
		}

		if (!$isExists) {
			$this->tags->add($newTag);
		}
	}

	/**
	 * Set Tags for this quiz from data array.
	 * If tag doesn't exist we create it.
	 * Data array must be in this format:
	 * <code>
	 * $tags = array('tagId1','tagId2','newTagValue3',...);
	 * </code>
	 *
	 * @param array $tags
	 */
	public function setTagsFromArray($tags) {
		$this->tags->clear();

		$repository = new \_OurBrand_\Quiz\Domain\Repository\TagRepository();
		foreach ($tags as $value) {
			if (trim($value) == '') {
				continue;
			}
			$tag = $repository->findByIdentifier($value);
			if (is_null($tag)) {
				$tag = new \_OurBrand_\Quiz\Domain\Model\Tag($value);
			}
			$this->addTag($tag);
		}
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $knowledgePrerequisites
	 */
	public function setKnowledgePrerequisites($knowledgePrerequisites) {
		$this->knowledgePrerequisites = $knowledgePrerequisites;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getKnowledgePrerequisites() {
		return $this->knowledgePrerequisites;
	}

	/**
	 * Set Knowledge Prerequisites for this quiz from data array.
	 * Data array must be in this format:
	 * <code>
	 * $knowledgePrerequisites = array(
	 *  array(
	 *    'url' => 'url1'
	 *    'title' => 'title1'
	 *   ),
	 *  array(
	 *    'url' => 'url2',
	 *    'title' => 'title2'
	 *   )
	 * );
	 * </code>
	 *
	 * @param array $knowledgePrerequisites
	 */
	public function setKnowledgePrerequisitesFromArray($knowledgePrerequisites) {
		$this->knowledgePrerequisites->clear();
        $out = false;
        $successfull = array();
        $portalUtility = new \_OurBrand_\Quiz\Utility\PortalUtility();
		foreach ($knowledgePrerequisites as $data) {
            if($portalUtility->isUrlFromPortals($data['url'])){
                $url = new \_OurBrand_\Quiz\Domain\Model\KnowledgePrerequisite();
                $url->setTitle($data['title']);
                $url->setUrl($data['url']);
                $url->setQuiz($this);
                $url->setNumber(count($this->knowledgePrerequisites));
                $this->knowledgePrerequisites->add($url);
                $successfull[] = 1;
            }
		}

        if(array_sum($successfull)===count($knowledgePrerequisites)){
            $out = true;
        }

        return $out;
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
	 * @return Quiz $this
	 */
	public function calculateDuration() {
		$duration = 0;
		// Only for POA
		if ($this->type == self::POA_TYPE) {
			$exercises = $this->getExercises();
			foreach ($exercises as $exercise) {

                // Editor dropdown is in minutes not seconds.
				$duration += ($exercise->getDuration()*60);
			}
			$this->setDuration($duration);
		}

		return $this;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function setCategory($exerciseSkillCategory) {
		$this->exerciseSkillCategory = $exerciseSkillCategory;
		return $this;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory
	 */
	public function getCategory() {
		return $this->exerciseSkillCategory;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ContentCategory $contentCategory
	 */
	public function setContentCategory($contentCategory) {
		$this->contentCategory = $contentCategory;
		return $this;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ContentCategory
	 */
	public function getContentCategory() {
		return $this->contentCategory;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExamType $examType
	 */
	public function setExamType($examType) {
		$this->examType = $examType;
		return $this;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ExamType
	 */
	public function getExamType() {
		return $this->examType;
	}


	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Quiz $snapshotOf
	 */
	public function setSnapshotOf($snapshotOf) {
		$this->snapshotOf = $snapshotOf;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Quiz
	 */
	public function getSnapshotOf() {
		return $this->snapshotOf;
	}

	/**
	 * @param \DateTime $snapshotTimestamp
	 */
	public function setSnapshotTimestamp($snapshotTimestamp) {
		$this->snapshotTimestamp = $snapshotTimestamp;
	}

	/**
	 * @return \DateTime
	 */
	public function getSnapshotTimestamp() {
		return $this->snapshotTimestamp;
	}

	/**
	 * @param boolean $isLatestSnapshot
	 */
	public function setIsLatestSnapshot($isLatestSnapshot) {
		$this->isLatestSnapshot = $isLatestSnapshot;
	}

	/**
	 * @return boolean
	 */
	public function getIsLatestSnapshot() {
		return $this->isLatestSnapshot;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $snapshots
	 */
	public function setSnapshots($snapshots) {
		$this->snapshots = $snapshots;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSnapshots() {
		return $this->snapshots;
	}


	/**
	 * Make sure all object references are cloned.
	 *
	 */
	public function postClone() {

		// Images / resources
		if (is_object($this->bannerImage)) {
			// Clone does not work for this kind of reference.
			// Manual copy mode engaged.
			$this->bannerImage = $this->bannerImage->copy();
		}

		// Tags.
		$tempTags = new ArrayCollection();
		foreach ($this->tags as $tag) {
			$tempTags->add($tag);
		}
		$this->tags = $tempTags;

		// Knowledge Prerequisites.
		$tempKnowledgePrerequisites = new ArrayCollection();
		foreach ($this->knowledgePrerequisites as $knowledgePrerequisite) {
			$newKnowledgePrerequisite = clone $knowledgePrerequisite;
			$newKnowledgePrerequisite->setQuiz($this);
			$tempKnowledgePrerequisites->add($knowledgePrerequisite);
		}
		$this->knowledgePrerequisites = $tempKnowledgePrerequisites;

		$tempExercises = new ArrayCollection();
		foreach ($this->exercises as $exercise) {
			$newExercise = clone $exercise;
			$newExercise->postClone();
			$newExercise->setQuiz($this);
			$tempExercises->add($newExercise);
		}
		$this->exercises = $tempExercises;

		$tempSubjects = new ArrayCollection();
		foreach($this->subjects as $subject) {
			$tempSubjects->add($subject);
		}
		$this->subjects = $tempSubjects;

		$tempLevels = new ArrayCollection();
		foreach($this->levels as $level) {
			$tempLevels->add($level);
		}
		$this->levels = $tempLevels;

	}

	public function getMaxScore() {
		$score = 0;
		foreach ($this->getExercises() as $exercise) {
			$score += $exercise->getMaxScore();
		}
		return $score;
	}

	/**
	 * Validate the Quiz for completion optioniality.
	 *
	 * @return int $ready (0 = not ready, 1 = ready for completion, 2 = completed)
	 */
	public function getReadyForCompletion() {
		$ready = 1;

		//
		// Page 1 - Frontpage
		if (empty($this->title)) {
			$ready = 0;
		}
		if (empty($this->author)) {
			$ready = 0;
		}
		if (count($this->subjects) == 0) {
			$ready = 0;
		}
		if (count($this->levels) == 0) {
			$ready = 0;
		}
		if (is_null($this->type)) {
			$ready = 0;
		}
		if (empty($this->introduction)) {
			$ready = 0;
		}

		//
		// Page 2 - Exercises
		if (count($this->exercises) == 0) {
			$ready = 0;
		} else {
			//Call the same getReadyForCompletion function for each exercise
			foreach ($this->getExercises() as $exercise) {
				// js: exercise object can be base obj. or extended obj.
				if (is_object($exercise) == false
					|| (is_object($exercise) == true && method_exists($exercise, 'getReadyForCompletion') == false)
					|| (is_object($exercise) == true && method_exists($exercise,'getReadyForCompletion') == true && !$exercise->getReadyForCompletion())
				) {
					$ready = 0;
				}
			}
		}

		//
		// Page 3 - Final Setup
		if ($ready && $this->description && count($this->tags) != 0) {
			$ready = 2;
		}

		return $ready;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function setQuizCategory($quizCategory){
		$this->quizCategory = $quizCategory;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\QuizCategory
	 */
	public function getQuizCategory(){
		return $this->quizCategory;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function setQuizType($quizType){
		$this->quizType = $quizType;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\QuizType
	 */
	public function getQuizType(){
		return $this->quizType;
	}

	/**
	 * @param bool $isPortalQuiz
	 */
	public function setIsPortalQuiz($isPortalQuiz) {
		$this->isPortalQuiz = $isPortalQuiz;
	}

	/**
	 * @return bool
	 */
	public function getIsPortalQuiz() {
		return $this->isPortalQuiz;
	}

	/**
	 * Sets properties for quiz which is made for Portal
	 */
	public function setPropertiesForPortalQuizWhenCreatesTheQuiz() {
		$this->setIsPortalQuiz(true);
		$this->setIsDeleted(true);
	}

	/**
	 * @param array $properties Properites for saving
	 */
	public function setPropertiesForPortalQuizWhenEditsTheExercise($properties) {
		$this->setTitle($properties['title']);
		$this->setAuthor($properties['author']);
		$this->subjects->clear();
		$this->addSubject($properties['subject']);
	}
}
