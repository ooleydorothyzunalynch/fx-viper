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

use \TYPO3\FLOW3\Cache\CacheManager;

/**
 * Representation of a Proxy Class during rendering time
 *
 * @proxy disable
 */
class ProxyClass {

	/**
	 * Namespace, extracted from the fully qualified original class name
	 *
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * The original class name
	 *
	 * @var string
	 */
	protected $originalClassName;

	/**
	 * Fully qualified class name of the original class
	 *
	 * @var string
	 */
	protected $fullOriginalClassName;

	/**
	 * @var \TYPO3\FLOW3\Object\Proxy\ProxyConstructor
	 */
	protected $constructor;

	/**
	 * @var array
	 */
	protected $methods = array();

	/**
	 * @var array
	 */
	protected $constants = array();

	/**
	 * @var array
	 */
	protected $interfaces = array('\TYPO3\FLOW3\Object\Proxy\ProxyInterface');

	/**
	 * @var array
	 */
	protected $properties = array();

	/**
	 * A list of tags to attach to the cache entry this proxy class will be stored in
	 *
	 * @var array
	 */
	protected $cacheTags = array();

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * Creates a new ProxyClass instance.
	 *
	 * @param string $fullOriginalClassName The fully qualified class name of the original class
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($fullOriginalClassName) {
		if (strpos($fullOriginalClassName, '\\') === FALSE) {
			$this->originalClassName = $fullOriginalClassName;
		} else {
			$this->namespace = substr($fullOriginalClassName, 0, strrpos($fullOriginalClassName, '\\'));
			$this->originalClassName = substr($fullOriginalClassName, strlen($this->namespace) + 1);
		}
		$this->fullOriginalClassName = $fullOriginalClassName;
		$this->addClassDependency($fullOriginalClassName);
		foreach (class_parents($fullOriginalClassName) as $parentClassName) {
			$this->addClassDependency($parentClassName);
		}
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
	 * Returns the ProxyConstructor for this ProxyClass. Creates it if needed.
	 *
	 * @return \TYPO3\FLOW3\Object\Proxy\ProxyConstructor
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getConstructor() {
		if (!isset($this->constructor)) {
			$this->constructor = new \TYPO3\FLOW3\Object\Proxy\ProxyConstructor($this->fullOriginalClassName);
			$this->constructor->injectReflectionService($this->reflectionService);
		}
		return $this->constructor;
	}

	/**
	 * Returns the named ProxyMethod for this ProxyClass. Creates it if needed.
	 *
	 * @param string $methodName The name of the methods to return
	 * @return \TYPO3\FLOW3\Object\Proxy\ProxyMethod
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getMethod($methodName) {
		if ($methodName === '__construct') {
			return $this->getConstructor();
		}
		if (!isset($this->methods[$methodName])) {
			$this->methods[$methodName] = new \TYPO3\FLOW3\Object\Proxy\ProxyMethod($this->fullOriginalClassName, $methodName);
			$this->methods[$methodName]->injectReflectionService($this->reflectionService);
		}
		return $this->methods[$methodName];
	}

	/**
	 * Adds a constant to this proxy class
	 *
	 * @param string $name Name of the constant. Should be ALL_UPPERCASE_WITH_UNDERSCORES
	 * @param string $value PHP code which assigns the value. Example: 'foo' (including quotes!)
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addConstant($name, $valueCode) {
		$this->constants[$name] = $valueCode;
	}

	/**
	 * Adds a class property to this proxy class
	 *
	 * @param string $name Name of the property
	 * @param string $initialValueCode PHP code of the initial value assignment
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addProperty($name, $initialValueCode, $visibility = 'private', $docComment = '') {
		$this->properties[$name] = array(
			'initialValueCode' => $initialValueCode,
			'visibility' => $visibility,
			'docComment' => $docComment
		);
	}

	/**
	 * Adds one or more interfaces to the "implements" section of the class definition.
	 *
	 * Note that the passed interface names must already have a leading backslash,
	 * for example "\TYPO3\FLOW3\Foo\BarInterface".
	 *
	 * @param array $interfaceNames Fully qualified names of the interfaces to introduce
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addInterfaces(array $interfaceNames) {
		$this->interfaces = array_merge($this->interfaces, $interfaceNames);
		$this->addClassDependencies($interfaceNames);
	}

	/**
	 * Adds a class or interface name as a dependency.
	 *
	 * @param string $className Class name this proxy class depends on
	 * @return void
	 */
	public function addClassDependency($className) {
		$this->cacheTags[] = CacheManager::getClassTag($className);
	}

	/**
	 * Adds multiple classes or interfaces dependencies.
	 *
	 * @param string $className Class name this proxy class depends on
	 * @return void
	 */
	public function addClassDependencies(array $classNames) {
		foreach ($classNames as $className) {
			$this->cacheTags[] = CacheManager::getClassTag($className);
		}
	}

	/**
	 * Returns a list of cache tags for the cache entry of this proxy class
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getCacheTags() {
		return array_unique($this->cacheTags);
	}

	/**
	 * Renders and returns the PHP code for this ProxyClass.
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function render() {
		$namespace = $this->namespace;
		$proxyClassName = $this->originalClassName;
		$originalClassName = $this->originalClassName . \TYPO3\FLOW3\Object\Proxy\Compiler::ORIGINAL_CLASSNAME_SUFFIX;
		$abstractKeyword = $this->reflectionService->isClassAbstract($this->fullOriginalClassName) ? 'abstract ' : '';

		$constantsCode = $this->renderConstantsCode();
		$propertiesCode = $this->renderPropertiesCode();

		$methodsCode = isset($this->constructor) ? $this->constructor->render() : '';
		foreach ($this->methods as $proxyMethod) {
			$methodsCode .= $proxyMethod->render();
		}

		if ($methodsCode . $constantsCode === '') {
			return '';
		}

		$classAnnotations = '';
		foreach ($this->reflectionService->getClassTagsValues($this->fullOriginalClassName) as $tag => $values) {
			$classAnnotations .= ' * @' . $tag . ' ' . implode(' ', $values) . "\n";
		}

		$classCode =
			"namespace $namespace;\n" .
			"\n" .
			"/**\n" .
			" * Autogenerated Proxy Class\n" .
			$classAnnotations .
			" */\n" .
			$abstractKeyword . "class $proxyClassName extends $originalClassName implements " . implode(', ', array_unique($this->interfaces)) ." {\n\n" .
			$constantsCode .
			$propertiesCode .
			$methodsCode .
			"}";
		return $classCode;
	}

	/**
	 * Renders code for the added class constants
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function renderConstantsCode() {
		$code = '';
		foreach ($this->constants as $name => $valueCode) {
			$code .= '	const ' . $name . ' = ' . $valueCode . ";\n\n";
		}
		return $code;
	}

	/**
	 * Renders code for the added class properties
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function renderPropertiesCode() {
		$code = '';
		foreach ($this->properties as $name => $attributes) {
			if (!empty($attributes['docComment'])) {
				$code .= '	' . $attributes['docComment'] . "\n";
			}
			$code .= '	' . $attributes['visibility'] . ' $' . $name . ' = ' . $attributes['initialValueCode'] . ";\n\n";
		}
		return $code;
	}
}
?>