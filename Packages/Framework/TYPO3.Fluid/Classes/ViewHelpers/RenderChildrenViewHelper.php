<?php
namespace TYPO3\Fluid\ViewHelpers;

/*
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

/**
 * Render the inner parts of a Widget.
 * This ViewHelper can only be used in a template which belongs to a Widget Controller.
 *
 * It renders everything inside the Widget ViewHelper, and you can pass additional
 * arguments.
 *
 * @api
 * @scope prototype
 */
class RenderChildrenViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param array $arguments
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render(array $arguments = array()) {
		$renderingContext = $this->getWidgetRenderingContext();
		$widgetChildNodes = $this->getWidgetChildNodes();

		$this->addArgumentsToTemplateVariableContainer($arguments);
		$output = $widgetChildNodes->evaluate($renderingContext);
		$this->removeArgumentsFromTemplateVariableContainer($arguments);

		return $output;
	}

	/**
	 * Get the widget rendering context, or throw an exception if it cannot be found.
	 *
	 * @return \TYPO3\Fluid\Core\Rendering\RenderingContextInterface
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getWidgetRenderingContext() {
		$renderingContext = $this->getWidgetContext()->getViewHelperChildNodeRenderingContext();
		if (!($renderingContext instanceof \TYPO3\Fluid\Core\Rendering\RenderingContextInterface)) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\RenderingContextNotFoundException('Rendering Context not found inside Widget. <f:renderChildren> has been used in an AJAX Request, but is only usable in non-ajax mode.', 1284986604);
		}
		return $renderingContext;
	}

	/**
	 * @return \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getWidgetChildNodes() {
		return $this->getWidgetContext()->getViewHelperChildNodes();
	}

	/**
	 * @return \TYPO3\Fluid\Core\Widget\WidgetContext
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getWidgetContext() {
		$request = $this->controllerContext->getRequest();
		$widgetContext = $request->getInternalArgument('__widgetContext');
		if ($widgetContext === NULL) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException('The Request does not contain a widget context! <f:renderChildren> must be called inside a Widget Template.', 1284986120);
		}

		return $widgetContext;
	}

	/**
	 * Add the given arguments to the TemplateVariableContainer of the widget.
	 *
	 * @param array $arguments
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function addArgumentsToTemplateVariableContainer(array $arguments) {
		$templateVariableContainer = $this->getWidgetRenderingContext()->getTemplateVariableContainer();
		foreach ($arguments as $identifier => $value) {
			$templateVariableContainer->add($identifier, $value);
		}
	}

	/**
	 * Remove the given arguments from the TemplateVariableContainer of the widget.
	 *
	 * @param array $arguments
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function removeArgumentsFromTemplateVariableContainer(array $arguments) {
		$templateVariableContainer = $this->getWidgetRenderingContext()->getTemplateVariableContainer();
		foreach ($arguments as $identifier => $value) {
			$templateVariableContainer->remove($identifier);
		}
	}
}
?>