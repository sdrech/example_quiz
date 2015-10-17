<?php
namespace _OurBrand_\Quiz\Tests\Unit\ViewHelpers;

/*																			*
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".			*
 * @author Thor Solli														*
 *																			*/

/**
 * Testcase for the image ViewHelper
 */
class ImageViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \_OurBrand_\Quiz\ViewHelpers\ImageViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \TYPO3\Fluid\Core\ViewHelper\TagBuilder
	 */
	protected $mockTagBuilder;

	/**
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $mockResourcePublisher;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	protected $mockImage;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	protected $mockThumbnail;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->viewHelper = $this->getAccessibleMock('_OurBrand_\Quiz\ViewHelpers\ImageViewHelper', array('dummy'));
		$this->mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder');
		$this->viewHelper->injectTagBuilder($this->mockTagBuilder);
		$this->mockResourcePublisher = $this->getMock('TYPO3\Flow\Resource\Publishing\ResourcePublisher');
		$this->viewHelper->_set('resourcePublisher', $this->mockResourcePublisher);
		$this->mockImage = $this->getMock('_OurBrand_\Quiz\Domain\Model\ImageResource');
		$this->mockImage->expects($this->any())->method('getWidth')->will($this->returnValue(100));
		$this->mockImage->expects($this->any())->method('getHeight')->will($this->returnValue(100));
		$this->mockThumbnail = $this->getMockBuilder('\_OurBrand_\Quiz\Domain\Model\ImageResource')->disableOriginalConstructor()->getMock();
	}

	/**
	 * @test
	 */
	public function ratioModeDefaultsToInset() {
		$this->mockImage->expects($this->once())->method('getThumbnail')->with(50, 100, 'inset')->will($this->returnValue($this->mockThumbnail));
		$this->viewHelper->render($this->mockImage, 50);
	}

	/**
	 * @test
	 */
	public function ratioModeIsOutboundIfAllowCroppingIsTrue() {
		$this->mockImage->expects($this->once())->method('getThumbnail')->with(50, 100, 'outbound')->will($this->returnValue($this->mockThumbnail));
		$this->viewHelper->render($this->mockImage, 50, NULL, TRUE);
	}
	
	/**
	 * @test
	 */
	public function thumbnailWidthDoesNotExceedImageWithByDefault() {
		$this->mockImage->expects($this->never())->method('getThumbnail');
		$this->viewHelper->render($this->mockImage, 456, NULL);
	}

	/**
	 * @test
	 */
	public function thumbnailHeightDoesNotExceedImageHeightByDefault() {
		$this->mockImage->expects($this->never())->method('getThumbnail');
		$this->viewHelper->render($this->mockImage, NULL, 123);
	}

	/**
	 * @test
	 */
	public function thumbnailWidthMightExceedImageWithIfAllowUpScalingIsTrue() {
		$this->mockTagBuilder->expects($this->once())->method('render');
		$this->mockTagBuilder->expects($this->once())->method('addAttributes')->with(array('width' => 456,'height' => null,'src'=>null));
		//$this->mockImage->expects($this->once())->method('getAspectRatio')->will($this->returnValue(1));
		//$this->mockThumbnail->expects($this->any())->method('getWidth')->will($this->returnValue(456));
		//$this->mockThumbnail->expects($this->any())->method('getHeight')->will($this->returnValue(100));
		$this->mockImage->expects($this->once())->method('getThumbnail')->with(456, 100, 'inset')->will($this->returnValue($this->mockThumbnail));
		$this->viewHelper->render($this->mockImage, 456, NULL, FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function thumbnailHeightMightExceedImageHeightIfAllowUpScalingIsTrue() {
		$this->mockTagBuilder->expects($this->once())->method('render');
		$this->mockTagBuilder->expects($this->once())->method('addAttributes')->with(array('width' => null,'height' => 456,'src'=>null));
		$this->mockImage->expects($this->once())->method('getThumbnail')->with(100, 456, 'inset')->will($this->returnValue($this->mockThumbnail));
		$this->viewHelper->render($this->mockImage, NULL, 456, FALSE, TRUE);
	}
}
