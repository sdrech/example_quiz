<?php
namespace _OurBrand_\Quiz\Controller;

/*																		*
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".	   *
 *																		*
 *																		*/

use _OurBrand_\Quiz\Domain\Model\ImageResource;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Resource\ResourceManager;


class ImageResourceController extends AbstractController {
	
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;
	
	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ImageResourceRepository
	 * @Flow\Inject
	 */
	protected $imageResourceRepository;
	
	/**
	 * Image edit action
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function editAction($image) {
		if ($image->getOriginalresource() == null) {
			$this->redirect('new', null, null, array('image' => $image));
		}
		$thumbWidth = $this->request->hasArgument('thumbWidth') ? $this->request->getArgument('thumbWidth') : '';
		$thumbHeight = $this->request->hasArgument('thumbHeight') ? $this->request->getArgument('thumbHeight') : '';
		if (intval($thumbWidth) < 180 && intval($thumbHeight) < 180) {
			$thumbWidth = 400;
		}
		
		$this->view->assign('container', $this->request->hasArgument('container') ? $this->request->getArgument('container') : '');
		$this->view->assign('image', $image);
		$this->view->assign('mode', $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : '');
		$this->view->assign('imageContainer', $this->request->hasArgument('imgcon') ? $this->request->getArgument('imgcon') : array());
		$this->view->assign('renderPartial', ($this->request->hasArgument('renderPartial') ? $this->request->getArgument('renderPartial') : ''));

		$this->view->assign('thumbWidth', $thumbWidth);
		$this->view->assign('thumbHeight', $thumbHeight);
	}
	
	/**
	 * Image crop action
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function cropAction($image) {
		$this->view->assign('image', $image);
	}
	
	/**
	 * New asset form
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 * @return void
	 */
	public function newAction($image=null) {
		$this->view->assign('image', $image);
	}
	

	/**
	 * Initialization for uploadAction
	 *
	 * @return void
	 */
	protected function initializeUploadAction() {
		$imageMappingConf = $this->arguments->getArgument('image')->getPropertyMappingConfiguration();
		$imageMappingConf->allowProperties('copyright', 'title', 'originalResource','type');
		$imageMappingConf->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
	}

	/**
	 * Upload a new asset. No redirection and 	no response body, no flash message, for use by plupload (or similar).
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 * @return string
	 */
	public function uploadAction($image) {
		$dontcrop = intval($this->request->hasArgument('dontcrop') ? $this->request->getArgument('dontcrop') : 0);
		$maxwidth = intval($this->request->getArgument('maxwidth') > 0 ? $this->request->getArgument('maxwidth') : 800);
		$maxheight = intval($this->request->hasArgument('maxheight') ? $this->request->getArgument('maxheight') : 800);
		$minwidth = intval($this->request->hasArgument('minwidth') ? $this->request->getArgument('minwidth') : 0);
		$minheight = intval($this->request->hasArgument('minheight') ? $this->request->getArgument('minheight') : 0);
		$thumbWidth = intval($this->request->getArgument('thumbWidth') > 0 ? $this->request->getArgument('thumbWidth') : '');
		$thumbHeight = intval($this->request->getArgument('thumbHeight') > 0 ? $this->request->getArgument('thumbHeight') : '');
		$imageContainer = $this->request->hasArgument('imgcontainer') ? $this->request->getArgument('imgcontainer') : array();
		$renderPartial = $this->request->hasArgument('renderPartial') ? $this->request->getArgument('renderPartial') : '';
		
		if ($minwidth > 0 || $minheight > 0) {
			if ($image->getWidth() < $minwidth) {
				$errorMsg = $this->translateById(
					'imageresource.upload.error.dimensions',
					array(
						'uploaded' => $image->getWidth() . 'x' . $image->getHeight(),
						'required' => $minwidth . 'x' . $minheight
					)
				);
				$this->addFlashMessage($errorMsg);
				$this->redirect('new');
			} elseif ($image->getHeight() < $minheight) {
				$errorMsg = $this->translateById(
					'imageresource.upload.error.dimensions',
					array(
						'uploaded' => $image->getWidth() . 'x' . $image->getHeight(),
						'required' => $minwidth . 'x' . $minheight
					)
				);
				$this->addFlashMessage($errorMsg);
				$this->redirect('new');
			}
		}
		if ($image->getWidth() > $maxwidth) {
			$image->resizeImage($maxwidth);
		} elseif ($image->getHeight() > $maxheight) {
			$image->resizeImage(null, $maxheight);
		}
		if ($this->persistenceManager->isNewObject($image)) {
			$this->imageResourceRepository->add($image);
		} else {
			$this->imageResourceRepository->update($image);
		}
		if ($dontcrop == 1) {
			$this->forward('edit', NULL, NULL, array('image' => $image, 'mode' => 'crop','thumbWidth'=> $thumbWidth, 'thumbHeight' => $thumbHeight, 'imgcon' => $imageContainer, 'renderPartial' => $renderPartial, 'dontcrop' => $dontcrop));
		} else {
			$this->forward('crop', NULL, NULL, array('image' => $image, 'thumbWidth'=> $thumbWidth, 'thumbHeight' => $thumbHeight, 'imgcon' => $imageContainer, 'renderPartial' => $renderPartial, 'dontcrop' => $dontcrop));
		}
	}

	/**
	 * Update image action
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function updateAction($image) {
		$renderPartial = $this->request->hasArgument('renderPartial') ? $this->request->getArgument('renderPartial') : '';

		// update image with cropping instructions, if any
		$processInstructions = $this->request->hasArgument('processinstructions') ? $this->request->getArgument('processinstructions') : array();
		$imgCon = $this->request->hasArgument('imgcontainer') ? $this->request->getArgument('imgcontainer') : array();
		if (isset($processInstructions['start']) && is_array($processInstructions['start']) && is_array($processInstructions['size'])) {
			$image->cropImage(
				$processInstructions['start']['x'],
				$processInstructions['start']['y'],
				$processInstructions['size']['width'],
				$processInstructions['size']['height']
			);
		}
		
		// update image
		$this->imageResourceRepository->update($image);
		if (!$this->isJson) {
			$this->forward(
				'edit',
				null,
				null,
				array(
					'image' => $image,
					'mode' => $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : array(),
					'imgcon' => $imgCon,
					'renderPartial' => $renderPartial
				)
			);
		}
		return '1';
	}
	
	/**
	 * Reset image object
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\ImageResource $image
	 */
	public function deleteAction($image) {
		$image->setOriginalResource(null);
		$image->setTitle('');
		$image->setCopyright('');
		$image->setAlt('');
		$image->setType('');
		$image->setHeight(0);
		$image->setWidth(0);

		$this->imageResourceRepository->update($image);
		if (!$this->request->hasArgument('ajax')) {
			$this->redirect('new', null, null, array('image' => $image));
		}
		return '1';
	}
}

?>
