<?php
namespace TYPO3\FLOW3\Resource;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Model describing a resource
 *
 * @scope prototype
 * @entity
 */
class Resource {

	/**
	 * @var \TYPO3\FLOW3\Resource\ResourcePointer
	 * @ManyToOne
	 * @identity
	 */
	protected $resourcePointer;

	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface
	 */
	protected $publishingConfiguration;

	/**
	 * @var string
	 * @validate StringLength(maximum = 100)
	 * @identity
	 */
	protected $filename = '';

	/**
	 * @var string
	 * @validate StringLength(maximum = 100)
	 * @identity
	 */
	protected $fileExtension = '';

	/**
	 * Sets the filename
	 *
	 * @param string $filename
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setFileName($filename) {
		$pathInfo = pathinfo($filename);
		if (isset($pathInfo['extension'])) {
			$this->fileExtension = strtolower($pathInfo['extension']);
		} else {
			$this->fileExtension = '';
		}
		$this->filename = $pathInfo['filename'];
		if ($this->fileExtension !== '') {
			$this->filename .= '.' . $this->fileExtension;
		}
	}

	/**
	 * Gets the filename
	 *
	 * @return string The filename
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Returns the file extension used for this resource
	 *
	 * @return string The file extension used for this file
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getFileExtension() {
		return $this->fileExtension;
	}

	/**
	 * Returns the mime type for this resource
	 *
	 * @return string The mime type
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getMimeType() {
		return \TYPO3\FLOW3\Utility\FileTypes::getMimeTypeFromFilename('x.' . $this->getFileExtension());
	}

	/**
	 * Sets the resource pointer
	 *
	 * @param \TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setResourcePointer(\TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer) {
		$this->resourcePointer = $resourcePointer;
	}

	/**
	 * Returns the resource pointer
	 *
	 * @return \TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getResourcePointer() {
		return $this->resourcePointer;
	}

	/**
	 * Sets the publishing configuration for this resource
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface $publishingConfiguration The publishing configuration
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setPublishingConfiguration(\TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface $publishingConfiguration = NULL) {
		$this->publishingConfiguration = $publishingConfiguration;
	}

	/**
	 * Returns the publishing configuration for this resource
	 *
	 * @return \TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface The publishing configuration
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPublishingConfiguration() {
		return $this->publishingConfiguration;
	}

}
?>
