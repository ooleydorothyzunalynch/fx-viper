<?php
namespace TYPO3\FLOW3\MVC\Web\Routing;

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
 * An ObjectPathMapping model
 * This contains the URI representation of an object (pathSegment)
 *
 * @entity
 */
class ObjectPathMapping {

	/**
	 * Class name of the object this mapping belongs to
	 *
	 * @var string
	 * @Id
	 * @validate NotEmpty
	 */
	protected $objectType;

	/**
	 * Pattern of the path segment (for example "{date}/{title}")
	 *
	 * @var string
	 * @Id
	 * @validate NotEmpty
	 */
	protected $uriPattern;

	/**
	 * Path segment (URI representation) of the object this mapping belongs to
	 *
	 * @var string
	 * @Id
	 * @validate NotEmpty
	 */
	protected $pathSegment;

	/**
	 * Identifier of the object this mapping belongs to
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * @param string $pathSegment
	 */
	public function setPathSegment($pathSegment) {
		$this->pathSegment = $pathSegment;
	}

	/**
	 * @return string
	 */
	public function getPathSegment() {
		return $this->pathSegment;
	}

	/**
	 * @param string $uriPattern
	 */
	public function setUriPattern($uriPattern) {
		$this->uriPattern = $uriPattern;
	}

	/**
	 * @return string
	 */
	public function getUriPattern() {
		return $this->uriPattern;
	}

	/**
	 * @param string $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $objectType
	 */
	public function setObjectType($objectType) {
		$this->objectType = $objectType;
	}

	/**
	 * @return string
	 */
	public function getObjectType() {
		return $this->objectType;
	}
}
?>
