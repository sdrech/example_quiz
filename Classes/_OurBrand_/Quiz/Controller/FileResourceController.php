<?php
namespace _OurBrand_\Quiz\Controller;

/*																		*
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".	   *
 *																		*
 *																		*/

use _OurBrand_\Quiz\Domain\Model\FileResource;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Resource\ResourceManager;


class FileResourceController extends AbstractController {
	
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;
	
	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\FileResourceRepository
	 * @Flow\Inject
	 */
	protected $fileResourceRepository;
	
	/**
	 * Edit fileResource
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $file
	 */
	public function editAction($file) {
		$filetype = $this->request->hasArgument('filetype') ? $this->request->getArgument('filetype') : 'file';
		if ($file->getOriginalresource() == null) {
			$this->redirect('new', null, null, array('file' => $file, 'filetype' => $filetype));
		}
		switch ($filetype) {
			case 'audio':
				$icontype = 'music';
				break;
			case 'pdf':
				$icontype = 'paperclip';
				break;
				default:
				$icontype = 'paperclip';
		}
		$this->view->assign('file', $file);
		$this->view->assign('filetype', $filetype);
		$this->view->assign('icontype', $icontype);
		$this->view->assign('mode', $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : '');
		$this->view->assign('renderPartial', ($this->request->hasArgument('renderPartial') ? $this->request->getArgument('renderPartial') : ''));
	}
	
	/**
	 * New asset form
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $file
	 * @return void
	 */
	public function newAction($file=null) {
		$filetype = $this->request->hasArgument('filetype') ? $this->request->getArgument('filetype') : 'file';
		switch ($filetype) {
			case 'audio':
				$icontype = 'music';
				break;
			case 'pdf':
				$icontype = 'paperclip';
				break;
				default:
				$icontype = 'paperclip';
		}
		
		$this->view->assign('file', $file);
		$this->view->assign('filetype', $filetype);
		$this->view->assign('icontype', $icontype);
	}
	
	/**
	 * Initialization for createAction
	 *
	 * @return void
	 */
	protected function initializeUploadAction() {
		$fileMappingConf = $this->arguments->getArgument('file')->getPropertyMappingConfiguration();
		$fileMappingConf->allowProperties('description', 'originalResource','title','type');
		$fileMappingConf->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
	}

	/**
	 * Upload a new asset. No redirection and 	no response body, no flash message, for use by plupload (or similar).
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $file
	 * @return string
	 */
	public function uploadAction($file) {
		
		$renderPartial = $this->request->hasArgument('renderPartial') ? $this->request->getArgument('renderPartial') : '';
		$mode = $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : '';
		$filetype = $this->request->hasArgument('filetype') ? $this->request->getArgument('filetype') : 'file';
		if ($filetype == 'pdf') {
			if ($file->getOriginalResource()->getFileextension() !== 'pdf') {
				$this->forward('new', NULL, NULL, array('file' => $file,'mode'=>$mode,'renderPartial'=>$renderPartial, 'filetype' => $filetype));
			}
		}
		
		if ($this->persistenceManager->isNewObject($file)) {
			$this->fileResourceRepository->add($file);
		} else {
			$this->fileResourceRepository->update($file);
		}
		
		$this->forward('edit', NULL, NULL, array('file' => $file,'mode'=>$mode,'renderPartial'=>$renderPartial, 'filetype' => $filetype));
	}
	
	/**
	 * Update action for fileresource
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $file
	 */
	public function updateAction($file) {
		// update file
		
						
		$this->fileResourceRepository->update($file);
		if (!$this->request->hasArgument('ajax')) {
			$this->forward('edit', null, null, array('file' => $file));
		}
		return '1';
	}
	
	/**
	 * Delete fileResource
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\FileResource $file
	 */
	public function deleteAction($file) {
		$filetype = $this->request->hasArgument('filetype') ? $this->request->getArgument('filetype') : 'file';
		
		$file->setOriginalResource(null);
		$file->setTitle('');
		$file->setDescription('');
		
		$this->fileResourceRepository->update($file);
		if (!$this->request->hasArgument('ajax')) {
			$this->redirect('new', null, null, array('file' => $file, 'filetype' => $filetype));
		}
		return '1';
	}
}

?>
