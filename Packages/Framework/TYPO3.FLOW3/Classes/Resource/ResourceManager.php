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
 * The Resource Manager
 *
 * @scope singleton
 * @api
 */
class ResourceManager {

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var \TYPO3\FLOW3\Cache\Frontend\StringFrontend
	 */
	protected $statusCache;

	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var string
	 */
	protected $persistentResourcesStorageBaseUri;

	/**
	 * @var \SplObjectStorage
	 */
	protected $importedResources;

	/**
	 * Injects the object manager
	 *
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects the resource publisher
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectResourcePublisher(\TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

	/**
	 * Injects the reflection service
	 *
	 * @param \TYPO3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectReflectionService(\TYPO3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Injects the environment
	 *
	 * @param \TYPO3\FLOW3\Utility\Environment $environment
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectEnvironment(\TYPO3\FLOW3\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * Injects the status cache
	 *
	 * @param \TYPO3\FLOW3\Cache\Frontend\StringFrontend $statusCache
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectStatusCache(\TYPO3\FLOW3\Cache\Frontend\StringFrontend $statusCache) {
		$this->statusCache = $statusCache;
	}

	/**
	 * Injects the persistence manager
	 *
	 * @param \TYPO3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Injects the settings of this package
	 *
	 * @param array $settings
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Check for implementations of TYPO3\FLOW3\Resource\Streams\StreamWrapperInterface and
	 * register them.
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initialize() {
		\TYPO3\FLOW3\Resource\Streams\StreamWrapperAdapter::injectObjectManager($this->objectManager);
		$streamWrapperClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface('TYPO3\FLOW3\Resource\Streams\StreamWrapperInterface');
		foreach ($streamWrapperClassNames as $streamWrapperClassName) {
			$scheme = $streamWrapperClassName::getScheme();
			if (in_array($scheme, stream_get_wrappers())) {
				stream_wrapper_unregister($scheme);
			}
			stream_wrapper_register($scheme, '\TYPO3\FLOW3\Resource\Streams\StreamWrapperAdapter');
			\TYPO3\FLOW3\Resource\Streams\StreamWrapperAdapter::registerStreamWrapper($scheme, $streamWrapperClassName);
		}

			// For now this URI is hardcoded, but might be manageable in the future
			// if additional persistent resources storages are supported.
		$this->persistentResourcesStorageBaseUri = FLOW3_PATH_DATA . 'Persistent/Resources/';
		\TYPO3\FLOW3\Utility\Files::createDirectoryRecursively($this->persistentResourcesStorageBaseUri);

		$this->importedResources = new \SplObjectStorage();
  	}

	/**
	 * Imports a resource (file) from the given location as a persistent resource.
	 * On a successful import this method returns a Resource object representing the
	 * newly imported persistent resource.
	 *
	 * @param string $uri An URI (can also be a path and filename) pointing to the resource to import
	 * @return mixed A resource object representing the imported resource or FALSE if an error occurred.
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function importResource($uri) {
		$pathInfo = pathinfo($uri);
		if (isset($pathInfo['extension']) && substr(strtolower($pathInfo['extension']), -3, 3) === 'php' ) {
			return FALSE;
		}

		$temporaryTargetPathAndFilename = $this->environment->getPathToTemporaryDirectory() . uniqid('FLOW3_ResourceImport_');
		if (copy($uri, $temporaryTargetPathAndFilename) === FALSE) {
			return FALSE;
		}

		$hash = sha1_file($temporaryTargetPathAndFilename);
		$finalTargetPathAndFilename = $this->persistentResourcesStorageBaseUri . $hash;
		if (rename($temporaryTargetPathAndFilename, $finalTargetPathAndFilename) === FALSE) {
			unlink($temporaryTargetPathAndFilename);
			return FALSE;
		}
		$this->fixFilePermissions($finalTargetPathAndFilename);
		$resource = new \TYPO3\FLOW3\Resource\Resource();
		$resource->setFilename($pathInfo['basename']);

		$resourcePointer = $this->getResourcePointerForHash($hash);
		$resource->setResourcePointer($resourcePointer);
		$this->importedResources[$resource] = array(
			'originalFilename' => $pathInfo['basename']
		);
		return $resource;
	}

	/**
	 * Returns an object storage with all resource objects which have been imported
	 * by the Resource Manager during this script call. Each resource comes with
	 * an array of additional information about its import.
	 *
	 * Example for a returned object storage:
	 *
	 * $resource1 => array('originalFilename' => 'Foo.txt'),
	 * $resource2 => array('originalFilename' => 'Bar.txt'),
	 * ...
	 *
	 * @return \SplObjectStorage
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getImportedResources() {
		return clone $this->importedResources;
	}

	/**
	 * Imports a resource (file) from the given upload info array as a persistent
	 * resource.
	 * On a successful import this method returns a Resource object representing
	 * the newly imported persistent resource.
	 *
	 * @param array $uploadInfo An array detailing the resource to import (expected keys: name, tmp_name)
	 * @return mixed A resource object representing the imported resource or FALSE if an error occurred.
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function importUploadedResource(array $uploadInfo) {
		$pathInfo = pathinfo($uploadInfo['name']);
		if (!isset($pathInfo['extension']) || substr(strtolower($pathInfo['extension']), -3, 3) === 'php' ) {
			return FALSE;
		}

		if (!file_exists($uploadInfo['tmp_name'])) {
			return FALSE;
		}
		$hash = sha1_file($uploadInfo['tmp_name']);
		$finalTargetPathAndFilename = $this->persistentResourcesStorageBaseUri . $hash;
		if (move_uploaded_file($uploadInfo['tmp_name'], $finalTargetPathAndFilename) === FALSE) {
			return FALSE;
		}
		$this->fixFilePermissions($finalTargetPathAndFilename);
		$resource = new \TYPO3\FLOW3\Resource\Resource();
		$resource->setFilename($pathInfo['basename']);

		$resourcePointer = $this->getResourcePointerForHash($hash);
		$resource->setResourcePointer($resourcePointer);
		$this->importedResources[$resource] = array(
			'originalFilename' => $pathInfo['basename']
		);
		return $resource;
	}

	/**
	 * Fixes the permissions as needed for FLOW3 to run fine in web and cli context.
	 *
	 * @param string $pathAndFilename
	 * @return void
	 */
	protected function fixFilePermissions($pathAndFilename) {
		chmod($pathAndFilename, 0666 ^ umask());
	}

