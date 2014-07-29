<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/14/14
 * @time: 12:08 PM
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
	 * @return mixed
	 */
	public function getArtifacts($name=null)
	{
		if (is_array($name)) {
			return array_intersect_assoc($this->artifacts,$name);
		}
		if (!is_null($name) && array_key_exists($name,$this->artifacts)){
			return $this->artifacts[$name];
		}
		return $this->artifacts;
	}
} 