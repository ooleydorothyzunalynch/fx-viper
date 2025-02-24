<?php
namespace TYPO3\FLOW3\MVC\Controller;

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
 * The default property mapping configuration is available
 * inside the Argument-object.
 *
 * @api
 * @scope prototype
 */
class MvcPropertyMappingConfiguration extends \TYPO3\FLOW3\Property\PropertyMappingConfiguration {

	/**
	 * Allow creation of a certain sub property
	 *
	 * @param string $propertyPath
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function allowCreationForSubProperty($propertyPath) {
		$this->forProperty($propertyPath)->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
	}

	/**
	 * Allow modification for a given property path
	 *
	 * @param string $propertyPath
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function allowModificationForSubProperty($propertyPath) {
		$this->forProperty($propertyPath)->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
	}

	/**
	 * Set the target type for a certain property. Especially useful
	 * if there is an object which has a nested object which is abstract,
	 * and you want to instanciate a concrete object instead.
	 *
	 * @param string $propertyPath
	 * @param string $targetType
	 * @return void
	 * @api
	 */
	public function setTargetTypeForSubProperty($propertyPath, $targetType) {
		$this->forProperty($propertyPath)->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_TARGET_TYPE, $targetType);
	}
}
?>