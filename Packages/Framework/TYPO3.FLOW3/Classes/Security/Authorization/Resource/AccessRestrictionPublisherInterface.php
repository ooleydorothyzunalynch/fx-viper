<?php
namespace TYPO3\FLOW3\Security\Authorization\Resource;

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
 * Interface for access restriction publishers
 *
 */
interface AccessRestrictionPublisherInterface {

	/**
	 * Publishes access restrictions for file path.
	 * This could be a e.g. .htaccess file to deny public access for the directory or its files
	 *
	 * @param string $path The path to publish the restrictions for
	 * @return void
	 */
	public function publishAccessRestrictionsForPath($path);
}

?>
