<?php
namespace TYPO3\FLOW3;

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

use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * The FLOW3 Package
 *
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$bootstrap->registerCompiletimeCommand('typo3.flow3:core:*');
		$bootstrap->registerCompiletimeCommand('typo3.flow3:cache:flush');

		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'finishedRuntimeRun', 'TYPO3\FLOW3\Persistence\PersistenceManagerInterface', 'persistAll');
		$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'dispatchedCommandLineSlaveRequest', 'TYPO3\FLOW3\Persistence\PersistenceManagerInterface', 'persistAll');
		$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'bootstrapShuttingDown', 'TYPO3\FLOW3\Configuration\ConfigurationManager', 'shutdown');
		$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'bootstrapShuttingDown', 'TYPO3\FLOW3\Object\ObjectManagerInterface', 'shutdown');
		$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'bootstrapShuttingDown', 'TYPO3\FLOW3\Reflection\ReflectionService', 'saveToCache');

		$dispatcher->connect('TYPO3\FLOW3\Command\CoreCommandController', 'finishedCompilationRun', 'TYPO3\FLOW3\Security\Policy\PolicyService', 'savePolicyCache');
	}
}

?>
