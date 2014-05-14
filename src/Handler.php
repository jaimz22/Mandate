<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 5/14/14
 * @time: 12:08 PM
 */

namespace VertigoLabs\CommandRunner;


abstract class Handler
{
	abstract public function handle(Command $command);
} 