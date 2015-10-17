<?php
namespace _OurBrand_\Quiz\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use _OurBrand_\Quiz\Annotations as _OurBrand_;
use Doctrine\ORM\Mapping as ORM;


/**
 * @Flow\ValueObject
 */
class ErrorType {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM\Column(nullable=TRUE)
	 */
	protected $description;

	/**
	 * @param string $title
	 * @param string $description
	 */
	public function __construct($title, $description) {
		$this->title = $title;
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

}
?>