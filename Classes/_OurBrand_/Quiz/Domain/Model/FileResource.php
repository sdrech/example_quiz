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
 */
class FileResource {


	/**
	 * @var \TYPO3\Flow\Resource\Resource
	 * @ORM\OneToOne
	*/
	protected $originalResource;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $type = '';

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
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param \TYPO3\Flow\Resource\Resource $originalResource
	 */
	public function setOriginalResource($originalResource) {
		$this->originalResource = $originalResource;
	}

	/**
	 * @return \TYPO3\Flow\Resource\Resource
	 */
	public function getOriginalResource() {
		return $this->originalResource;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\FileResource
	 */
	public function copy() {
		$newFile = new FileResource();

		$resource = new \TYPO3\Flow\Resource\Resource();
		$resource->setFilename($this->getOriginalResource()->getFilename());
		$resource->setResourcePointer($this->getOriginalResource()->getResourcePointer());

		$newFile->setOriginalResource($resource);
		return $newFile;
	}



}