	/**
	 * Helper function which creates or fetches a resource pointer object for a given hash.
	 *
	 * If a ResourcePointer with the given hash exists, this one is used. Else, a new one
	 * is created. This is a workaround for missing ValueObject support in Doctrine.
	 *
	 * @param string $hash
	 * @return \TYPO3\FLOW3\Resource\ResourcePointer
	 */
	public function getResourcePointerForHash($hash) {
		$resourcePointer = $this->persistenceManager->getObjectByIdentifier($hash, 'TYPO3\FLOW3\Resource\ResourcePointer');
		if (!$resourcePointer) {
			$resourcePointer = new \TYPO3\FLOW3\Resource\ResourcePointer($hash);
			$this->persistenceManager->add($resourcePointer);
		}

		return $resourcePointer;
	}

	/**
	 * Deletes the file represented by the given resource instance.
	 *
	 * @param \TYPO3\FLOW3\Resource\Resource $resource
	 * @return boolean
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function deleteResource($resource) {
			// instanceof instead of type hinting so it can be used as slot
		if ($resource instanceof \TYPO3\FLOW3\Resource\Resource) {
			$this->resourcePublisher->unpublishPersistentResource($resource);
			if (is_file($this->persistentResourcesStorageBaseUri . $resource->getResourcePointer()->getHash())) {
				unlink($this->persistentResourcesStorageBaseUri . $resource->getResourcePointer()->getHash());
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Method which returns the base URI of the location where persistent resources
	 * are stored.
	 *
	 * @return string The URI
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPersistentResourcesStorageBaseUri() {
		return $this->persistentResourcesStorageBaseUri;
	}

	/**
	 * Prepares a mirror of public package resources that is accessible through
	 * the web server directly.
	 *
	 * @param array $activePackages
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function publishPublicPackageResources(array $activePackages) {
		if ($this->settings['resource']['publishing']['detectPackageResourceChanges'] === FALSE && $this->statusCache->has('packageResourcesPublished')) {
			return;
		}
		foreach ($activePackages as $packageKey => $package) {
			$this->resourcePublisher->publishStaticResources($package->getResourcesPath() . 'Public/', 'Packages/' . $packageKey . '/');
		}
		if (!$this->statusCache->has('packageResourcesPublished')) {
			$this->statusCache->set('packageResourcesPublished', 'y', array(\TYPO3\FLOW3\Cache\Frontend\FrontendInterface::TAG_PACKAGE));
		}
	}
}

?>