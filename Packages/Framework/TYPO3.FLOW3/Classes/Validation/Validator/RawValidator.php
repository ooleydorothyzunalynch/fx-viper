<?php
namespace TYPO3\FLOW3\Validation\Validator;

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
 * A validator which accepts any input
 *
 * @api
 * @scope singleton
 */
class RawValidator implements \TYPO3\FLOW3\Validation\Validator\ValidatorInterface {

	/**
	 * Always returns an empty result.
	 *
	 * @param mixed $value The value that should be validated
	 * @return \TYPO3\FLOW3\Error\Result
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function validate($value) {
		return new \TYPO3\FLOW3\Error\Result();
	}
}
?>