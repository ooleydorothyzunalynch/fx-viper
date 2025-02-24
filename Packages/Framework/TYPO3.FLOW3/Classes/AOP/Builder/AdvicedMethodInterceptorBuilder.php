<?php
namespace TYPO3\FLOW3\AOP\Builder;

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
 * An AOP interceptor code builder for methods enriched by advices.
 *
 * @scope singleton
 */
class AdvicedMethodInterceptorBuilder extends \TYPO3\FLOW3\AOP\Builder\AbstractMethodInterceptorBuilder {

	/**
	 * Builds interception PHP code for an adviced method
	 *
	 * @param string $methodName Name of the method to build an interceptor for
	 * @param array $interceptedMethods An array of method names and their meta information, including advices for the method (if any)
	 * @param string $targetClassName Name of the target class to build the interceptor for
	 * @param array
	 * @return string PHP code of the interceptor
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function build($methodName, array $interceptedMethods, $targetClassName) {
		if ($methodName === '__construct') throw new \TYPO3\FLOW3\AOP\Exception('The ' . __CLASS__ . ' cannot build constructor interceptor code.', 1173107446);

		$proxyMethod = $this->compiler->getProxyClass($targetClassName)->getMethod($methodName);

		$groupedAdvices = $interceptedMethods[$methodName]['groupedAdvices'];
		$advicesCode = $this->buildAdvicesCode($groupedAdvices, $methodName, $targetClassName);

		if ($methodName !== NULL || $methodName === '__wakeup') {
			$proxyMethod->addPreParentCallCode('
		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode[\'' . $methodName . '\'])) {
');
			$proxyMethod->addPostParentCallCode('
		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode[\'' . $methodName . '\'] = TRUE;
			try {
			' . $advicesCode . '
			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode[\'' . $methodName . '\']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode[\'' . $methodName . '\']);
		}
');
		}
	}
}

?>