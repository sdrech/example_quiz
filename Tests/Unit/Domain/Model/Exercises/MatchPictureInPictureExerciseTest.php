<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model\Exercises;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */
use _OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExercise;
use _OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureExerciseExercise;

/**
 * Testcase for MatchPictureInPictureExercise
 */

class MatchPictureInPictureExerciseTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function setUp() {
	}

	/**
	 * @return array
	 */
	public function shapesDataProvider() {
		$mockImage = $this->getMock('_OurBrand_\Quiz\Domain\Model\ImageResource');
		$img = new $mockImage();

		$shapes = array(
			'empty all' => array(
				array(),
				'assertions' => array(
					'shapesCount' => 0,
					'activeShapeCount' => 0
				)
			),
			'one shape' => array(
				array(
					array(
						'x' => 130,
						'y' => 50,
						'width' => 100,
						'height' => 100,
						'uuid' => '711386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					)
				),
				'assertions' => array(
					'shapesCount' => 1,
					'activeShapeCount' => 1
				)
			),
			'two shapes' => array(
				array(
					array(
						'x' => 130,
						'y' => 50,
						'width' => 100,
						'height' => 100,
						'uuid' => '711386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					),
					array(
						'x' => 200,
						'y' => 100,
						'width' => 100,
						'height' => 100,
						'uuid' => '711386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					)
				),
				'assertions' => array(
					'shapesCount' => 2,
					'activeShapeCount' => 2
				)
			),
			'three shapes 2 inactive' => array(
				array(
					array(
						'x' => 130,
						'y' => 50,
						'width' => 100,
						'height' => 100,
						'uuid' => '011386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					),
					array(
						'x' => 200,
						'y' => 100,
						'width' => 100,
						'height' => 100,
						'uuid' => '111386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 0,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					),
					array(
						'x' => 400,
						'y' => 100,
						'width' => 100,
						'height' => 100,
						'uuid' => '211386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 0,
						'type' => 'image',
						'inCreation' => 0,
						'imageObj' => $img
					)
				),
				'assertions' => array(
					'shapesCount' => 3,
					'activeShapeCount' => 1
				)
			),
			'one shape - inCreation = 0' => array(
				array(
					array(
						'x' => 130,
						'y' => 50,
						'width' => 100,
						'height' => 100,
						'uuid' => '711386a9-12be-9fd8-a51f-3c4b80f4f4ee',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 1,
						'imageObj' => $img
					)
				),

				'assertions' => array(
					'shapesCount' => 0,
					'activeShapeCount' => 0
				)
			),
			'one shape - no uuid' => array(


				array(
					array(
						'x' => 130,
						'y' => 50,
						'width' => 100,
						'height' => 100,
						'uuid' => '',
						'active' => 1,
						'type' => 'image',
						'inCreation' => 1,
						'imageObj' => $img
					)
				),
				'assertions' => array(
					'iscomplete' => 0,
					'shapesCount' => 0,
					'activeShapeCount' => 0
				)
			),

		);

		return $shapes;
	}

	/**
	 * @dataProvider shapesDataProvider
	 * @param array $shapeArray
	 * @param array $assertions
	 */
	public function testAddShapesFromArray($shapeArray, $assertions) {

		$exercise = new MatchPictureInPictureExercise();
		$this->assertNull($exercise->addShapesFromArray($shapeArray));

		$shapes = $exercise->getShapes();

		//$this->assertCount($shapesCount, $shapeArray);


		$activeShapesCount = 0;
		$inactiveShapesCount = 0;

		$iteration = 0;
		foreach ($shapes as $shape) {
			$this->assertSame($exercise, $shape->getExercise());
			$this->assertSame($shapeArray[$iteration]['x'], $shape->getX());
			$this->assertSame($shapeArray[$iteration]['y'], $shape->getY());
			$this->assertSame($shapeArray[$iteration]['active'], $shape->getActive());
			$this->assertSame($shapeArray[$iteration]['imageObj'], $shape->getImage());

			if ($shape->getActive()) {
				$activeShapesCount++;
			}
			else {
				$inactiveShapesCount++;
			}

			$iteration++;
		}
	}

	/**
	 * @dataProvider shapesDataProvider
	 * @param $shapeArray
	 * @param $assertions
	 */
	public function testGetActiveShapes($shapeArray, $assertions) {

		$exercise = new MatchPictureInPictureExercise();
		$this->assertNull($exercise->addShapesFromArray($shapeArray));

		// test Active shapes
		$activeShapes = $exercise->getActiveShapes();
		$this->assertCount($assertions['activeShapeCount'], $activeShapes);

	}


	/**
	 * @dataProvider shapesDataProvider
	 * @param $shapeArray
	 * @param $assertions
	 */
	public function testClearShapes($shapeArray, $assertions) {

		$exercise = new MatchPictureInPictureExercise();
		$this->assertNull($exercise->addShapesFromArray($shapeArray));


		// Test clearShapes()
		$exercise->clearShapes();
		$this->assertSame(0, count($exercise->getShapes()));
	}

	/**
	 * @dataProvider shapesDataProvider
	 * @param $shapeArray
	 * @param $assertions
	 */
	public function testGetRandomShapes($shapeArray, $assertions) {
		$exercise = new MatchPictureInPictureExercise();
		$this->assertNull($exercise->addShapesFromArray($shapeArray));

		$randomShapes = $exercise->getRandomShapes();

		// Check that number match
		$this->assertCount(count($randomShapes), $exercise->getShapes());

		// Check that each shape has the correct attributes
		foreach ($randomShapes as $shape) {

			$this->assertTrue(method_exists($shape, 'getX'), 'object doent have method: getX()');
			$this->assertTrue(method_exists($shape, 'getY'), 'object doent have method: getY()');
			$this->assertTrue(method_exists($shape, 'getType'), 'object doent have method: getType()');
			$this->assertTrue(method_exists($shape, 'getImage'), 'object doent have method: getImage()');
			$this->assertTrue(method_exists($shape, 'getActive'), 'object doent have method: getActive()');
			$this->assertTrue(method_exists($shape, 'getAttributes'), 'object doent have method: getAttributes()');

		}

	}


	/**
	 * @dataProvider shapesDataProvider
	 * @param $shapeArray
	 * @param $assertions
	 */
	public function testGetReadyForCompletion($shapeArray, $assertions) {
		$exercise = new MatchPictureInPictureExercise();
		$exercise->setDescription('sdfdsfds');
		$exercise->setTitle('dfgdfgfd');
		$this->assertNull($exercise->addShapesFromArray($shapeArray));

		$assertedReady = $assertions['activeShapeCount'] > 0 ? 1 : 0;
		$this->assertSame($assertedReady, $exercise->getReadyForCompletion());

	}


	public function testMainImage() {
		$exercise = new MatchPictureInPictureExercise();

		$mockMainImage = $this->getMock('_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage');
		$mockMainImage->expects($this->any())->method('getX')->will($this->returnValue(666));

		$collection = new \Doctrine\Common\Collections\ArrayCollection();
		$collection->add($mockMainImage);

		$this->assertNull($exercise->setMainImageCollection($collection), 'setMainImageCollection() must return null');
		$this->assertCount(1, $exercise->getMainImageCollection(), 'getMainImageCollection() count doesn\'t match');
		$this->assertSame(
			true,
			is_object($exercise->getMainImage()),
			'getMainImage() must return an object of \_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage'
		);


		$mockImage = $this->getMock('\_OurBrand_\Quiz\Domain\Model\ImageResource');
		$this->assertNull(
			$exercise->addMainImage($mockImage),
			'addMainImage() must have an imageResource object given'
		);
	}


	/**
	 * @dataProvider shapesDataProvider
	 * @param $shapeArray
	 * @param $assertions
	 */
	public function testPostClone($shapeArray, $assertions) {
		$exercise = new MatchPictureInPictureExercise();
		$this->assertNull($exercise->addShapesFromArray($shapeArray));

		// mainImage
		$mockMainImage = $this->getMock('_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureMainImage');

		$collection = new \Doctrine\Common\Collections\ArrayCollection();
		$collection->add($mockMainImage);
		$exercise->setMainImageCollection($collection);

		$mockImage = $this->getMock('\_OurBrand_\Quiz\Domain\Model\ImageResource', array('clear'));
		$mockMainImage->expects($this->any())->method('clear')->will($this->returnValue(null));

		$this->assertNull(
			$exercise->addMainImage($mockImage),
			'addMainImage() must have an imageResource object given'
		);


		// shapes
		$mockShape = $this->getMock(
			'_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape',
			array('getImageSrc', 'getType', 'getActive')
		);
		$this->assertNull($exercise->addShape($mockShape), 'addShape()');


		// active shape
		$mockShape = $this->getMock(
			'_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape',
			array('getImageSrc', 'getType', 'getActive')
		);
		$this->assertNull($exercise->addShape($mockShape), 'addShape()');

		$exerciseClone = clone($exercise);
		$this->assertNull($exerciseClone->postClone(), '$clone->postClone()');


		// check that the cloned data are the same

		// Test mainImage
		$this->assertCount(
			count($exercise->getMainImageCollection()),
			$exerciseClone->getMainImageCollection(),
			'cloned getMainImageCollection() does not match'
		);

		// Test shapes
		$this->assertCount(
			count($exercise->getShapes()),
			$exerciseClone->getShapes(),
			'cloned getShapes() does not match'
		);


	}


	/**
	 */
	public function testGetShapesAsJson() {

		$exercise = new MatchPictureInPictureExercise();


		// inactive shape
		$mockShape = $this->getMock(
			'_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape',
			array('getImageSrc', 'getType', 'getActive')
		);
		$mockShape->expects($this->any())->method('getImageSrc')->will($this->returnValue('mocked src'));
		$mockShape->expects($this->any())->method('getType')->will($this->returnValue('image'));
		$mockShape->expects($this->any())->method('getActive')->will($this->returnValue(false));
		$this->assertNull($exercise->addShape($mockShape), 'addShape()');


		// active shape
		$mockShape = $this->getMock(
			'_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureShape',
			array('getImageSrc', 'getType', 'getActive')
		);
		$mockShape->expects($this->any())->method('getImageSrc')->will($this->returnValue('mocked src'));
		$mockShape->expects($this->any())->method('getType')->will($this->returnValue('image'));
		$mockShape->expects($this->any())->method('getActive')->will($this->returnValue(true));
		$this->assertNull($exercise->addShape($mockShape), 'addShape()');


		$this->assertStringStartsWith('[{', $exercise->getShapesAsJson(true), 'returned string must start with [{');
		$this->assertStringEndsWith('}]', $exercise->getShapesAsJson(true), 'returned string must end with }]');

	}


	/**
	 */
	public function testSetShapes() {

		$mockShape = $this->getMock('_OurBrand_\Quiz\Domain\Model\Exercises\MatchPictureInPictureShape');
		$mockShape->expects($this->any())->method('getX')->will($this->returnValue(666));

		$collection = new \Doctrine\Common\Collections\ArrayCollection();
		$collection->add($mockShape);


		$exercise = new MatchPictureInPictureExercise();
		$exercise->setShapes($collection);

		$this->assertCount(1, $exercise->getShapes());
	}

	/**
	 * @return array
	 */
	public function answersDataProvider() {

		$utility = new \_OurBrand_\Quiz\Utility\Utility();

		return array(
			array(
				array(
					$utility->getSaltedString('12345') => 12345,
					$utility->getSaltedString('666') => 666,
					$utility->getSaltedString('1') => 123, // wrong
					$utility->getSaltedString('2') => '', // wrong
				),
				'assertions' => array(
					'corrects' => 2,
					'isComplete' => 0
				)
			),
			array(
				array(
					$utility->getSaltedString('12345') => 12345,
					$utility->getSaltedString('0') => 4213423243, // wrong
					$utility->getSaltedString('1') => 123, // wrong
					$utility->getSaltedString('2') => 123312132, // wrong
				),
				'assertions' => array(
					'corrects' => 1,
					'isComplete' => 1
				)
			),
			array(
				array(
					$utility->getSaltedString('3') => 3,
					$utility->getSaltedString('1') => 1,
					$utility->getSaltedString('2') => 2,
				),
				'assertions' => array(
					'corrects' => 3,
					'isComplete' => 1
				)
			),
			array(
				array(

				),
				'assertions' => array(
					'corrects' => 0,
					'isComplete' => 0
				)
			)
		);
	}

	/**
	 * @dataProvider answersDataProvider
	 */
	public function testAnswers($answers, $assertions) {
		$exercise = new MatchPictureInPictureExercise();

		$this->assertSame($assertions['corrects'], $exercise->calculateScoreForAnswers($answers));

	}


	/**
	 * @dataProvider answersDataProvider
	 * @param $answers
	 * @param $assertions
	 */
	public function testIsCompleted($answers, $assertions) {

		$exercise = new MatchPictureInPictureExercise();

		$this->assertSame($assertions['isComplete'], $exercise->isCompleted($answers));


	}





}