<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 8/5/2014
 * @time: 1:18 AM
 */

namespace VertigoLabs\Mandate\Interfaces;


interface CommandInterface
{
	public function addHandler(HandlerInterface $handler);
	public function yieldHandlers();
	public function performSuccessCallback();
	public function performFailureCallback($exception);
	public function onSuccess(callable $success);
	public function onFailure(callable $failure);
} 