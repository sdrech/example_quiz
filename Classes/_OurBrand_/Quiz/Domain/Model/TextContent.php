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
class TextContent
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
     * @ORM\Column(length=4096)
	 */
	protected $text;

	/**
	 * @var string
	 */
	protected $copyright;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\TextType
	 * @ORM\ManyToOne
	 */
	protected $type;

	/**
	 * @param string $copyright
	 * @return \_OurBrand_\Quiz\Domain\Model\TextContent $this
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
	 * @param string $text
	 * @return \_OurBrand_\Quiz\Domain\Model\TextContent $this
	 */
	public function setText($text) {
		$this->text = $text;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @param string $title
	 * @return \_OurBrand_\Quiz\Domain\Model\TextContent $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\TextType $type
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @return @return \_OurBrand_\Quiz\Domain\Model\TextType
	 */
	public function getType() {
		return $this->type;
	}

}