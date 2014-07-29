<?php
/**
 * @author: James Murray <jaimz@vertigolabs.org>
 * @copyright: James Murray 2014
 * @date: 5/14/14
 * @time: 12:08 PM
 *
 * DISCLAIMER: This software is provided free of charge, and may be distributed.
 * It is not the fault of the author if this software causes damages, loss of data
 * loss of life, pregnant girlfriends, deep horrible depression, cupcakes, or good times
 * with friends.
 */

namespace VertigoLabs\Mandate;

/**
 * Class Handler
 * @package VertigoLabs\Mandate
 *
 * @property array artifacts
 */
abstract class Handler
{
	private $artifacts = [];

	abstract public function handle(Command $command);

	public function produceArtifact($name)
	{
		$this->artifacts[$name] = false;
		return $this;
	}

	protected function emitArtifact($name,$value)
	{
		if (!array_key_exists($name,$this->artifacts)) {
			throw new \Exception('can not emit unregistered artifact "'.$name.'"');
		}
		$artifact = new Artifact($name,$value);
		$this->artifacts[$name] = $artifact;
	}

	/**
	 * @param null|string|array $name
	 *
	 * @return Artifact|Artifact[]
	 */
	public function getArtifacts($name=null)
	{
		if (is_array($name)) {
			return array_intersect_key($this->artifacts,array_flip($name));
		}
		if (!is_null($name) && array_key_exists($name,$this->artifacts)){
			return $this->artifacts[$name];
		}
		return $this->artifacts;
	}
} 