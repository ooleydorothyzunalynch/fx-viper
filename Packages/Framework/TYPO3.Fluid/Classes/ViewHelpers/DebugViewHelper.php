<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * Viewhelper that outputs its childnodes with \TYPO3\var_dump()
 *
 * @api
 * @scope prototype
 */
class DebugViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Wrapper for \TYPO3\FLOW3\var_dump()
	 *
	 * @param string $title
	 * @return string debug string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($title = NULL) {
		ob_start();
		\TYPO3\FLOW3\var_dump($this->renderChildren(), $title);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}


?>