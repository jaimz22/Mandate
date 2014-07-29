<?php
/**
 * @author: James Murray <jaimz@vertigolabs.org>
 * @copyright: James Murray 2014
 * @date: 5/15/14
 * @time: 9:31 AM
 *
 * DISCLAIMER: This software is provided free of charge, and may be distributed.
 * It is not the fault of the author if this software causes damages, loss of data
 * loss of life, pregnant girlfriends, deep horrible depression, cupcakes, good times
 * with friends.
 */

namespace VertigoLabs\Mandate;


class Artifact
{
	private $name,$value;
	public function __construct($name,$value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getValue()
	{
		return $this->value;
	}
} 