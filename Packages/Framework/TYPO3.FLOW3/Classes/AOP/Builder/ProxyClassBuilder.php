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

use \TYPO3\FLOW3\Cache\CacheManager;

/**
 * The main class of the AOP (Aspect Oriented Programming) framework.
 *
 * @proxy disable
 * @scope singleton
 */
class ProxyClassBuilder {

	/**
	 * @var \TYPO3\FLOW3\Object\Proxy\Compiler
	 */
	protected $compiler;

	/**
	 * The FLOW3 settings
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\FLOW3\Log\SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * An instance of the pointcut expression parser
	 * @var \TYPO3\FLOW3\AOP\Pointcut\PointcutExpressionParser
	 */
	protected $pointcutExpressionParser;

	/**
	 * @var \TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder
	 */
	protected $proxyClassBuilder;

	/**
	 * @var \TYPO3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $objectConfigurationCache;

	/**
	 * @var \TYPO3\FLOW3\Object\CompileTimeObjectManager
	 */
	protected $objectManager;

	/**
	 * Hardcoded list of FLOW3 sub packages (first 15 characters) which must be immune to AOP proxying for security, technical or conceptual reasons.
	 * @var array
	 */
	protected $blacklistedSubPackages = array('TYPO3\FLOW3\AOP', 'TYPO3\FLOW3\Cac', 'TYPO3\FLOW3\Con', 'TYPO3\FLOW3\Err', 'TYPO3\FLOW3\Eve', 'TYPO3\FLOW3\Loc', 'TYPO3\FLOW3\Log', 'TYPO3\FLOW3\Mon', 'TYPO3\FLOW3\Obj', 'TYPO3\FLOW3\Pac', 'TYPO3\FLOW3\Pro', 'TYPO3\FLOW3\Ref', 'TYPO3\FLOW3\Uti', 'TYPO3\FLOW3\Val');

	/**
	 * A registry of all known aspects
	 * @var array
	 */
	protected $aspectContainers = array();

	/**
	 * @var array
	 */
	protected $methodInterceptorBuilders = array();

	/**
	 * @param \TYPO3\FLOW3\Object\Proxy\Compiler $compiler
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectCompiler(\TYPO3\FLOW3\Object\Proxy\Compiler $compiler) {
		$this->compiler = $compiler;
	}

	/**
	 * Injects the reflection service
	 *
	 * @param \TYPO3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectReflectionService(\TYPO3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSystemLogger(\TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger) {
		$this->systemLogger = $systemLogger;
	}

	/**
	 * Injects an instance of the pointcut expression parser
	 *
	 * @param \TYPO3\FLOW3\AOP\Pointcut\PointcutExpressionParser $pointcutExpressionParser
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPointcutExpressionParser(\TYPO3\FLOW3\AOP\Pointcut\PointcutExpressionParser $pointcutExpressionParser) {
		$this->pointcutExpressionParser = $pointcutExpressionParser;
	}

	/**
	 * Injects the cache for storing information about objects
	 *
	 * @param \TYPO3\FLOW3\Cache\Frontend\VariableFrontend $objectConfigurationCache
	 * @return void
	 * @autowiring off
	 */
	public function injectObjectConfigurationCache(\TYPO3\FLOW3\Cache\Frontend\VariableFrontend $objectConfigurationCache) {
		$this->objectConfigurationCache = $objectConfigurationCache;
	}

