<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/13/14
 * @time: 5:16 PM
 */

namespace VertigoLabs\Mandate;


class Mandate
{
	private $commandQueue;
	private $anonPriority = 0;

	public function __construct()
	{
		$this->commandQueue = new \SplPriorityQueue();
		$this->commandQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
	}

	public function queue(Command $command,$priority=null)
	{
		if (is_null($priority)) {
			$priority=$this->anonPriority--;
		}
		$this->commandQueue->insert($command,$priority);
		return $this;
	}

	public function execute()
	{
		$this->commandQueue->rewind();
		$artifacts = [];
		while($this->commandQueue->count() > 0) {
			/** @var Command $command */
			$queue = $this->commandQueue->extract();
			$command = $queue['data'];
			$artifactWaitList = $command->getArtifactWaitList();
			if (!empty($artifactWaitList)) {
				if(empty($artifacts)) {
					if ($this->commandQueue->isEmpty()) {
						throw new \RuntimeException('Command Queue is empty without satisfying command artifacts');
					}
					$nextPriority = $this->commandQueue->top()['priority'];
					$this->commandQueue->insert($command,$nextPriority);
					continue;
				}else{
					foreach($artifactWaitList as $artifactName) {
						if (!isset($artifacts[$artifactName])) {
							if ($this->commandQueue->isEmpty()) {
								throw new \RuntimeException('Command Queue is empty without satisfying command artifacts');
							}
							$nextPriority = $this->commandQueue->top()['priority'];
							$this->commandQueue->insert($command,$nextPriority);
							continue 2;
						}
					}
				}
			}
			if (!empty($artifacts)) {
				$command->setArtifacts($artifacts);
			}
			/** @var Handler $handler */
			foreach($command->yieldHandlers() as $handler) {
				try{
					$handler->handle($command);
					$artifacts = array_merge($artifacts,$handler->getArtifacts());
					$command->performSuccessCallback();
				}catch (\Exception $e){
					$command->performFailureCallback($e);
				}
			}
		}
	}
}