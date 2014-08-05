<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 8/5/2014
 * @time: 1:20 AM
 */

namespace VertigoLabs\Mandate\Interfaces;


use VertigoLabs\Mandate\Artifact;

interface ArtifactEmittingHandlerInterface extends HandlerInterface
{
	/**
	 * Instructs the handler to produce an artifact
	 * with the specified name.
	 *
	 * @param $artifactName
	 *
	 * @return void
	 */
	public function produceArtifact($artifactName);

	/**
	 * Return the specified artifact, or an array of
	 * artifacts.
	 * @param string|array $artifactName
	 *
	 * @return Artifact|Artifact[]
	 */
	public function getArtifacts($artifactName=null);
} 