	/**
	 * Injects the Adviced Constructor Interceptor Builder
	 *
	 * @param \TYPO3\FLOW3\AOP\Builder\AdvicedConstructorInterceptorBuilder $builder
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectAdvicedConstructorInterceptorBuilder(\TYPO3\FLOW3\AOP\Builder\AdvicedConstructorInterceptorBuilder $builder) {
		$this->methodInterceptorBuilders['AdvicedConstructor'] = $builder;
	}

	/**
	 * Injects the Adviced Method Interceptor Builder
	 *
	 * @param \TYPO3\FLOW3\AOP\Builder\AdvicedMethodInterceptorBuilder $builder
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectAdvicedMethodInterceptorBuilder(\TYPO3\FLOW3\AOP\Builder\AdvicedMethodInterceptorBuilder $builder) {
		$this->methodInterceptorBuilders['AdvicedMethod'] = $builder;
	}

	/**
	 * @param \TYPO3\FLOW3\Object\CompileTimeObjectManager $objectManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\CompileTimeObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects the FLOW3 settings
	 *
	 * @param array $settings The settings
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Builds proxy class code which weaves advices into the respective target classes.
	 *
	 * The object configurations provided by the Compiler is searched for possible aspect
	 * annotations. If an aspect class is found, the poincut expressions are parsed and
	 * a new aspect with one or more advisors is added to the aspect registry of the AOP framework.
	 * Finally all advices are woven into their target classes by generating proxy classes.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function build() {
		$allAvailableClassNamesByPackage = $this->objectManager->getRegisteredClassNames();
		$possibleTargetClassNames = $this->getProxyableClasses($allAvailableClassNamesByPackage);
		$actualAspectClassNames = $this->reflectionService->getClassNamesByTag('aspect');
		sort($possibleTargetClassNames);
		sort($actualAspectClassNames);

		$this->aspectContainers = $this->buildAspectContainers($actualAspectClassNames);

		$rebuildEverything = FALSE;
		if ($this->objectConfigurationCache->has('allAspectClassesUpToDate') === FALSE) {
			$rebuildEverything = TRUE;
			$this->systemLogger->log('Aspects have been modified, therefore rebuilding all target classes.', LOG_INFO);
			$tags = array_map(function ($aspectClassName) { return \TYPO3\FLOW3\Cache\CacheManager::getClassTag($aspectClassName); }, $actualAspectClassNames);
			$this->objectConfigurationCache->set('allAspectClassesUpToDate', TRUE, $tags);
		}

		foreach ($possibleTargetClassNames as $targetClassName) {
			$isUnproxied = $this->objectConfigurationCache->has('unproxiedClass-' . str_replace('\\', '_', $targetClassName));
			$hasCacheEntry = $this->compiler->hasCacheEntryForClass($targetClassName) || $isUnproxied;
			if ($rebuildEverything === TRUE || $hasCacheEntry === FALSE) {
				$proxyBuildResult = $this->buildProxyClass($targetClassName, $this->aspectContainers);
				if ($proxyBuildResult !== FALSE) {
					if ($isUnproxied) {
						$this->objectConfigurationCache->remove('unproxiedClass-' . str_replace('\\', '_', $targetClassName));
					}
					$this->systemLogger->log(sprintf('Built AOP proxy for class "%s".', $targetClassName), LOG_INFO);
				} else {
					$this->objectConfigurationCache->set('unproxiedClass-' . str_replace('\\', '_', $targetClassName), TRUE, array(\TYPO3\FLOW3\Cache\CacheManager::getClassTag($targetClassName)));
				}
			}
		}
	}

	/**
	 * Traverses the aspect containers to find a pointcut from the aspect class name
	 * and pointcut method name
	 *
	 * @param string $aspectClassName Name of the aspect class where the pointcut has been declared
	 * @param string $pointcutMethodName Method name of the pointcut
	 * @return mixed The \TYPO3\FLOW3\AOP\Pointcut\Pointcut or FALSE if none was found
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function findPointcut($aspectClassName, $pointcutMethodName) {
		if (!isset($this->aspectContainers[$aspectClassName])) return FALSE;
		foreach ($this->aspectContainers[$aspectClassName]->getPointcuts() as $pointcut) {
			if ($pointcut->getPointcutMethodName() === $pointcutMethodName) {
				return $pointcut;
			}
		}
		return FALSE;
	}

	/**
	 * Returns an array of method names and advices which were applied to the specified class. If the
	 * target class has no adviced methods, an empty array is returned.
	 *
	 * @param string $targetClassName Name of the target class
	 * @return mixed An array of method names and their advices as array of \TYPO3\FLOW3\AOP\Advice\AdviceInterface
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAdvicedMethodsInformationByTargetClass($targetClassName) {
		throw new \TYPO3\FLOW3\AOP\Exception('This method is currently not supported.');
		if (!isset($this->advicedMethodsInformationByTargetClass[$targetClassName])) return array();
		return $this->advicedMethodsInformationByTargetClass[$targetClassName];
	}

	/**
	 * Determines which of the given classes are potentially proxyable
	 * and returns their names in an array.
	 *
	 * @param array $classNames Names of the classes to check
	 * @return array Names of classes which can be proxied
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getProxyableClasses(array $classNamesByPackage) {
		$proxyableClasses = array();
		foreach ($classNamesByPackage as $classNames) {
			foreach ($classNames as $className) {
				if (!in_array(substr($className, 0, 15), $this->blacklistedSubPackages)) {
					if (!$this->reflectionService->isClassTaggedWith($className, 'aspect') &&
						!$this->reflectionService->isClassFinal($className)) {
						$proxyableClasses[] = $className;
					}
				}
			}
		}
		return $proxyableClasses;
	}

	/**
	 * Checks the annotations of the specified classes for aspect tags
	 * and creates an aspect with advisors accordingly.
	 *
	 * @param array &$classNames Classes to check for aspect tags.
	 * @return array An array of \TYPO3\FLOW3\AOP\AspectContainer for all aspects which were found.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildAspectContainers(array &$classNames) {
		$aspectContainers = array();
		foreach ($classNames as $aspectClassName) {
			$aspectContainers[$aspectClassName] = $this->buildAspectContainer($aspectClassName);
		}
		return $aspectContainers;
	}

	/**
	 * Creates and returns an aspect from the annotations found in a class which
	 * is tagged as an aspect. The object acting as an advice will already be
	 * fetched (and therefore instantiated if neccessary).
	 *
	 * @param  string $aspectClassName Name of the class which forms the aspect, contains advices etc.
	 * @return mixed The aspect container containing one or more advisors or FALSE if no container could be built
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildAspectContainer($aspectClassName) {
		$aspectContainer = new \TYPO3\FLOW3\AOP\AspectContainer($aspectClassName);
		$methodNames = get_class_methods($aspectClassName);

		foreach ($methodNames as $methodName) {
			foreach ($this->reflectionService->getMethodTagsValues($aspectClassName, $methodName) as $tagName => $tagValues) {
				foreach ($tagValues as $tagValue) {
					switch ($tagName) {
						case 'around' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$advice = new \TYPO3\FLOW3\AOP\Advice\AroundAdvice($aspectClassName, $methodName);
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName);
							$advisor = new \TYPO3\FLOW3\AOP\Advisor($advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'before' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$advice = new \TYPO3\FLOW3\AOP\Advice\BeforeAdvice($aspectClassName, $methodName);
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName);
							$advisor = new \TYPO3\FLOW3\AOP\Advisor($advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'afterreturning' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$advice = new \TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice($aspectClassName, $methodName);
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName);
							$advisor = new \TYPO3\FLOW3\AOP\Advisor($advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'afterthrowing' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$advice = new \TYPO3\FLOW3\AOP\Advice\AfterThrowingAdvice($aspectClassName, $methodName);
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName);
							$advisor = new \TYPO3\FLOW3\AOP\Advisor($advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'after' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$advice = new \TYPO3\FLOW3\AOP\Advice\AfterAdvice($aspectClassName, $methodName);
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName);
							$advisor = new \TYPO3\FLOW3\AOP\Advisor($advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'pointcut' :
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($tagValue, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($tagValue, $pointcutFilterComposite, $aspectClassName, $methodName);
							$aspectContainer->addPointcut($pointcut);
						break;
					}
				}
			}
		}
		foreach ($this->reflectionService->getClassTagsValues($aspectClassName) as $tagName => $tagValues) {
			foreach ($tagValues as $tagValue) {
				switch ($tagName) {
					case 'introduce' :
						$splittedTagValue = explode(',', $tagValue);
						if (!is_array($splittedTagValue) || count($splittedTagValue) != 2) {
							throw new \TYPO3\FLOW3\AOP\Exception('The interface introduction in class "' . $aspectClassName . '" does not contain the two required parameters (interface name and pointcut expression).', 1172694761);
						}
						$pointcutExpression = trim($splittedTagValue[1]);
						$pointcutFilterComposite = $this->pointcutExpressionParser->parse($pointcutExpression, $this->renderSourceHint($aspectClassName, $methodName, $tagName));
						$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($pointcutExpression, $pointcutFilterComposite, $aspectClassName);
						$interfaceName = trim($splittedTagValue[0]);
						$introduction = new \TYPO3\FLOW3\AOP\InterfaceIntroduction($aspectClassName, $interfaceName, $pointcut);
						$aspectContainer->addInterfaceIntroduction($introduction);
					break;
				}
			}
		}
		foreach ($this->reflectionService->getClassPropertyNames($aspectClassName) as $propertyName) {
			foreach ($this->reflectionService->getPropertyTagsValues($aspectClassName, $propertyName) as $tagName => $tagValues) {
				foreach ($tagValues as $tagValue) {
					switch ($tagName) {
						case 'introduce' :
							if (empty($tagValue)) {
								throw new \TYPO3\FLOW3\AOP\Exception('The introduction in class "' . $aspectClassName . '" does not contain the required pointcut expression.', 1302695408);
							}
							$pointcutExpression = trim($tagValue);
							$pointcutFilterComposite = $this->pointcutExpressionParser->parse($pointcutExpression, $this->renderSourceHint($aspectClassName, $propertyName, $tagName));
							$pointcut = new \TYPO3\FLOW3\AOP\Pointcut\Pointcut($pointcutExpression, $pointcutFilterComposite, $aspectClassName);
							$introduction = new \TYPO3\FLOW3\AOP\PropertyIntroduction($aspectClassName, $propertyName, $pointcut);
							$aspectContainer->addPropertyIntroduction($introduction);
						break;
					}
				}
			}
		}
		if (count($aspectContainer->getAdvisors()) < 1 && count($aspectContainer->getPointcuts()) < 1 && count($aspectContainer->getInterfaceIntroductions()) < 1) throw new \TYPO3\FLOW3\AOP\Exception('The class "' . $aspectClassName . '" is tagged to be an aspect but doesn\'t contain advices nor pointcut or introduction declarations.', 1169124534);
		return $aspectContainer;
	}

	/**
	 * Builds methods for a single AOP proxy class for the specified class.
	 *
	 * @param string $targetClassName Name of the class to create a proxy class file for
	 * @param array &$aspectContainers The array of aspect containers from the AOP Framework
	 * @return boolean TRUE if the proxy class could be built, FALSE otherwise.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildProxyClass($targetClassName, array &$aspectContainers) {
		$interfaceIntroductions = $this->getMatchingInterfaceIntroductions($aspectContainers, $targetClassName);
		$introducedInterfaces = $this->getInterfaceNamesFromIntroductions($interfaceIntroductions);

		$propertyIntroductions = $this->getMatchingPropertyIntroductions($aspectContainers, $targetClassName);

		$methodsFromTargetClass = $this->getMethodsFromTargetClass($targetClassName);
		$methodsFromIntroducedInterfaces = $this->getIntroducedMethodsFromInterfaceIntroductions($interfaceIntroductions, $targetClassName);

		$interceptedMethods = array();
		$this->addAdvicedMethodsToInterceptedMethods($interceptedMethods, array_merge($methodsFromTargetClass, $methodsFromIntroducedInterfaces), $targetClassName, $aspectContainers);
		$this->addIntroducedMethodsToInterceptedMethods($interceptedMethods, $methodsFromIntroducedInterfaces);

		if (count($interceptedMethods) < 1 && count($introducedInterfaces) < 1) return FALSE;

		$proxyClass = $this->compiler->getProxyClass($targetClassName);
		if ($proxyClass === FALSE) {
			return FALSE;
		}

		$proxyClass->addInterfaces($introducedInterfaces);

		foreach ($propertyIntroductions as $propertyIntroduction) {
			$proxyClass->addProperty($propertyIntroduction->getPropertyName(), 'NULL', $propertyIntroduction->getPropertyVisibility(), $propertyIntroduction->getPropertyDocComment());
		}

		$proxyClass->getMethod('FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray')->addPreParentCallCode("\t\tif (is_callable('parent::FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray')) parent::FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();\n");
		$proxyClass->getMethod('FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray')->addPreParentCallCode($this->buildMethodsAndAdvicesArrayCode($interceptedMethods));
		$proxyClass->getMethod('FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray')->overrideMethodVisibility('protected');

		$callBuildMethodsAndAdvicesArrayCode = "\n\t\t\$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();\n";
		$proxyClass->getConstructor()->addPreParentCallCode($callBuildMethodsAndAdvicesArrayCode);
		$proxyClass->getMethod('__wakeup')->addPreParentCallCode($callBuildMethodsAndAdvicesArrayCode);

		if (!$this->reflectionService->hasMethod($targetClassName, '__wakeup')) {
			$proxyClass->getMethod('__wakeup')->addPostParentCallCode("\t\tif (is_callable('parent::__wakeup')) parent::__wakeup();\n");
		}

		$this->buildGetAdviceChainsMethodCode($targetClassName);
		$this->buildInvokeJoinPointMethodCode($targetClassName);
		$this->buildMethodsInterceptorCode($targetClassName, $interceptedMethods);

		$proxyClass->addProperty('FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices', 'array()');
		$proxyClass->addProperty('FLOW3_AOP_Proxy_groupedAdviceChains', 'array()');
		$proxyClass->addProperty('FLOW3_AOP_Proxy_methodIsInAdviceMode', 'array()');

		return TRUE;
	}

	/**
	 * Returns the methods of the target class.
	 *
	 * @param string $targetClassName Name of the target class
	 * @return array Method information with declaring class and method name pairs
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getMethodsFromTargetClass($targetClassName) {
		$methods = array();
		$class = new \ReflectionClass($targetClassName);

		foreach (array('__construct', '__clone') as $builtInMethodName) {
			if (!$class->hasMethod($builtInMethodName)) {
				$methods[] = array($targetClassName, $builtInMethodName);
			}
		}

		foreach ($class->getMethods() as $method) {
			$methods[] = array($targetClassName, $method->getName());
		}

		return $methods;
	}

	/**
	 * Creates code for an array of target methods and their advices.
	 *
	 * Example:
	 *
	 *	$this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array(
	 *		'getSomeProperty' => array(
	 *			'TYPO3\FLOW3\AOP\Advice\AroundAdvice' => array(
	 *				new \TYPO3\FLOW3\AOP\Advice\AroundAdvice('TYPO3\Foo\SomeAspect', 'aroundAdvice', \\TYPO3\\FLOW3\\Core\\Bootstrap::$staticObjectManager, function() { ... }),
	 *			),
	 *		),
	 *	);
	 *
	 *
	 * @param array $methodsAndGroupedAdvices An array of method names and grouped advice objects
	 * @return string PHP code for the content of an array of target method names and advice objects
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @see buildProxyClass()
	 */
	protected function buildMethodsAndAdvicesArrayCode(array $methodsAndGroupedAdvices) {
		if (count($methodsAndGroupedAdvices) < 1) return '';

		$methodsAndAdvicesArrayCode = "\n\t\t\$objectManager = \\TYPO3\\FLOW3\\Core\\Bootstrap::\$staticObjectManager;\n";
		$methodsAndAdvicesArrayCode .= "\t\t\$this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array(\n";
		foreach ($methodsAndGroupedAdvices as $methodName => $advicesAndDeclaringClass) {
			$methodsAndAdvicesArrayCode .= "\t\t\t'" . $methodName . "' => array(\n";
			foreach ($advicesAndDeclaringClass['groupedAdvices'] as $adviceType => $adviceConfigurations) {
				$methodsAndAdvicesArrayCode .= "\t\t\t\t'" . $adviceType . "' => array(\n";
				foreach ($adviceConfigurations as $adviceConfiguration) {
					$advice = $adviceConfiguration['advice'];
					$methodsAndAdvicesArrayCode .= "\t\t\t\t\tnew \\" . get_class($advice) . "('" . $advice->getAspectObjectName() . "', '" . $advice->getAdviceMethodName() . "', \$objectManager, " . $adviceConfiguration['runtimeEvaluationsClosureCode'] . "),\n";
				}
				$methodsAndAdvicesArrayCode .= "\t\t\t\t),\n";
			}
			$methodsAndAdvicesArrayCode .= "\t\t\t),\n";
		}
		$methodsAndAdvicesArrayCode .= "\t\t);\n";
		return  $methodsAndAdvicesArrayCode;
	}

