<?php
namespace TYPO3\FLOW3\Persistence\Generic\Qom;

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
 * Evaluates to the upper-case string value (or values, if multi-valued) of
 * operand.
 *
 * If operand does not evaluate to a string value, its value is first converted
 * to a string.
 *
 * If operand evaluates to null, the UpperCase operand also evaluates to null.
 *
 * @api
 * @scope prototype
 */
class UpperCase {

	/**
	 * @var \TYPO3\FLOW3\Persistence\Generic\Qom\DynamicOperand
	 */
	protected $operand;

	/**
	 * Constructs this UpperCase instance
	 *
	 * @param \TYPO3\FLOW3\Persistence\Generic\Qom\DynamicOperand $operand
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct(\TYPO3\FLOW3\Persistence\Generic\Qom\DynamicOperand $operand) {
		$this->operand = $operand;
	}

	/**
	 * Gets the operand whose value is converted to a upper-case string.
	 *
	 * @return \TYPO3\FLOW3\Persistence\Generic\Qom\DynamicOperand the operand; non-null
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function getOperand() {
		return $this->operand;
	}

}
?>