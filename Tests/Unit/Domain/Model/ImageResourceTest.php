<?php
namespace _OurBrand_\Quiz\Tests\Unit\Domain\Model;

/*																			*
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".			*
 * @author Thor Solli														*
 *																			*/
use _OurBrand_\Quiz\Domain\Model\ImageResource;

/**
 * Testcase for Quiz
 */
class ImageResourceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	protected $image;
	
	/**
	 * @return void
	 */
	public function setUp() {
		$mockResourcePointer = $this->getMock('TYPO3\Flow\Resource\ResourcePointer', array(), array(), '', FALSE);
		$mockResourcePointer
			->expects($this->any())
			->method('getHash')
			->will($this->returnValue('dummyResourcePointerHash'));

		$mockResource = $this->getMock('TYPO3\Flow\Resource\Resource');
		$mockResource
			->expects($this->any())
			->method('getResourcePointer')
			->will($this->returnValue($mockResourcePointer));

		$this->image = $this->getAccessibleMock('_OurBrand_\Quiz\Domain\Model\ImageResource', array('setOriginalResource'), array('resource' => $mockResource));
	}
	
	/**
	 * @test
	 */
	public function aspectRatioReturnedCorrectlyForSquareImage() {
		$this->image->_set('width', 480);
		$this->image->_set('height', 480);

		$this->assertEquals(1, $this->image->getAspectRatio());
		$this->assertEquals(1, $this->image->getAspectRatio(FALSE));
		$this->assertEquals(1, $this->image->getAspectRatio(TRUE));
	}
	
	/**
	 * @test
	 */
	public function aspectRatioReturnedCorrectlyForLandscapeImage() {
		$this->image->_set('width', 480);
		$this->image->_set('height', 320);

		$this->assertEquals(1.5, $this->image->getAspectRatio());
		$this->assertEquals(1.5, $this->image->getAspectRatio(FALSE));
		$this->assertEquals(1.5, $this->image->getAspectRatio(TRUE));
	}

	/**
	 * @test
	 */
	public function aspectRatioReturnedCorrectlyForPortraitImage() {
		$this->image->_set('width', 320);
		$this->image->_set('height', 480);

		$this->assertEquals(1.5, $this->image->getAspectRatio());
		$this->assertEquals(1.5, $this->image->getAspectRatio(FALSE));
		$this->assertEquals(0.6667, round($this->image->getAspectRatio(TRUE), 4));
	}

	/**
	 * @test
	 *
	public function widthAndHeightIsCastToIntegerWhenCreatingThumbnail() {
		$variant = $this->image->getThumbnail('4', '3');
		$processingInstructions = $variant->getProcessingInstructions();
		$this->assertInternalType('integer', $processingInstructions[0]['options']['size']['width']);
		$this->assertInternalType('integer', $processingInstructions[0]['options']['size']['height']);
	}*/
}
