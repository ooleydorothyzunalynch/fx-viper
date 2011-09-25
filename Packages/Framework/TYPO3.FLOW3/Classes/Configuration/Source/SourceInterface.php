<?php
namespace TYPO3\FLOW3\Configuration\Source;

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
 * Contract for a configuration source
 *
 * @author Robert Lemke <robert@typo3.org>
 */
interface SourceInterface {

	/**
	 * Loads the specified configuration file and returns its content in a
	 * configuration container
	 *
	 * @param string $pathAndFilename Full path and file name of the file to load, excluding the dot and file extension
	 * @return \TYPO3\FLOW3\Configuration\Container
	 * @throws \TYPO3\FLOW3\Configuration\Exception\NoSuchFileException if the specified file does not exist
	 */
	public function load($pathAndFilename);

	/**
	 * Save the specified configuration container to the given file
	 *
	 * @param string $pathAndFilename Full path and file name of the file to write to, excluding the dot and file extension
	 * @param array $configuration The configuration array to save
	 * @return void
	 */
	public function save($pathAndFilename, array $configuration);

}
?>