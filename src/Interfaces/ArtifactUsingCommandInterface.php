<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 8/5/2014
 * @time: 1:25 AM
 */

namespace VertigoLabs\Mandate\Interfaces;


interface ArtifactUsingCommandInterface extends CommandInterface
{
	public function bindArtifact($propertyName, $artifactName, $waitForAvailability=false);
	public function getArtifactWaitList();
	public function setArtifacts($artifacts);
	public function getArtifactBindings();
} 