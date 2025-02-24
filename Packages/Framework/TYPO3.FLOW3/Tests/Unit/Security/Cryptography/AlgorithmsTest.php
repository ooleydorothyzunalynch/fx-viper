<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FLOW3\Tests\Unit\Security\Cryptography;

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
 * Testcase for the cryptographic algorithms
 *
 */
class AlgorithmsTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * Data provider for pbkdf2TestVectorsAreCorrect()
	 *
	 * Based on certified test vectors from http://tools.ietf.org/html/rfc6070
	 *
	 * @return array
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function pbkdf2TestVectors() {
		$output = array();

		$output[] = array('password', 'salt', 1, 20, '0c60c80f961f0e71f3a9b524af6012062fe037a6');
		$output[] = array('password', 'salt', 2, 20, 'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957');
		$output[] = array('password', 'salt', 4096, 20, '4b007901b765489abead49d926f721d065a429c1');
		$output[] = array('passwordPASSWORDpassword', 'saltSALTsaltSALTsaltSALTsaltSALTsalt', 4096, 25, '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038');
		$output[] = array('pass' . pack('H', '00') . 'word', 'sa' . pack('H', '00') . 'lt', 4096, 16, '56fa6aa75548099dcc37d7f03425e0c3');

		return $output;
	}

	/**
	 * @test
	 * @dataProvider pbkdf2TestVectors
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function pbkdf2TestVectorsAreCorrect($password, $salt, $iterationCount, $derivedKeyLength, $output) {
		$result = \TYPO3\FLOW3\Security\Cryptography\Algorithms::pbkdf2($password, $salt, $iterationCount, $derivedKeyLength, 'sha1');
		$this->assertEquals(unpack('H*', pack('H*', $output)), unpack('H*', $result));
	}

}
?>