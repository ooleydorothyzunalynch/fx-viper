<?php
namespace TYPO3\FLOW3\Tests\Unit\Property\TypeConverter;

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
 * Testcase for the Array converter
 *
 */
class ArrayConverterTest extends \TYPO3\FLOW3\Tests\UnitTestCase {


	/**
	 * @var \TYPO3\FLOW3\Property\TypeConverter\ArrayConverter
	 */
	protected $converter;

	public function setUp() {
		$this->converter = new \TYPO3\FLOW3\Property\TypeConverter\ArrayConverter();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function checkMetadata() {
		$this->assertEquals(array('array'), $this->converter->getSupportedSourceTypes(), 'Source types do not match');
		$this->assertEquals('array', $this->converter->getSupportedTargetType(), 'Target type does not match');
		$this->assertEquals(1, $this->converter->getPriority(), 'Priority does not match');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertFromDoesNotModifyTheSourceArray() {
		$sourceArray = array('Foo' => 'Bar', 'Baz');
		$this->assertEquals($sourceArray, $this->converter->convertFrom($sourceArray, 'array'));
	}
}
?>