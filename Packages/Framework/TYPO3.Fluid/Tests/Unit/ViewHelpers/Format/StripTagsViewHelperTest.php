<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class StripTagsViewHelperTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Format\StripTagsViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\StripTagsViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperDeactivatesEscapingInterceptor() {
		$this->assertFalse($this->viewHelper->isEscapingInterceptorEnabled());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderUsesValueAsSourceIfSpecified() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render('Some string');
		$this->assertEquals('Some string', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderUsesChildnodesAsSourceIfSpecified() {
		$this->viewHelper->expects($this->atLeastOnce())->method('renderChildren')->will($this->returnValue('Some string'));
		$actualResult = $this->viewHelper->render();
		$this->assertEquals('Some string', $actualResult);
	}

	/**
	 * Data Provider for the render tests
	 *
	 * @return array
	 */
	public function testStrings() {
		return array(
			array('This is a sample text without special characters.', 'This is a sample text without special characters.'),
			array('This is a sample text <b>with <i>some</i> tags</b>.', 'This is a sample text with some tags.'),
			array('This text contains some &quot;&Uuml;mlaut&quot;.', 'This text contains some &quot;&Uuml;mlaut&quot;.')
		);
	}

	/**
	 * @test
	 * @dataProvider testStrings
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCorrectlyConvertsIntoPlaintext($source, $expectedResult) {
		$actualResult = $this->viewHelper->render($source);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsUnmodifiedSourceIfItIsNoString() {
		$source = new \stdClass();
		$actualResult = $this->viewHelper->render($source);
		$this->assertSame($source, $actualResult);
	}
}
?>
