<?php
namespace TYPO3\FLOW3\MVC\CLI;

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

use \TYPO3\FLOW3\MVC\CLI\Command;
use \TYPO3\FLOW3\MVC\CLI\CommandManager;

/**
 * Builds a CLI request object from the raw command call
 *
 * @scope singleton
 */
class RequestBuilder {

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var CommandManager
	 */
	protected $commandManager;

	/**
	 * @param \TYPO3\FLOW3\Utility\Environment $environment
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectEnvironment(\TYPO3\FLOW3\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\FLOW3\Package\PackageManagerInterface $packageManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPackageManager(\TYPO3\FLOW3\Package\PackageManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 * @param \TYPO3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectReflectionService(\TYPO3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param CommandManager $commandManager
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function injectCommandManager(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Builds a CLI request object from a command line.
	 *
	 * The given command line may be a string (e.g. "mypackage:foo do-that-thing --force") or
	 * an array consisting of the individual parts. The array must not include the script
	 * name (like in $argv) but start with command right away.
	 *
	 * @param mixed $commandLine The command line, either as a string or as an array
	 * @return \TYPO3\FLOW3\MVC\CLI\Request The CLI request as an object
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function build($commandLine) {
		$request = new Request();
		$request->setControllerObjectName('TYPO3\FLOW3\Command\HelpCommandController');

		$rawCommandLineArguments = is_array($commandLine) ? $commandLine : explode(' ', $commandLine);
		if (count($rawCommandLineArguments) === 0) {
			$request->setControllerCommandName('helpStub');
			return $request;
		}
		$commandIdentifier = trim(array_shift($rawCommandLineArguments));
		try {
			$command = $this->commandManager->getCommandByIdentifier($commandIdentifier);
		} catch (\TYPO3\FLOW3\MVC\Exception\CommandException $exception) {
			$request->setArgument('exception', $exception);
			$request->setControllerCommandName('error');
			return $request;
		}
		$controllerObjectName = $this->objectManager->getObjectNameByClassName($command->getControllerClassName());
		$controllerCommandName = $command->getControllerCommandName();
		$request->setControllerObjectName($controllerObjectName);
		$request->setControllerCommandName($controllerCommandName);

		list($commandLineArguments, $exceedingCommandLineArguments) = $this->parseRawCommandLineArguments($rawCommandLineArguments, $controllerObjectName, $controllerCommandName);
		$request->setArguments($commandLineArguments);
		$request->setExceedingArguments($exceedingCommandLineArguments);

		return $request;
	}

	/**
	 * Takes an array of unparsed command line arguments and options and converts it separated
	 * by named arguments, options and unnamed arguments.
	 *
	 * @param array $rawCommandLineArguments The unparsed command parts (such as "--foo") as an array
	 * @param string $controllerObjectName Object name of the designated command controller
	 * @param string $controllerCommandName Command name of the recognized command (ie. method name without "Command" suffix)
	 * @return array All and exceeding command line arguments
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseRawCommandLineArguments(array $rawCommandLineArguments, $controllerObjectName, $controllerCommandName) {
		$commandLineArguments = array();
		$exceedingArguments = array();
		$commandMethodName = $controllerCommandName . 'Command';
		$commandMethodParameters = $this->reflectionService->getMethodParameters($controllerObjectName, $commandMethodName);

		$requiredArguments = array();
		$optionalArguments = array();
		$argumentNames = array();
		foreach ($commandMethodParameters as $parameterName => $parameterInfo) {
			$argumentNames[] = $parameterName;
			if ($parameterInfo['optional'] === FALSE) {
				$requiredArguments[strtolower($parameterName)] = array('parameterName' => $parameterName, 'type' => $parameterInfo['type']);
			} else {
				$optionalArguments[strtolower($parameterName)] = array('parameterName' => $parameterName, 'type' => $parameterInfo['type']);
			}
		}

		$decidedToUseNamedArguments = FALSE;
		$decidedToUseUnnamedArguments = FALSE;
		$argumentIndex = 0;
		while (count($rawCommandLineArguments) > 0) {

			$rawArgument = array_shift($rawCommandLineArguments);

			if ($rawArgument[0] === '-') {
				if ($rawArgument[1] === '-') {
					$rawArgument = substr($rawArgument, 2);
				} else {
					$rawArgument = substr($rawArgument, 1);
				}
				$argumentName = $this->extractArgumentNameFromCommandLinePart($rawArgument);

				if (isset($optionalArguments[$argumentName])) {
					$argumentValue = $this->getValueOfCurrentCommandLineOption($rawArgument, $rawCommandLineArguments, $optionalArguments[$argumentName]['type']);
					$commandLineArguments[$optionalArguments[$argumentName]['parameterName']] = $argumentValue;
				} elseif(isset($requiredArguments[$argumentName])) {
					if ($decidedToUseUnnamedArguments) {
						throw new \TYPO3\FLOW3\MVC\Exception\InvalidArgumentMixingException(sprintf('Unexpected named argument "%s". If you use unnamed arguments, all required arguments must be passed without a name.', $argumentName), 1309971821);
					}
					$decidedToUseNamedArguments = TRUE;
					$argumentValue = $this->getValueOfCurrentCommandLineOption($rawArgument, $rawCommandLineArguments, $requiredArguments[$argumentName]['type']);
					$commandLineArguments[$requiredArguments[$argumentName]['parameterName']] = $argumentValue;
					unset($requiredArguments[$argumentName]);
				}
			} else {
				if (count($requiredArguments) > 0) {
					if ($decidedToUseNamedArguments) {
						throw new \TYPO3\FLOW3\MVC\Exception\InvalidArgumentMixingException(sprintf('Unexpected unnamed argument "%s". If you use named arguments, all required arguments must be passed named.', $rawArgument), 1309971820);
					}
					$argument = array_shift($requiredArguments);
					$commandLineArguments[$argument['parameterName']] = $rawArgument;
					$decidedToUseUnnamedArguments = TRUE;
				} else {
					$exceedingArguments[] = $rawArgument;
				}
			}
			$argumentIndex ++;
		}

		return array($commandLineArguments, $exceedingArguments);
	}

	/**
	 * Extracts the option or argument name from the name / value pair of a command line.
	 *
	 * @param string $commandLinePart Part of the command line, e.g. "my-important-option=SomeInterestingValue"
	 * @return string The lowercased argument name, e.g. "myimportantoption"
	 */
	protected function extractArgumentNameFromCommandLinePart($commandLinePart) {
		$nameAndValue = explode('=', $commandLinePart, 2);
		return strtolower(str_replace('-', '', $nameAndValue[0]));
	}

