<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 8/5/2014
 * @time: 1:19 AM
 */

namespace VertigoLabs\Mandate\Interfaces;


interface HandlerInterface
{
	/**
	 * Handle the actions necessary to carry out the
	 * instructions of a command
	 *
	 * @param CommandInterface $command
	 *
	 * @return mixed
	 */
	public function handle(CommandInterface $command);
}