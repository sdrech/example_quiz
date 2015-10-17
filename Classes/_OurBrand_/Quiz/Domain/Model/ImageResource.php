<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Exception;

/**
 * Represents an image.
 *
 * @Flow\Entity
 */
class ImageResource {

	/**
	 * @var \_OurBrand_\Quiz\Service\ImageService
	 * @Flow\Inject
	 */
	protected $imageService;

	/**
	 * @var \TYPO3\Flow\Resource\Resource
	 * @ORM\ManyToOne
	 */
	protected $originalResource;
	
		
	/**
	 * @var string
	 */
	protected $title;
	
	/**
	 * @var string
	 */
	protected $alt;
	

	/**
	 * @var int
	 */
	protected $width;


	/**
	 * @var int
	 */
	protected $height;

	/**
	 * @var string
	 */
	protected $copyright;

	/**
	 * one of PHPs IMAGETYPE_* constants
	 *
	 * @var integer
	 */
	protected $type;


	public function __construct(){
		$this->originalResource = null;
		$this->width = 0;
		$this->height = 0;
		$this->title = '';
		$this->alt = '';
		$this->copyright = '';
		$this->type = 0;
	}

	/**
	 * @param int $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @param string $title
	 */
	public function setTitle($title='') {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @param int $type
	 */
	public function setType($type='') {
		$this->type = $type;
	}
		
	/**
	 * @param string $alt
	 */
	public function setAlt($alt='') {
		$this->alt = $alt;
	}

	/**
	 * @return string
	 */
	public function getAlt() {
		return $this->alt;
	}
	
	
	
	/**
	 * Edge / aspect ratio of the image
	 *
	 * @param boolean $respectOrientation If false (the default), orientation is disregarded and always a value >= 1 is returned (like usual in "4 / 3" or "16 / 9")
	 * @return float
	 */
	public function getAspectRatio($respectOrientation = FALSE) {
		$aspectRatio = $this->getWidth() / $this->getHeight();
		if ($respectOrientation === FALSE && $aspectRatio < 1) {
			$aspectRatio = 1 / $aspectRatio;
		}

		return $aspectRatio;
	}
	
	
	/**
	 * @param \TYPO3\Flow\Resource\Resource $originalResource
	 */
	public function setOriginalResource($originalResource) {
		$this->originalResource = $originalResource;
		//$this->setResource($originalResource);
		$this->lastModified = new \DateTime();
		if($this->originalResource != null){
			$this->initialize();
		}
	}

	/**
	 * @return \TYPO3\Flow\Resource\Resource
	 */
	public function getOriginalResource() {
		return $this->originalResource;
	}
	
	/**
	 * One of PHPs IMAGETYPE_* constants that reflects the image type
	 *
	 * @see http://php.net/manual/image.constants.php
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param int $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param string $copyright
	 */
	public function setCopyright($copyright) {
		$this->copyright = $copyright;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCopyright() {
		return $this->copyright;
	}

	/**
	 * File extension of the image without leading dot.
	 * @see http://www.php.net/manual/function.image-type-to-extension.php
	 *
	 * @return string
	 */
	public function getFileExtension() {
		return image_type_to_extension($this->type, FALSE);
	}
	
	/**
	 * Calculates image width, height and type from the image resource
	 * The getimagesize() method may either return FALSE; or throw a Warning
	 * which is translated to a \TYPO3\Flow\Error\Exception by Flow. In both
	 * cases \TYPO3\Media\Exception\ImageFileException should be thrown.
	 *
	 * @throws \TYPO3\Media\Exception\ImageFileException
	 * @return void
	 */
	protected function initialize() {
		try {
			$imageSize = getimagesize('resource://' . $this->originalResource->getResourcePointer()->getHash());
			if ($imageSize === FALSE) {
				throw new \TYPO3\Flow\Exception('The given resource was not a valid image file', 1336662898);
			}
			$this->width = (integer)$imageSize[0];
			$this->height = (integer)$imageSize[1];
			$this->type = (integer)$imageSize[2];
		} catch(\TYPO3\Flow\Exception $exception) {
			throw $exception;
		} catch(\TYPO3\Flow\Exception $exception) {
			$exceptionMessage = 'An error with code "' . $exception->getCode() . '" occured when trying to read the image: "' . $exception->getMessage() . '"';
			throw new \TYPO3\Flow\Exception($exceptionMessage, 1336663970);
		}
	}
	
	/**
	 * Returns a thumbnail of this image.
	 *
	 * If maximum width/height is not specified or exceed the original images size,
	 * width/height of the original image is used
	 *
	 * Note: The image variant that will be created is intentionally not added to the
	 * imageVariants collection of this image. If you want to create a persisted image
	 * variant, use createImageVariant() instead.
	 *
	 * @param integer $maximumWidth
	 * @param integer $maximumHeight
	 * @param string $ratioMode Whether the resulting image should be cropped if both edge's sizes are supplied that would hurt the aspect ratio.
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @see \TYPO3\Media\Domain\Service\ImageService::transformImage()
	 */
	public function getThumbnail($maximumWidth = NULL, $maximumHeight = NULL, $ratioMode = 'inset') {
		if($maximumWidth == NULL || $maximumHeight == NULL){
			if(intval($maximumWidth) > 0){
				$aspRatio = $this->getAspectRatio(true);
				$maximumHeight = (int)($maximumWidth / $aspRatio);
			} elseif(intval($maximumHeight) > 0){
				$aspRatio = $this->getAspectRatio(true);
				$maximumWidth = (int)($maximumHeight / $aspRatio);
			}
			
		}
		$processingInstructions = array(
			array(
				'command' => 'thumbnail',
				'options' => array(
					'size' => array(
						'width' => intval($maximumWidth ?$maximumWidth: $this->width),
						'height' => intval($maximumHeight ?$maximumHeight: $this->height)
					),
					'mode' => $ratioMode
				),
			),
		);
		return $this->transformImage($processingInstructions);
	}
	
	private function transformImage(array $processingInstructions){
		$thumb = new ImageResource();
		$thumb->setOriginalResource($this->imageService->transformImage($this, $processingInstructions));
		return $thumb;
	}
	
	/**
	 * Crop this image.
	 *
	 * If maximum width/height is not specified or exceed the original images size,
	 * width/height of the original image is used
	 *
	 * @param integer $startX
	 * @param integer $startY
	 * @param integer $maximumWidth
	 * @param integer $maximumHeight
	 * @param string $ratioMode Whether the resulting image should be cropped if both edge's sizes are supplied that would hurt the aspect ratio.
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @see \TYPO3\Media\Domain\Service\ImageService::transformImage()
	 */
	public function cropImage($startX = 0, $startY = 0, $width = NULL, $height = NULL) {
		$processingInstructions = array(
			array(
				'command' => 'crop',
				'options' => array(
					'start' => array(
						'x' => $startX,
						'y' => $startY
					),
					'size' => array(
						'width' => intval($width ?: $this->width),
						'height' => intval($height ?: $this->height)
					),
				),
			),
		);
		/** @todo remove old resource pointer */
		$this->setOriginalResource($this->imageService->transformImage($this, $processingInstructions));
		//$this->resource = null;
		//$this->originalResource($tmpRes);
		return $this;
	}
	
	/**
	 * Resize this image.
	 *
	 * If maximum width/height is not specified or exceed the original images size,
	 * width/height of the original image is used
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 * @see \TYPO3\Media\Domain\Service\ImageService::transformImage()
	 */
	public function resizeImage($width = NULL, $height = NULL) {
		if($width == NULL || $height == NULL){
			if(intval($width) > 0){
				$aspRatio = $this->getAspectRatio(true);
				$height = (int)($width / $aspRatio);
			} elseif(intval($height) > 0){
				$aspRatio = $this->getAspectRatio(true);
				$width = (int)($height * $aspRatio);
			}
			
		}
		$processingInstructions = array(
			array(
				'command' => 'resize',
				'options' => array(
					'size' => array(
						'width' => intval($width ?: $this->width),
						'height' => intval($height ?: $this->height)
					),
				),
			),
		);
		/** @todo remove old resource pointer */
		//$this->resource = null;
		$this->setOriginalResource($this->imageService->transformImage($this, $processingInstructions));
		//return $this;
	}



	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\ImageResource
	 */
	public function copy() {
		$newImage = new ImageResource();

		if(is_object($this->getOriginalResource())) {
			$resource = new \TYPO3\Flow\Resource\Resource();
			$resource->setFilename($this->getOriginalResource()->getFilename());
			$resource->setResourcePointer($this->getOriginalResource()->getResourcePointer());

			$newImage->setOriginalResource($resource);
		}

		return $newImage;
	}

	/**
	 * Call after __clone to set
	 * new references and stuff. Do not
	 * use clone on this model Use copy() instead.
	 */
	public function postClone() {
		$this->originalResource = null;
		//$this->resource = null;
	}


}
