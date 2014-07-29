<?php
/**
 * @author: James Murray <jaimz@vertigolabs.org>
 * @copyright: James Murray 2014
 * @date: 5/14/14
 * @time: 11:37 AM
 *
 * DISCLAIMER: This software is provided free of charge, and may be distributed.
 * It is not the fault of the author if this software causes damages, loss of data
 * loss of life, pregnant girlfriends, deep horrible depression, cupcakes, or good times
 * with friends.
 */

namespace VertigoLabs\Mandate;


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
	/**
	 * @var array
	 */
	private $boundArtifacts = [];
	private $boundWait = [];

	public function addHandler(Handler $handler)
	{
		if(!($this->handlers instanceof \SplQueue)){
			$this->handlers = new \SplQueue();
			$this->handlers->setIteratorMode(\SplQueue::IT_MODE_FIFO | \SplQueue::IT_MODE_DELETE);
		}
		$this->handlers->enqueue($handler);
		return $this;
	}

	/**
	 * @return Handler
	 * @throws \Exception
	 */
	public function yieldHandlers()
	{
		if(!($this->handlers instanceof \SplQueue)){
			throw new \Exception('Commands can not be executed without a handler');
		}
		while($this->handlers->count() > 0){
			yield $this->handlers->dequeue();
		}
	}

	public function bindArtifact($property, $artifactName, $waitForAvailability = false)
	{
		$this->boundArtifacts[$property] = $artifactName;
		$this->boundWait[$artifactName] = $waitForAvailability;
		return $this;
	}

	public function getArtifactWaitList()
	{
		return array_keys(array_filter($this->boundWait));
	}

	/**
	 * @param Artifact[] $artifacts
	 */
	public function setArtifacts($artifacts)
	{
		$boundArtifacts = array_flip($this->boundArtifacts);
		$availableArtifacts = array_intersect_key($boundArtifacts,$artifacts);
		foreach($availableArtifacts as $artifactName=>$propertyName) {
			$this->boundArtifacts[$propertyName] = $artifacts[$artifactName]->getValue();
		}
	}

	public function getArtifactBindings()
	{
		return $this->boundArtifacts;
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
		if (array_key_exists($field,$this->boundArtifacts)) {
			return $this->boundArtifacts[$field];
		}
		if (property_exists($this,$field)) {
			$reader = function & ($object, $property) {
				$value = & \Closure::bind(function & () use ($property) {
					return $this->$property;
				}, $object, $object)->__invoke();

				return $value;
			};
			return $reader($this,$field);
		}
		return null;
	}
} 