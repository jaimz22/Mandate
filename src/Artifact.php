<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/15/14
 * @time: 9:31 AM
 */

namespace VertigoLabs\CommandRunner;


class Artifact
{
	private $name,$value;
	public function __construct($name,$value)
	{
		$this->name = $name;
		$this->value = $value;
	}
} 