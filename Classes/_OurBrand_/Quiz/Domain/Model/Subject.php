<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;


/**
 * @Flow\Entity
 */
class Subject {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var int
	 */
	protected $isLanguage = 0;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Quiz>
	 * @ORM\ManyToMany(mappedBy="subjects");
	 */
	protected $quizzes;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory>
	 * @ORM\OneToMany(mappedBy="subject")
	 */
	protected $exerciseSkillCategories;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\QuizCategory>
	 * @ORM\OneToMany(mappedBy="subject")
	 */
	protected $quizCategories;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\QuizType>
	 * @ORM\OneToMany(mappedBy="subject")
	 */
	protected $quizTypes;

	/**
	 *
	 */
	public function __construct() {
		if (is_a($this->exerciseSkillCategories, '\Doctrine\Common\Collections\Collection') === false) {
			$this->exerciseSkillCategories = new \Doctrine\Common\Collections\ArrayCollection();
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return int
	 */
	public function getIsLanguage() {
		return $this->isLanguage;
	}

	/**
	 * @param int $isLanguage
	 */
	public function setIsLanguage($isLanguage) {
		$this->isLanguage = $isLanguage;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getExerciseSkillCategories() {
		return $this->exerciseSkillCategories;
	}

	/**
	 * @param mixed $categories
	 */
	public function setExerciseSkillCategories($exerciseSkillCategories) {
		$this->exerciseSkillCategories = $exerciseSkillCategories;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function addExerciseSkillCategory($exerciseSkillCategory) {
		if (!$this->exerciseSkillCategories->contains($exerciseSkillCategory)) {
			$this->exerciseSkillCategories->add($exerciseSkillCategory);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\ExerciseSkillCategory $exerciseSkillCategory
	 */
	public function removeExerciseSkillCategory($exerciseSkillCategory) {
		if ($this->exerciseSkillCategories->contains($exerciseSkillCategory)) {
			$this->exerciseSkillCategories->removeElement($exerciseSkillCategory);
		}
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $quizCategories
	 */
	public function setQuizCategories($quizCategories) {
		$this->quizCategories = $quizCategories;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getQuizCategories() {
		return $this->quizCategories;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function addQuizCategory($quizCategory) {
		if (!$this->quizCategories->contains($quizCategory)) {
			$this->quizCategories->add($quizCategory);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizCategory $quizCategory
	 */
	public function removeQuizCategory($quizCategory) {
		if ($this->quizCategories->contains($quizCategory)) {
			$this->quizCategories->removeElement($quizCategory);
		}
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $quizTypes
	 */
	public function setQuizTypes($quizTypes) {
		$this->quizTypes = $quizTypes;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getQuizTypes() {
		return $this->quizTypes;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function addQuizType($quizType) {
		if (!$this->quizTypes->contains($quizType)) {
			$this->quizTypes->add($quizType);
		}
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\QuizType $quizType
	 */
	public function removeQuizType($quizType) {
		if ($this->quizTypes->contains($quizType)) {
			$this->quizTypes->removeElement($quizType);
		}
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $quizzes
	 */
	public function setQuizzes($quizzes) {
		$this->quizzes = $quizzes;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getQuizzes() {
		return $this->quizzes;
	}


}

?>