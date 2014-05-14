<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/13/14
 * @time: 5:16 PM
 */

namespace VertigoLabs\CommandRunner;


class CommandRunner
{
	private $commandQueue;
	private $anonPriority = 0;

	public function __construct()
	{
		$this->commandQueue = new \SplPriorityQueue();
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
		while($this->commandQueue->count() > 0) {
			$command = $this->commandQueue->extract();
			foreach($command->yieldHandlers() as $handler) {
				try{
					$handler->handle($command);
					$command->performSuccessCallback();
				}catch (\Exception $e){
					$command->performFailureCallback($e);
				}
			}
		}
	}
}