	/**
	 * Returns the value of the first argument of the given input array. Shifts the parsed argument off the array.
	 *
	 * @param string $currentArgument The current argument
	 * @param array &$rawCommandLineArguments Array of the remaining command line arguments
	 * @param string $expectedArgumentType The expected type of the current argument, because booleans get special attention
	 * @return string The value of the first argument
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getValueOfCurrentCommandLineOption($currentArgument, array &$rawCommandLineArguments, $expectedArgumentType) {
		if (!isset($rawCommandLineArguments[0]) || (isset($rawCommandLineArguments[0]) && $rawCommandLineArguments[0][0] === '-' && (strpos($currentArgument, '=') === FALSE))) {
			return TRUE;
		}

		if (strpos($currentArgument, '=') === FALSE) {
			$possibleValue = trim(array_shift($rawCommandLineArguments));
			if (strpos($possibleValue, '=') === FALSE) {
				if ($expectedArgumentType !== 'boolean') {
					return $possibleValue;
				}
				if (array_search($possibleValue, array('on', '1', 'y', 'yes', 'true', 'TRUE')) !== FALSE) {
					return TRUE;
				}
				if (array_search($possibleValue, array('off', '0', 'n', 'no', 'false', 'FALSE')) !== FALSE) {
					return FALSE;
				}
				array_unshift($rawCommandLineArguments, $possibleValue);
				return TRUE;
			}
			$currentArgument .= $possibleValue;
		}

		$splitArgument = explode('=', $currentArgument, 2);
		while ((!isset($splitArgument[1]) || trim($splitArgument[1]) === '') && count($rawCommandLineArguments) > 0) {
			$currentArgument .= array_shift($rawCommandLineArguments);
			$splitArgument = explode('=', $currentArgument);
		}

		$value = (isset($splitArgument[1])) ? $splitArgument[1] : '';
		return $value;
	}

}
?>