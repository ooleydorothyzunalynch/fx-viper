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
 * The controller context holds information about the request, response, arguments
 * and further details of a controller. Instances of this class act as a container
 * for conveniently passing the information to other classes who need it, usually
 * views being views or view helpers.
 *
 * @api
 * @scope prototype
 */
class ControllerContext {

	/**
	 * @var \TYPO3\FLOW3\MVC\RequestInterface
	 */
	protected $request;

	/**
	 * @var \TYPO3\FLOW3\MVC\ResponseInterface
	 */
	protected $response;

	/**
	 * @var \TYPO3\FLOW3\MVC\Controller\Arguments
	 */
	protected $arguments;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder
	 */
	protected $uriBuilder;

	/**
	 * @var \TYPO3\FLOW3\MVC\Controller\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	/**
	 * Constructs this context
	 *
	 * @param \TYPO3\FLOW3\MVC\RequestInterface $request
	 * @param \TYPO3\FLOW3\MVC\ResponseInterface $response
	 * @param \TYPO3\FLOW3\MVC\Controller\Arguments $arguments
	 * @param \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder $uriBuilder
	 * @param \TYPO3\FLOW3\MVC\Controller\FlashMessageContainer $flashMessageContainer The flash messages
	 */
	public function __construct(\TYPO3\FLOW3\MVC\RequestInterface $request, \TYPO3\FLOW3\MVC\ResponseInterface $response, \TYPO3\FLOW3\MVC\Controller\Arguments $arguments,
			\TYPO3\FLOW3\MVC\Web\Routing\UriBuilder $uriBuilder, \TYPO3\FLOW3\MVC\Controller\FlashMessageContainer $flashMessageContainer) {
		$this->request = $request;
		$this->response = $response;
		$this->arguments = $arguments;
		$this->uriBuilder = $uriBuilder;
		$this->flashMessageContainer = $flashMessageContainer;
	}

	/**
	 * Get the request of the controller
	 *
	 * @return \TYPO3\FLOW3\MVC\RequestInterface
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get the response of the controller
	 *
	 * @return \TYPO3\FLOW3\MVC\RequestInterface
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Get the arguments of the controller
	 *
	 * @return \TYPO3\FLOW3\MVC\Controller\Arguments
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * Returns the URI Builder bound to this context
	 *
	 * @return \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function getUriBuilder() {
		return $this->uriBuilder;
	}

	/**
	 * Get the flash message container
	 *
	 * @return \TYPO3\FLOW3\MVC\Controller\FlashMessageContainer A container for flash messages
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function getFlashMessageContainer() {
		return $this->flashMessageContainer;
	}
}
?>