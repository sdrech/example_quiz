<?php
namespace _OurBrand_\Quiz\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use _OurBrand_\Quiz\Annotations as _OurBrand_;
use _OurBrand_\Quiz\Utility as Utility;

/**
 * @Flow\Entity
 * @_OurBrand_\ExerciseUseController("MatchPictureInPictureExercise")
 */
class MatchPictureInPictureExercise
	extends \_OurBrand_\Quiz\Domain\Model\Exercise
	implements \_OurBrand_\Quiz\Domain\Model\ExerciseInterface {


	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage>
	 * @ORM\OneToMany(mappedBy="exercise")
	 */
	protected $mainImageCollection;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape>
	 * @ORM\OneToMany(mappedBy="exercise")
	 */
	protected $shapes;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\Doctrine\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * Check if all required fields are filled out by the [Instructor]
	 *
	 * Note: This function should always call getExerciseReadyForCompletion()
	 * to validate the extended Exercise
	 *
	 * @return boolean $ready
	 */
	public function getReadyForCompletion() {
		$ready = $this->getExerciseReadyForCompletion();

		if ($ready) {
			$shapes = $this->getActiveShapes();

			if (count($shapes) === 0) {
				$ready = 0;
			}
		}

		return $ready;

	}


	public function __construct() {
		$this->shapes = new ArrayCollection();
		$this->mainImage = new ArrayCollection(); // Note: although it's a collection, we make sure that there's only one image present at all time

		parent::__construct();
	}


	/**
	 * Delete all current shapes
	 */
	public function clearShapes() {
		$this->shapes->clear();
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $shapes
	 */
	public function setShapes($shapes) {
		$this->shapes = $shapes;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape $shape
	 */
	public function addShape(\_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape $shape) {
		$shape->setExercise($this);
		$this->shapes->add($shape);
		$this->setMaxScore(count($this->getActiveShapes()));

	}


	/**
	 * @param array $shapes
	 */
	public function addShapesFromArray($shapes) {
		$this->shapes->clear();

		foreach ($shapes as $shape) {

			if (empty($shape['uuid']) || $shape['inCreation'] == 1) {
				continue;
			}

			$shapeObj = new \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShapeImage();
			$shapeObj->setImage($shape['imageObj']);
			$shapeObj->setType($shape['type']);
			$shapeObj->setX($shape['x']);
			$shapeObj->setY($shape['y']);
			$shapeObj->setActive($shape['active']);
			$shapeObj->setAttributes($shape);

			$this->addShape($shapeObj);
		}

	}


	/**
	 * @return ArrayCollection|\Doctrine\Common\Collections\Collection
	 */
	public function getShapes() {
		return $this->shapes;
	}


	/**
	 * Returns array with active shapes (only shapes that are in use in the exercise)
	 * @return array
	 */
	public function getActiveShapes() {
		$shapes = $this->getShapes();

		$activeShapes = array();

		foreach ($shapes as $shape) {
			if ($shape->getActive()) {
				$activeShapes[] = $shape;
			}
		}

		return $activeShapes;
	}


	/**
	 * Returns shapes in random order
	 * @return array
	 */
	public function getRandomShapes() {
		$shapes = $this->shapes->toArray();
		shuffle($shapes);

		return $shapes;

	}


	/**
	 * @param array $answers
	 * @return int $score
	 */
	public function calculateScoreForAnswers($answers) {
		$score = 0;

		$utility = new \_OurBrand_\Quiz\Utility\Utility();

		foreach ($answers as $saltedUuid => $answer) {
			if ($saltedUuid == $utility->getSaltedString($answer)) {
				$score++;
			}
		}

		return $score;
	}


	/**
	 * @param array $answers -
	 * @return int
	 */
	public function isCompleted($answers = array()) {

		if (!is_array($answers) || count($answers) === 0) {
			return 0;
		}

		foreach ($answers as $answerUuid) {
			if (empty($answerUuid)) {
				return 0;
			}
		}

		return 1;
	}


	/**
	 * @inheritdoc
	 */
	public function postClone() {
		parent::postClone();


		// Copy shapes
		$newShapes = new ArrayCollection();
		foreach ($this->getShapes() as $shape) {
			$newShape = clone $shape;
			$newShape->setExercise($this);
			$newShapes->add($newShape);
		}

		$this->shapes = $newShapes;

		// copy the mainImage
		$tempMainImageCollection = new ArrayCollection();
		foreach ($this->getMainImageCollection() as $mainImageCollection) {
			$newMainImageCollection = clone $mainImageCollection;
			$newMainImageCollection->setExercise($this);
			$tempMainImageCollection->add($newMainImageCollection);
		}

		$this->mainImageCollection = $tempMainImageCollection;

	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $mainImage
	 */
	public function setMainImageCollection($mainImage) {
		$this->mainImageCollection = $mainImage;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection<\_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage>
	 */
	public function getMainImageCollection() {
		return $this->mainImageCollection;
	}

	/**
	 * Get the main image in the collection (there should only be one)
	 * @return \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage
	 */
	public function getMainImage() {
		if (count($this->mainImageCollection) > 0) {
			return $this->mainImageCollection->first();
		} else {
			return null;
		}
	}

	/**
	 *
	 * Saves mainImage on the exercise.
	 * We make sure that the collection does only contain a single image.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $imageObj
	 */
	public function addMainImage($imageObj) {

		$mainImage = new \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage();
		$mainImage->setImage($imageObj);
		$mainImage->setExercise($this);

		$this->mainImageCollection->clear(); // First we delete the image that is already present. We only have ONE main image on an exercise
		$this->mainImageCollection->add($mainImage);

	}


	/**
	 * Return shapes as JSON
	 *
	 * @param bool $activeOnly - return only active shapes
	 * @return string
	 */
	public function getShapesAsJson($activeOnly = false) {
		$shapes = $this->getShapes();

		$shapesArray = array();
		foreach ($shapes as $shape) {
			if ($activeOnly && $shape->getActive() === false) {
				continue;
			}

			// Create an array structure that javascript expects
			// array( x => xxx, y => xxx, uuid => xxx, width => xxx, height => xxx )
			$attributes = $shape->getAttributes();
			$attributes['x'] = $shape->getX();
			$attributes['y'] = $shape->getY();
			$attributes['type'] = $shape->getType();

			if ($shape->getType() == 'image') { // the image shape is a special case, that also have an src attribute
				$attributes['src'] = $shape->getImageSrc();
			}

			$shapesArray[] = $attributes;

		}

		return json_encode($shapesArray);
	}
}