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
 * Testcase for the Integer converter
 *
 * @covers \TYPO3\FLOW3\Property\TypeConverter\IntegerConverter<extended>
 */
class IntegerConverterTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\FLOW3\Property\TypeConverterInterface
	 */
	protected $converter;

	public function setUp() {
		$this->converter = new \TYPO3\FLOW3\Property\TypeConverter\IntegerConverter();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function checkMetadata() {
		$this->assertEquals(array('integer', 'string'), $this->converter->getSupportedSourceTypes(), 'Source types do not match');
		$this->assertEquals('integer', $this->converter->getSupportedTargetType(), 'Target type does not match');
		$this->assertEquals(1, $this->converter->getPriority(), 'Priority does not match');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function convertFromShouldCastTheStringToInteger() {
		$this->assertSame(15, $this->converter->convertFrom('15', 'integer'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertFromDoesNotModifyIntegers() {
		$source = 123;
		$this->assertSame($source, $this->converter->convertFrom($source, 'integer'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function canConvertFromShouldReturnTrueForANumericStringSource() {
		$this->assertTrue($this->converter->canConvertFrom('15', 'integer'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function canConvertFromShouldReturnTrueForAnIntegerSource() {
		$this->assertTrue($this->converter->canConvertFrom(123, 'integer'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getSourceChildPropertiesToBeConvertedShouldReturnEmptyArray() {
		$this->assertEquals(array(), $this->converter->getSourceChildPropertiesToBeConverted('myString'));
	}
}
?>