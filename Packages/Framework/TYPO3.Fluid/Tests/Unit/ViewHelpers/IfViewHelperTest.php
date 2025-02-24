<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Testcase for IfViewHelper
 *
 */
class IfViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\ViewHelpers\IfViewHelper
	 */
	protected $viewHelper;

	/**
	 * var \TYPO3\Fluid\Core\ViewHelper\Arguments
	 */
	protected $mockArguments;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\IfViewHelper', array('renderThenChild', 'renderElseChild'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersThenChildIfConditionIsTrue() {
		$this->viewHelper->expects($this->at(0))->method('renderThenChild')->will($this->returnValue('foo'));

		$actualResult = $this->viewHelper->render(TRUE);
		$this->assertEquals('foo', $actualResult);
	}


	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersElseChildIfConditionIsFalse() {
		$this->viewHelper->expects($this->at(0))->method('renderElseChild')->will($this->returnValue('foo'));

		$actualResult = $this->viewHelper->render(FALSE);
		$this->assertEquals('foo', $actualResult);
	}
}

?>
