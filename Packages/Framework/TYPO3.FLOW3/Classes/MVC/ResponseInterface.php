<?php
namespace TYPO3\FLOW3\MVC;

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
 * A generic and very basic response implementation
 *
 * @author Robert Lemke <robert@typo3.org>
 * @api
 * @scope prototype
 */
interface ResponseInterface {

	/**
	 * Overrides and sets the content of the response
	 *
	 * @param string $content The response content
	 * @return void
	 * @api
	 */
	public function setContent($content);

	/**
	 * Appends content to the already existing content.
	 *
	 * @param string $content More response content
	 * @return void
	 * @api
	 */
	public function appendContent($content);

	/**
	 * Returns the response content without sending it.
	 *
	 * @return string The response content
	 * @api
	 */
	public function getContent();

	/**
	 * Sends the response
	 *
	 * @return void
	 * @api
	 */
	public function send();
}
?>