	/**
	 * Traverses all intercepted methods and their advices and builds PHP code to intercept
	 * methods if neccessary.
	 *
	 * The generated code is added directly to the proxy class by calling the respective
	 * methods of the Compiler API.
	 *
	 * @param string $targetClassName The target class the pointcut should match with
	 * @param array $interceptedMethods An array of method names which need to be intercepted
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildMethodsInterceptorCode($targetClassName, array $interceptedMethods) {
		foreach ($interceptedMethods as $methodName => $methodMetaInformation) {
			if (count($methodMetaInformation['groupedAdvices']) === 0) {
				throw new \TYPO3\FLOW3\AOP\Exception\VoidImplementationException(sprintf('Refuse to introduce method %s into target class %s because it has no implementation code. You might want to create an around advice which implements this method.', $methodName, $targetClassName), 1303224472);
			}
			$builderType = 'Adviced' . ($methodName === '__construct' ? 'Constructor' : 'Method');
			$this->methodInterceptorBuilders[$builderType]->build($methodName, $interceptedMethods, $targetClassName);
		}
	}

	/**
	 * Traverses all aspect containers, their aspects and their advisors and adds the
	 * methods and their advices to the (usually empty) array of intercepted methods.
	 *
	 * @param array &$interceptedMethods An array (empty or not) which contains the names of the intercepted methods and additional information
	 * @param array $methods An array of class and method names which are matched against the pointcut (class name = name of the class or interface the method was declared)
	 * @param string $targetClassName Name of the class the pointcut should match with
	 * @param array &$aspectContainers All aspects to take into consideration
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function addAdvicedMethodsToInterceptedMethods(array &$interceptedMethods, array $methods, $targetClassName, array &$aspectContainers) {
		$pointcutQueryIdentifier = 0;

		foreach ($aspectContainers as $aspectContainer) {
			foreach ($aspectContainer->getAdvisors() as $advisor) {
				$pointcut = $advisor->getPointcut();
				foreach ($methods as $method) {
					list($methodDeclaringClassName, $methodName) = $method;

					if ($this->reflectionService->isMethodFinal($targetClassName, $methodName)) continue;

					if ($pointcut->matches($targetClassName, $methodName, $methodDeclaringClassName, $pointcutQueryIdentifier)) {
						$advice = $advisor->getAdvice();
						$interceptedMethods[$methodName]['groupedAdvices'][get_class($advice)][] = array(
							'advice' => $advice,
							'runtimeEvaluationsClosureCode' => $pointcut->getRuntimeEvaluationsClosureCode()
						);
						$interceptedMethods[$methodName]['declaringClassName'] = $methodDeclaringClassName;
					}
					$pointcutQueryIdentifier ++;
				}
			}
		}
	}

	/**
	 * Traverses all methods which were introduced by interfaces and adds them to the
	 * intercepted methods array if they didn't exist already.
	 *
	 * @param array &$interceptedMethods An array (empty or not) which contains the names of the intercepted methods and additional information
	 * @param array $methodsFromIntroducedInterfaces An array of class and method names from introduced interfaces
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function addIntroducedMethodsToInterceptedMethods(array &$interceptedMethods, array $methodsFromIntroducedInterfaces) {
		foreach ($methodsFromIntroducedInterfaces as $interfaceAndMethodName) {
			list($interfaceName, $methodName) = $interfaceAndMethodName;
			if (!isset($interceptedMethods[$methodName])) {
				$interceptedMethods[$methodName]['groupedAdvices'] = array();
				$interceptedMethods[$methodName]['declaringClassName'] = $interfaceName;
			}
		}
	}

	/**
	 * Traverses all aspect containers and returns an array of interface
	 * introductions which match the target class.
	 *
	 * @param array &$aspectContainers All aspects to take into consideration
	 * @param string $targetClassName Name of the class the pointcut should match with
	 * @return array array of interface names
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getMatchingInterfaceIntroductions(array &$aspectContainers, $targetClassName) {
		$introductions = array();
		foreach ($aspectContainers as $aspectContainer) {
			foreach ($aspectContainer->getInterfaceIntroductions() as $introduction) {
				$pointcut = $introduction->getPointcut();
				if ($pointcut->matches($targetClassName, NULL, NULL, uniqid())) {
					$introductions[] = $introduction;
				}
			}
		}
		return $introductions;
	}

	/**
	 * Traverses all aspect containers and returns an array of property
	 * introductions which match the target class.
	 *
	 * @param array &$aspectContainers All aspects to take into consideration
	 * @param string $targetClassName Name of the class the pointcut should match with
	 * @return array array of property introductions
	 */
	protected function getMatchingPropertyIntroductions(array &$aspectContainers, $targetClassName) {
		$introductions = array();
		foreach ($aspectContainers as $aspectContainer) {
			foreach ($aspectContainer->getPropertyIntroductions() as $introduction) {
				$pointcut = $introduction->getPointcut();
				if ($pointcut->matches($targetClassName, NULL, NULL, uniqid())) {
					$introductions[] = $introduction;
				}
			}
		}
		return $introductions;
	}

