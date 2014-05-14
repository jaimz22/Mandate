<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/14/14
 * @time: 11:37 AM
 */

namespace VertigoLabs\CommandRunner;


abstract class Command
{
	/**
	 * @var \SplQueue
	 */
	private $handlers;
	/**
	 * @var callable
	 */
	private $success;
	/**
	 * @var callable
	 */
	private $failure;

	public function addHandler(Handler $handler)
	{
		if (!($this->handlers instanceof \SplQueue)) {
			$this->handlers = new \SplQueue();
			$this->handlers->setIteratorMode(\SplQueue::IT_MODE_FIFO|\SplQueue::IT_MODE_DELETE);
		}
		$this->handlers->enqueue($handler);
		return $this;
	}

	public function yieldHandlers()
	{
		if (!($this->handlers instanceof \SplQueue)) {
			throw new \Exception('Commands can not be executed without a handler');
		}
		while($this->handlers->count() > 0)
		{
			yield $this->handlers->dequeue();
		}
	}

	public function performSuccessCallback()
	{
		if (is_callable($this->success)) {
			call_user_func($this->success);
		}
	}

	public function onSuccess(callable $success)
	{
		$this->success=$success;
		return $this;
	}

	public function performFailureCallback($e)
	{
		if (is_callable($this->failure)) {
			call_user_func($this->failure,$e,$this);
		}
	}

	public function onFailure(callable $failure)
	{
		$this->failure=$failure;
		return $this;
	}

	public function __get($field)
	{
		return $this->$field;
	}
} 