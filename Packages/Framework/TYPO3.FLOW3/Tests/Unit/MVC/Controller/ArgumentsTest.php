<?php
namespace TYPO3\FLOW3\Tests\Unit\MVC\Controller;

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

use \TYPO3\FLOW3\MVC\Controller\Arguments;
use \TYPO3\FLOW3\MVC\Controller\Argument;

/**
 * Testcase for the MVC Controller Arguments
 *
 * @covers \TYPO3\FLOW3\MVC\Controller\Arguments
 */
class ArgumentsTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addingAnArgumentManuallyWorks() {
		$arguments = new Arguments();
		$newArgument = new Argument('argumentName1234', 'Text');

		$arguments->addArgument($newArgument);
		$this->assertSame($newArgument, $arguments->getArgument('argumentName1234'), 'The added and retrieved argument is not the same.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addingAnArgumentReplacesArgumentWithSameName() {
		$arguments = new Arguments();

		$firstArgument = new Argument('argumentName1234', 'Text');
		$arguments->addArgument($firstArgument);

		$secondArgument = new Argument('argumentName1234', 'Text');
		$arguments->addArgument($secondArgument);

		$this->assertSame($secondArgument, $arguments->getArgument('argumentName1234'), 'The added and retrieved argument is not the same.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addingArgumentThroughArrayAccessWorks() {
		$arguments = new Arguments();
		$argument = new Argument('argumentName1234', 'Text');
		$arguments[] = $argument;
		$this->assertTrue($arguments->hasArgument('argumentName1234'), 'Added argument does not exist.');
		$this->assertSame($argument, $arguments->getArgument('argumentName1234'), 'Added and retrieved arguments are not the same.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function retrievingArgumentThroughArrayAccessWorks() {
		$arguments = new Arguments();
		$newArgument = $arguments->addNewArgument('someArgument');
		$this->assertSame($newArgument, $arguments['someArgument'], 'Argument retrieved by array access is not the one we added.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentWithNonExistingArgumentNameThrowsException() {
		$arguments = new Arguments();
		try {
			$arguments->getArgument('someArgument');
			$this->fail('getArgument() did not throw an exception although the specified argument does not exist.');
		} catch (\TYPO3\FLOW3\MVC\Exception\NoSuchArgumentException $exception) {
		}
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function issetReturnsCorrectResult() {
		$arguments = new Arguments();
		$this->assertFalse(isset($arguments['someArgument']), 'isset() did not return FALSE.');
		$arguments->addNewArgument('someArgument');
		$this->assertTrue(isset($arguments['someArgument']), 'isset() did not return TRUE.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentNamesReturnsNamesOfAddedArguments() {
		$arguments = new Arguments();
		$arguments->addNewArgument('first');
		$arguments->addNewArgument('second');
		$arguments->addNewArgument('third');

		$expectedArgumentNames = array('first', 'second', 'third');
		$this->assertEquals($expectedArgumentNames, $arguments->getArgumentNames(), 'Returned argument names were not as expected.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentShortNamesReturnsShortNamesOfAddedArguments() {
		$arguments = new Arguments();
		$arguments->addNewArgument('first')->setShortName('a');
		$arguments->addNewArgument('second')->setShortName('b');
		$arguments->addNewArgument('third')->setShortName('c');

		$expectedShortNames = array('a', 'b', 'c');
		$this->assertEquals($expectedShortNames, $arguments->getArgumentShortNames(), 'Returned argument short names were not as expected.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addNewArgumentCreatesAndAddsNewArgument() {
		$arguments = new Arguments();
		$addedArgument = $arguments->addNewArgument('dummyName');
		$this->assertInstanceOf('TYPO3\FLOW3\MVC\Controller\Argument', $addedArgument, 'addNewArgument() either did not add a new argument or did not return it.');

		$retrievedArgument = $arguments['dummyName'];
		$this->assertSame($addedArgument, $retrievedArgument, 'The added and the retrieved argument are not the same.');

		$this->assertEquals('dummyName', $addedArgument->getName(), 'The name of the added argument is not as expected.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addNewArgumentAssumesTextDataTypeByDefault() {
		$arguments = new Arguments();
		$arguments->addNewArgument('dummyName');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function addNewArgumentCanAddArgumentsMarkedAsRequired() {
		$arguments = new Arguments();
		$addedArgument = $arguments->addNewArgument('dummyName', 'Text', TRUE);
		$this->assertTrue($addedArgument->isRequired(), 'addNewArgument() did not create an argument that is marked as required.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addNewArgumentCanAddArgumentsMarkedAsOptionalWithDefaultValues() {
		$arguments = new Arguments();
		$defaultValue = 'Default Value 42';
		$addedArgument = $arguments->addNewArgument('dummyName', 'Text', FALSE, $defaultValue);
		$this->assertEquals($defaultValue, $addedArgument->getValue(), 'addNewArgument() did not store the default value in the argument.');
	}

	/**
	 * @test
	 * @expectedException \LogicException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function callingInvalidMethodThrowsException() {
		$arguments = new Arguments();
		$arguments->nonExistingMethod();
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function removeAllClearsAllArguments() {
		$arguments = new Arguments();
		$arguments->addArgument(new Argument('foo', 'Text'));

		$arguments->removeAll();

		$this->assertFalse($arguments->hasArgument('foo'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getValidationResultsShouldFetchAllValidationResltsFromArguments() {
		$error1 = new \TYPO3\FLOW3\Error\Error('Validation error', 1234);
		$error2 = new \TYPO3\FLOW3\Error\Error('Validation error 2', 1235);

		$results1 = new \TYPO3\FLOW3\Error\Result();
		$results1->addError($error1);

		$results2 = new \TYPO3\FLOW3\Error\Result();
		$results2->addError($error2);

		$argument1 = $this->getMock('TYPO3\FLOW3\MVC\Controller\Argument', array('getValidationResults'), array('name1', 'string'));
		$argument1->expects($this->once())->method('getValidationResults')->will($this->returnValue($results1));

		$argument2 = $this->getMock('TYPO3\FLOW3\MVC\Controller\Argument', array('getValidationResults'), array('name2', 'string'));
		$argument2->expects($this->once())->method('getValidationResults')->will($this->returnValue($results2));

		$arguments = new \TYPO3\FLOW3\MVC\Controller\Arguments();
		$arguments->addArgument($argument1);
		$arguments->addArgument($argument2);
		$this->assertSame(array('name1' => array($error1), 'name2' => array($error2)), $arguments->getValidationResults()->getFlattenedErrors());
	}
}
?>