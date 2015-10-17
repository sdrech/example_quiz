<?php
namespace _OurBrand_\Quiz\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "_OurBrand_.Quiz".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ExerciseService
{
	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\QuizRepository
	 * @Flow\Inject
	 */
	protected $quizRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\ExerciseRepository
	 * @Flow\Inject
	 */
	protected $exerciseRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\AnswerRepository
	 * @Flow\Inject
	 */
	protected $answerRepository;

	/**
	 * @var \_OurBrand_\Quiz\Domain\Repository\FileResourceRepository
	 * @Flow\Inject
	 */
	protected $fileResourceRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \_OurBrand_\Quiz\Service\QuizService
	 * @Flow\Inject
	 */
	protected $quizService;


	/**
	 * Previous given answers are removed before the new ones are stored.
	 *
	 * @param int $score
	 * @param int $status 0=uncompleted, 1=completed
	 * @param array $answers
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 */
	public function registerAnswer($score, $status, $answers, $exercise, $studentQuizSession) {

		// Clean up answers
		$previousAnswers = $this->answerRepository->findBySessionAndExercise($studentQuizSession, $exercise);
		if ($previousAnswers->count() > 0) {
			foreach ($previousAnswers as $previousAnswer) {
				$this->answerRepository->remove($previousAnswer);
			}
		}

		// Json file data.
		$resultsData = array('s' => $score);

		// Students time on this exercise.
		$answer = $this->getStudentsAnswerForExercise($studentQuizSession,$exercise);
		if(is_a($answer,'\_OurBrand_\Quiz\Domain\Model\Answer')){
			$startTimeHour = intval($studentQuizSession->getStartTime()->format('H'));
			$startTimeMinute = intval($studentQuizSession->getStartTime()->format('i'));
			$startTime = ($startTimeHour*60)+$startTimeMinute;
			$answerTimeHour = intval($answer->getAnswerTime()->format('H'));
			$answerTimeMinute = intval($answer->getAnswerTime()->format('i'));
			$answerTime = ($answerTimeHour*60)+$answerTimeMinute;

			$resultsData['t'] = ($answerTime-$startTime);
		}

		// Calculate minutes spent by the student
		$resultsData['mu'] = $this->calcMinutesSpent($studentQuizSession); // Minutes Used

		// Max score
		$resultsData['ms'] = $exercise->getMaxScore();

		// Save result data to json file.
		$this->quizService->addResultToQuizSessionResultsStatistics($studentQuizSession, $exercise, $resultsData);

		// Answer Data model.
		$answerData = new \_OurBrand_\Quiz\Domain\Model\AnswerData();
		$answerData->setData(serialize($answers));

		// Answer model.
		$answer = new \_OurBrand_\Quiz\Domain\Model\Answer();
		$answer->setExercise($exercise);
		$answer->setScore($score);
		$answer->addAnswerData($answerData);
		$answer->setStudentQuizSession($studentQuizSession);
		$answer->setAnswerTime(new \DateTime());
		$answer->setStatus($status);

		$this->answerRepository->add($answer);
	}

	/**
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 */
	public function updateExercise($exercise) {

		// @TODO : is Minutes and Seconds needed ?
		if ($exercise->getDuration()===0 && intval($exercise->getMinutes())>0){
			$duration = (int)$exercise->getMinutes() * 60 + (int)$exercise->getSeconds();
			$exercise->setDuration($duration);
		}

		$quiz = $exercise->getQuiz();
		$quiz->touch();
		$quiz->calculateDuration();
		$this->quizRepository->update($quiz);

		if (is_object($exercise->getPdfFile()) && !is_object($exercise->getPdfFile()->getOriginalResource())) {
			$pdFile = $exercise->getPdfFile();
			$exercise->setPdfFile(null);
			$this->fileResourceRepository->remove($pdFile);
		}

		if (is_object($exercise->getSoundFile()) && !is_object($exercise->getSoundFile()->getOriginalResource())) {
			$soundFile = $exercise->getSoundFile();
			$exercise->setSoundFile(null);
			$this->fileResourceRepository->remove($soundFile);
		}

		$this->exerciseRepository->update($exercise);
		$this->persistenceManager->persistAll();
	}

	/**
	 * Wrapper.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @param \_OurBrand_\Quiz\Domain\Model\Exercise $exercise
	 * @return \_OurBrand_\Quiz\Domain\Model\Answer
	 */
	protected function getStudentsAnswerForExercise($studentQuizSession,$exercise){
		$answer = $this->answerRepository->findBySessionAndExercise($studentQuizSession,$exercise)->getFirst();
		return $answer;
	}

	/**
	 * Calculate how many minutes are spent in total by student, check if finished, if not use current usage.
	 *
	 * @param \_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession
	 * @return int
	 */
	protected function calcMinutesSpent(\_OurBrand_\Quiz\Domain\Model\StudentQuizSession $studentQuizSession){
		$minutesSpent = 0;

		$startTimeHour = intval($studentQuizSession->getStartTime()->format('H'));
		$startTimeMinute = intval($studentQuizSession->getStartTime()->format('i'));
		$startTime = ($startTimeHour*60)+$startTimeMinute;

		$answers = $this->answerRepository->findBySession($studentQuizSession);
		foreach($answers as $answer){

			$answerTimeHour = intval($answer->getAnswerTime()->format('H'));
			$answerTimeMinute = intval($answer->getAnswerTime()->format('i'));
			$answerTime = ($answerTimeHour*60)+$answerTimeMinute;

			$minutesSpent += ($answerTime-$startTime);
		}

		if($minutesSpent<0){
			$minutesSpent = 0;
		}

		return $minutesSpent;
	}
}
