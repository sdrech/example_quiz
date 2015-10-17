<?php
namespace _OurBrand_\Quiz\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
use _OurBrand_\Quiz\Domain\Model;

/**
 * @Flow\Scope("singleton")
 */
class SubjectRepository extends Repository {


	protected $defaultOrderings = array(
		'title' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING
	);

}
