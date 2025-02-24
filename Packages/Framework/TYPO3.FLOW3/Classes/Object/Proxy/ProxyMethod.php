<?php
namespace TYPO3\FLOW3\Object\Proxy;

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
 * Representation of a method within a proxy class
 *
 * @proxy disable
 */
class ProxyMethod {

	const BEFORE_PARENT_CALL = 1;
	const AFTER_PARENT_CALL = 2;

	/**
	 * Fully qualified class name of the original class
	 *
	 * @var string
	 */
	protected $fullOriginalClassName;

	/**
	 * Name of the original method
	 *
	 * @var string
	 */
	protected $methodName;

	/**
	 * Visibility of the method
	 *
	 * @var string
	 */
	protected $visibility;

	/**
	 * @var string
	 */
	protected $addedPreParentCallCode = '';

	/**
	 * @var string
	 */
	protected $addedPostParentCallCode = '';

	/**
	 * @var string
	 */
	protected $methodParametersCode = '';

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * Constructor
	 *
	 * @param string $fullOriginalClassName The fully qualified class name of the original class
	 * @param string $methodName Name of the proxy (and original) method
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($fullOriginalClassName, $methodName) {
		$this->fullOriginalClassName = $fullOriginalClassName;
		$this->methodName = $methodName;
	}

