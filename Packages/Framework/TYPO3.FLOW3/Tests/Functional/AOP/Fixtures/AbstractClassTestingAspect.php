<?php
namespace TYPO3\FLOW3\Tests\Functional\AOP\Fixtures;

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
 * An aspect for testing functionality related to abstract classes
 *
 * @aspect
 */
class AbstractClassTestingAspect {

	/**
	 * @around method(public TYPO3\FLOW3\Tests\Functional\AOP\Fixtures\SubClassOfAbstractClass->abstractMethod())
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint
	 * @return void
	 */
	public function abstractMethodInSubClassAdvice(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);
		return $result . ' adviced';
	}

	/**
	 * @around method(public TYPO3\FLOW3\Tests\Functional\AOP\Fixtures\AbstractClass->concreteMethod())
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint
	 * @return void
	 */
	public function concreteMethodInAbstractClassAdvice(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);
		return $result . ' adviced';
	}
}
?>