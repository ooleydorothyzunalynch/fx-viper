<?php
namespace TYPO3\FLOW3\Tests\Unit\Validation\Validator;

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

require_once('AbstractValidatorTestcase.php');

/**
 * Testcase for the regular expression validator
 *
 */
class RegularExpressionValidatorTest extends \TYPO3\FLOW3\Tests\Unit\Validation\Validator\AbstractValidatorTestcase {

	protected $validatorClassName = 'TYPO3\FLOW3\Validation\Validator\RegularExpressionValidator';

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function regularExpressionValidatorMatchesABasicExpressionCorrectly() {
		$this->validatorOptions(array('regularExpression' => '/^simple[0-9]expression$/'));

		$this->assertFalse($this->validator->validate('simple1expression')->hasErrors());
		$this->assertTrue($this->validator->validate('simple1expressions')->hasErrors());
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function regularExpressionValidatorCreatesTheCorrectErrorIfTheExpressionDidNotMatch() {
		$this->validatorOptions(array('regularExpression' => '/^simple[0-9]expression$/'));
		$subject = 'some subject that will not match';
		$errors = $this->validator->validate($subject)->getErrors();
		$this->assertEquals(array(new \TYPO3\FLOW3\Validation\Error('The given subject did not match the pattern. Got: %1$s', 1221565130, array($subject))), $errors);
	}
}

?>