	/**
	 * Returns an array of interface names introduced by the given introductions
	 *
	 * @param array $interfaceIntroductions An array of interface introductions
	 * @return array Array of interface names
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getInterfaceNamesFromIntroductions(array $interfaceIntroductions) {
		$interfaceNames = array();
		foreach ($interfaceIntroductions as $introduction) {
			$interfaceNames[] = '\\' . $introduction->getInterfaceName();
		}
		return $interfaceNames;
	}

	/**
	 * Returns all methods declared by the introduced interfaces
	 *
	 * @param array $interfaceIntroductions An array of \TYPO3\FLOW3\AOP\InterfaceIntroduction
	 * @return array An array of method information (interface, method name)
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getIntroducedMethodsFromInterfaceIntroductions(array $interfaceIntroductions) {
		$methods = array();
		$methodsAndIntroductions = array();
		foreach ($interfaceIntroductions as $introduction) {
			$interfaceName = $introduction->getInterfaceName();
			foreach (get_class_methods($interfaceName) as $newMethodName) {
				if (isset($methodsAndIntroductions[$newMethodName])) throw new \TYPO3\FLOW3\AOP\Exception('Method name conflict! Method "' . $newMethodName . '" introduced by "' . $introduction->getInterfaceName() . '" declared in aspect "' . $introduction->getDeclaringAspectClassName() . '" has already been introduced by "' . $methodsAndIntroductions[$newMethodName]->getInterfaceName() . '" declared in aspect "' . $methodsAndIntroductions[$newMethodName]->getDeclaringAspectClassName() . '".', 1173020942);
				$methods[] = array($interfaceName, $newMethodName);
				$methodsAndIntroductions[$newMethodName] = $introduction;
			}
		}
		return $methods;
	}

	/**
	 * Adds a "getAdviceChains()" method to the current proxy class.
	 *
	 * @param  $targetClassName
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildGetAdviceChainsMethodCode($targetClassName) {
		$proxyMethod = $this->compiler->getProxyClass($targetClassName)->getMethod('FLOW3_AOP_Proxy_getAdviceChains');
		$proxyMethod->setMethodParametersCode('$methodName');
		$proxyMethod->overrideMethodVisibility('private');

		$code = <<<'EOT'
		$adviceChains = array();
		if (isset($this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName])) {
			$adviceChains = $this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName];
		} else {
			if (isset($this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices[$methodName])) {
				$groupedAdvices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices[$methodName];
				if (isset($groupedAdvices['TYPO3\FLOW3\AOP\Advice\AroundAdvice'])) {
					$this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName]['TYPO3\FLOW3\AOP\Advice\AroundAdvice'] = new \TYPO3\FLOW3\AOP\Advice\AdviceChain($groupedAdvices['TYPO3\FLOW3\AOP\Advice\AroundAdvice'], $this);
					$adviceChains = $this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName];
				}
			}
		}
		return $adviceChains;

EOT;
		$proxyMethod->addPreParentCallCode($code);
	}

	/**
	 * Adds a "invokeJoinPoint()" method to the current proxy class.
	 *
	 * @param  $targetClassName
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildInvokeJoinPointMethodCode($targetClassName) {
		$proxyMethod = $this->compiler->getProxyClass($targetClassName)->getMethod('FLOW3_AOP_Proxy_invokeJoinPoint');
		$proxyMethod->setMethodParametersCode('\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint');
		$code = <<<'EOT'
		if (__CLASS__ !== $joinPoint->getClassName()) return parent::FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);
		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode[$joinPoint->getMethodName()])) {
			return call_user_func_array(array('self', $joinPoint->getMethodName()), $joinPoint->getMethodArguments());
		}

EOT;
		$proxyMethod->addPreParentCallCode($code);
	}

	/**
	 * Renders a short message which gives a hint on where the currently parsed pointcut expression was defined.
	 *
	 * @return void
	 */
	protected function renderSourceHint($aspectClassName, $methodName, $tagName) {
		return sprintf('%s::%s (%s advice)', $aspectClassName, $methodName, $tagName);
	}
}
?>