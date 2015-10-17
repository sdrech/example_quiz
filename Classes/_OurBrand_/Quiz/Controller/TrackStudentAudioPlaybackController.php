<?php
namespace _OurBrand_\Quiz\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use _OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;


class TrackStudentAudioPlaybackController extends AbstractController {
	
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
    protected $resourceManager;
    
    /**
	 * @var \_OurBrand_\Quiz\Domain\Repository\TrackStudentAudioPlaybackRepository
	 * @Flow\Inject
	 */
	protected $trackStudentAudioPlaybackRepository;
	
	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;
	
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;
	
	/**
	 * Init tracker if in exam mode
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return json
	 */
	 public function getTrackerAction($exercise) {
	 	$audioSrc = '';
	 	if($exercise->getSoundFile() && $exercise->getSoundFile()->getOriginalResource()) {
	 		$audioSrc = base64_encode($this->resourcePublisher->getPersistentResourceWebUri($exercise->getSoundFile()->getOriginalResource()));
	 	}
	 	if($this->studentQuizSession){
		 	$tracker = $this->trackStudentAudioPlaybackRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();
		 	if(!is_a($tracker,'\_OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback')){
		 		$tracker = new TrackStudentAudioPlayback();
		 		$tracker->setExercise($exercise);
		 		$tracker->setStudentQuizSession($this->studentQuizSession);
		 		
		 		$tracker->setTimeElapsed(0);
		 		$tracker->setStatus(0);
		 		
		 		$this->trackStudentAudioPlaybackRepository->add($tracker);
		 		$this->persistenceManager->persistAll();
		 	}
		 	
		 	return json_encode(
		 		array(
		 			'elapsedTime' => $tracker->getTimeElapsed(),
		 			'status' => $tracker->getStatus(),
		 			'trackerId' => $this->persistenceManager->getIdentifierByObject($tracker),
		 			'md5' => $audioSrc, // not a md5 code, just base64 path to audio file
		 		)
		 	);
	 	}
	 	
	 	return json_encode(
	 		array(
	 			// set status to 0 when not in studentQuizSession
	 	 		'status' => 0,
	 	 		'md5' => $audioSrc, // not a md5 code, just base64 path to audio file
	 		)
	 	);
	 	
	 }
	 
	 
	/**
	 * Init tracker if in exam mode
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 *
	 * @return json
	 */
	 public function getDictationAudioAction($exercise) {
	 	$audioFiles = array();
	 	foreach($exercise->getSnippets() as $snippet){
		 	if($snippet->getAudio() && $snippet->getAudio()->getOriginalResource()) {
		 		$audioFiles[] = array(
		 			'src' => base64_encode($this->resourcePublisher->getPersistentResourceWebUri($snippet->getAudio()->getOriginalResource())),
		 		);
		 	}
	 	}
	 	if($this->studentQuizSession){
		 	$tracker = $this->trackStudentAudioPlaybackRepository->findBySessionAndExercise($this->studentQuizSession, $exercise)->getFirst();
		 	if(!is_a($tracker,'\_OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback')){
		 		$tracker = new TrackStudentAudioPlayback();
		 		$tracker->setExercise($exercise);
		 		$tracker->setStudentQuizSession($this->studentQuizSession);
		 		
		 		$tracker->setTimeElapsed(0);
		 		$tracker->setStatus(0);
		 		
		 		$this->trackStudentAudioPlaybackRepository->add($tracker);
		 		$this->persistenceManager->persistAll();
		 	}
		 	
		 	return json_encode(
		 		array(
		 			'elapsedTime' => $tracker->getTimeElapsed(),
		 			'status' => $tracker->getStatus(),
		 			'trackerId' => $this->persistenceManager->getIdentifierByObject($tracker),
		 			'audioFiles' => $audioFiles
		 		)
		 	);
	 	}
	 	
	 	return json_encode(
	 		array(
	 			// set status to 0 when not in studentQuizSession
	 	 		'status' => 0,
	 	 		'audioFiles' => $audioFiles,
	 		)
	 	);
	 	
	 }
	 
	 /**
	  * Update progression on playback, updated by trackerId.
	  *
	  */
	 public function updateTrackerAction() {
	 	$trackerId = $this->request->getArgument('trackerId');
	 	$tracker = $this->persistenceManager->getObjectByIdentifier($trackerId, '\_OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback');
	 	if(is_a($tracker,'\_OurBrand_\Quiz\Domain\Model\TrackStudentAudioPlayback')) {
	 		$elapsedTime = $this->request->getArgument('elapsedTime');
	 		$status = $this->request->getArgument('status');
	 		$tmpTime = $tracker->getTimeElapsed();
	 		// only update if new time is larger then the old one...
	 		if(intval($elapsedTime) > $tmpTime){
	 			$tracker->setTimeElapsed($elapsedTime);
	 		}
	 		$tracker->setStatus($status);
	 		
	 		$this->trackStudentAudioPlaybackRepository->update($tracker);
	 		$this->persistenceManager->persistAll();
	 	
			return json_encode(
		 		array(
		 			'elapsedTime' => $tracker->getTimeElapsed(),
		 			'status' => $tracker->getStatus(),
		 			'trackerId' => $this->persistenceManager->getIdentifierByObject($tracker)
		 		)
		 	);
	 	}
	}
}
