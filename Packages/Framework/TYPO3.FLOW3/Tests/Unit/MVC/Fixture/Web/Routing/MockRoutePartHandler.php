<?php
namespace TYPO3\FLOW3\MVC\Fixture\Web\Routing;

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
 * A mock RoutePartHandler
 *
 */
class MockRoutePartHandler extends \TYPO3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

	protected function matchValue($value) {
		$this->value = '_match_invoked_';
		return TRUE;
	}

	protected function resolveValue($value) {
		$this->value = '_resolve_invoked_';
		return TRUE;
	}
}
?>