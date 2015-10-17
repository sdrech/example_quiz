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
 *
 * @Flow\Entity
 */
class AnswerData {
	/**
	 * @var \_OurBrand_\Quiz\Domain\Model\Answer
	 * @ORM\ManyToOne(inversedBy="answerDatas")
	 */
	protected $answer;

	/**
	 * @var string
	 * @ORM\Column(length=4096)
	 */
	protected $data;

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Answer $answer
	 */
	public function setAnswer($answer) {
		$this->answer = $answer;
	}

	/**
	 * @return \_OurBrand_\Quiz\Domain\Model\Answer
	 */
	public function getAnswer() {
		return $this->answer;
	}

	/**
	 * @param string $data
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}


}
?>