	/**
	 * Injects the Reflection Service
	 *
	 * @param \TYPO3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectReflectionService(\TYPO3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Overrides the method's visibility
	 *
	 * @param string $visibility One of 'public', 'protected', 'private'
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function overrideMethodVisibility($visibility) {
		$this->visibility = $visibility;
	}

	/**
	 * Adds PHP code to the body of this method which will be executed before a possible parent call.
	 *
	 * @param string $code
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addPreParentCallCode($code) {
		$this->addedPreParentCallCode .= $code;
	}

	/**
	 * Adds PHP code to the body of this method which will be executed after a possible parent call.
	 *
	 * @param string $code
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addPostParentCallCode($code) {
		$this->addedPostParentCallCode .= $code;
	}

	/**
	 * Sets the (exact) code which use used in as the parameters signature for this method.
	 *
	 * @param string $code Parameters code, for example: '$foo, array $bar, \Foo\Bar\Baz $baz'
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setMethodParametersCode($code) {
		$this->methodParametersCode = $code;
	}

	/**
	 * Renders the PHP code for this Proxy Method
	 *
	 * @return string PHP code
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function render() {
		$methodDocumentation = $this->buildMethodDocumentation($this->fullOriginalClassName, $this->methodName);
		$methodParametersCode = ($this->methodParametersCode !== '' ? $this->methodParametersCode : $this->buildMethodParametersCode($this->fullOriginalClassName, $this->methodName));
		$callParentMethodCode = $this->buildCallParentMethodCode($this->fullOriginalClassName, $this->methodName);

		$staticKeyword = $this->reflectionService->isMethodStatic($this->fullOriginalClassName, $this->methodName) ? 'static ' : '';

		$visibility = ($this->visibility === NULL ? $this->getMethodVisibilityString() : $this->visibility);

		$code = '';
		if (strlen($this->addedPreParentCallCode) + strlen($this->addedPostParentCallCode) > 0) {
			$code = "\n" .
				$methodDocumentation .
				"	" . $staticKeyword . " " . $visibility . " function " . $this->methodName . "(" . $methodParametersCode . ") {\n" .
				$this->addedPreParentCallCode;

			if ($this->addedPostParentCallCode !== '') {
				$code .= "		\$result = " . ($callParentMethodCode === '' ? "NULL;\n" : $callParentMethodCode);
				$code .= $this->addedPostParentCallCode;
				$code .= "		return \$result;\n";
			} else {
				$code .= ($callParentMethodCode === '' ? '' : "		return " . $callParentMethodCode . ";\n");
			}
			$code .= "	}\n";

		}
		return $code;
	}

	/**
	 * Tells if enough code was provided (yet) so that this method would actually be rendered
	 * if render() is called.
	 *
	 * @return boolean TRUE if there is any code to render, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function willBeRendered() {
		return (strlen($this->addedPreParentCallCode) + strlen($this->addedPostParentCallCode) > 0);
	}

	/**
	 * Builds the method documentation block for the specified method keeping the vital annotations
	 *
	 * @param string $className Name of the class the method is declared in
	 * @param string $methodName Name of the method to create the parameters code for
	 * @return string $methodDocumentation DocComment for the given method
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildMethodDocumentation($className, $methodName) {
		$methodDocumentation = "	/**\n	 * Autogenerated Proxy Method\n";

		if ($this->reflectionService->hasMethod($className, $methodName)) {
			$methodTags = $this->reflectionService->getMethodTagsValues($className, $methodName);
			$ignoredTags = $this->reflectionService->getIgnoredTags();
			foreach ($methodTags as $tag => $values) {
				if (!in_array($tag, $ignoredTags)) {
					if (count($values) === 0) {
						$methodDocumentation .= '	 * @' . $tag . "\n";
					} else {
						foreach ($values as $value) {
							$methodDocumentation  .= '	 * @' . $tag . ' ' . $value . "\n";
						}
					}
				}
			}
		}

		$methodDocumentation .= "	 */\n";
		return $methodDocumentation;
	}

	/**
	 * Builds the PHP code for the parameters of the specified method to be
	 * used in a method interceptor in the proxy class
	 *
	 * @param string $fullClassName Name of the class the method is declared in
	 * @param string $methodName Name of the method to create the parameters code for
	 * @param boolean $addTypeAndDefaultValue If the type and default value for each parameters should be rendered
	 * @return string A comma speparated list of parameters
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function buildMethodParametersCode($fullClassName, $methodName, $addTypeAndDefaultValue = TRUE) {
		$methodParametersCode = '';
		$methodParameterTypeName = '';
		$defaultValue = '';
		$byReferenceSign = '';

		if ($fullClassName === NULL || $methodName === NULL) return '';

		$methodParameters = $this->reflectionService->getMethodParameters($fullClassName, $methodName);
		if (count($methodParameters) > 0) {
			$methodParametersCount = 0;
			foreach ($methodParameters as $methodParameterName => $methodParameterInfo) {
				if ($addTypeAndDefaultValue) {
					if ($methodParameterInfo['array'] === TRUE) {
						$methodParameterTypeName = 'array';
					} else {
						$methodParameterTypeName = ($methodParameterInfo['class'] === NULL) ? '' : '\\' . $methodParameterInfo['class'];
					}
					if ($methodParameterInfo['optional'] === TRUE) {
						$rawDefaultValue = (isset($methodParameterInfo['defaultValue']) ? $methodParameterInfo['defaultValue'] : NULL);
						if ($rawDefaultValue === NULL) {
							$defaultValue = ' = NULL';
						} elseif (is_bool($rawDefaultValue)) {
							$defaultValue = ($rawDefaultValue ? ' = TRUE' : ' = FALSE');
						} elseif (is_numeric($rawDefaultValue)) {
							$defaultValue = ' = ' . $rawDefaultValue;
						} elseif (is_string($rawDefaultValue)) {
							$defaultValue = " = '" . $rawDefaultValue . "'";
						} elseif (is_array($rawDefaultValue)) {
							$defaultValue = " = " . $this->buildArraySetupCode($rawDefaultValue);
						}
					}
					$byReferenceSign = ($methodParameterInfo['byReference'] ? '&' : '');
				}

				$methodParametersCode .= ($methodParametersCount > 0 ? ', ' : '') . ($methodParameterTypeName ? $methodParameterTypeName . ' ' : '') . $byReferenceSign . '$' . $methodParameterName . $defaultValue;
				$methodParametersCount ++;
			}
		}

		return $methodParametersCode;
	}

	/**
	 * Builds PHP code which calls the original (ie. parent) method after the added code has been executed.
	 *
	 * @param string $fullClassName Fully qualified name of the original class
	 * @param string $methodName Name of the original method
	 * @return string PHP code
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildCallParentMethodCode($fullClassName, $methodName) {
		if (!$this->reflectionService->hasMethod($fullClassName, $methodName)) {
			return '';
		}
		return "parent::" . $methodName . "(" . $this->buildMethodParametersCode($fullClassName, $methodName, FALSE) . ");\n";
	}

	/**
	 * Builds a string containing PHP code to build the array given as input.
	 *
	 * @param array $array
	 * @return string e.g. 'array()' or 'array(1 => 'bar')
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildArraySetupCode(array $array) {
		$code = 'array(';
		foreach ($array as $key => $value) {
			$code .= (is_string($key)) ? "'" . $key  . "'" : $key;
			$code .= ' => ';
			if ($value === NULL) {
				$code .= 'NULL';
			} elseif (is_bool($value)) {
				$code .= ($value ? 'TRUE' : 'FALSE');
			} elseif (is_numeric($value)) {
				$code .= $value;
			} elseif (is_string($value)) {
				$code .= "'" . $value . "'";
			}
			$code .= ', ';
		}
		return rtrim($code, ', ') . ')';
	}

	/**
	 * Returns the method's visibility string found by the reflection service
	 * Note: If the reflection service has no information about this method,
	 * 'public' is returned.
	 *
	 * @return string One of 'public', 'protected' or 'private'
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function getMethodVisibilityString() {
		if ($this->reflectionService->isMethodProtected($this->fullOriginalClassName, $this->methodName)) {
			return 'protected';
		} elseif ($this->reflectionService->isMethodPrivate($this->fullOriginalClassName, $this->methodName)) {
			return 'private';
		}
		return 'public';
	}
}
?>