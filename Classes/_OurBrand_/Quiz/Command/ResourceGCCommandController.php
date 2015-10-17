<?php
namespace _OurBrand_\Quiz\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ResourceGCCommandController extends \TYPO3\Flow\Cli\CommandController {


	/**
	 * @Flow\Inject
	 * @var \_OurBrand_\Quiz\Domain\Repository\ImageResourceRepository

	protected $imageRepository;
	 */



	/**
	 * An example command
	 *
	 * Clean up unused resources.
     *
	 * @return void
	 */
	public function cleanCommand() {

		$persistentResourcesStorageBaseUri = FLOW_PATH_DATA . 'Persistent/Resources/';

		$directoryHandle = opendir($persistentResourcesStorageBaseUri);
		while($file = readdir($directoryHandle)) {
			if(preg_match('/^[a-z0-9]{40}$/', $file) && ($this->imageRepository->findByResourcePointer($file) == null)) {
				unlink($persistentResourcesStorageBaseUri.'/'.$file);
			}
		}
		// TODO: Clean up static links

		$this->outputLine('Cleanup finished');
	}

}

