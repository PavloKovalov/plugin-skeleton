<?php

/**
 * Pluginname
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @see http://www.seotoaster.com/
 */
set_include_path(realpath(dirname(__FILE__) . '/lib') . PATH_SEPARATOR . get_include_path());

class Pluginname implements RCMS_Core_PluginInterface {
	const NAME = 'Pluginname';
	
	private $_model = null;
	private $_view = null;
	private $_request = null;
	private $_responce = null;
	private $_session = null;
	protected $_options = null;
	protected $_seotoasterData = null;
	protected $_srcRequestParams = null;
	private $_websiteUrl = '';
	private $_securedActions = array();

	public function __construct($options, $toasterData) {
		$this->_options = $options;
		$this->_toasterData = $toasterData;

		$this->_websiteUrl = $this->_toasterData['websiteUrl'];

		// adding helpers
		$this->_request = new Zend_Controller_Request_Http();
		$this->_responce = new Zend_Controller_Response_Http();

		//retriving session
		$this->_session = new Zend_Session_Namespace($this->_websiteUrl);

		// creating view
		$this->_view = new Zend_View(array(
				'scriptPath' => dirname(__FILE__) . '/lib/views'
			));
		$this->_view->websiteUrl = $this->_websiteUrl;
		$this->_view->pluginName = self::NAME;
	}

	public function run($requestParams = array()) {
		if (isset($params['run'])) {
			$methodName = '_' . strtolower($params['run']) . 'Action';
			if (method_exists($this, $methodName)) {
				if (!$this->_checkLogin() && in_array(strtolower($params['run']), $this->_securedActions)) {
					throw new Exception('Not allowed action');
				}
				return $this->$methodName($requestParams);
			}
		} elseif (!empty($this->_options[0])) {
			$methodName = array_shift($this->_options[0]);
			$methodName = '_' . strtolower($methodName) . 'Generator';
			if (method_exists($this, $methodName)) {
				return $this->$methodName($this->_options);
			}
		}
		return;
	}

	private function _checkLogin() {
		$currentUser = unserialize($this->_session->currentUser);
		if (is_a($currentUser, 'RCMS_Object_User_User')) {
			if ($currentUser->getRoleId() == RCMS_Object_User_User::USER_ROLE_ADMIN ||
				$currentUser->getRoleId() == RCMS_Object_User_User::USER_ROLE_SUPERADMIN) {
				return true;
			}
		}
		return false;
	}

}