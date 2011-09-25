<?php
namespace FX\Viper\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "FX.Viper".                   *
 *                                                                        *
 *                                                                        */

/**
 * Standard controller for the FX.Viper package 
 *
 * @scope singleton
 */
class StandardController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}
}
?>