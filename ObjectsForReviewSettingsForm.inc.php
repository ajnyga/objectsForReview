<?php

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewSettingsForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewSettingsForm
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Form for journal managers to modify Objects for Review plugin settings
 */

import('lib.pkp.classes.form.Form');

class ObjectsForReviewSettingsForm extends Form {

	/** @var int */
	var $_journalId;

	/** @var object */
	var $_plugin;

	/**
	 * Constructor
	 * @param $plugin ObjectsForReviewPlugin
	 * @param $journalId int
	 */
	function __construct($plugin, $journalId) {
		$this->_journalId = $journalId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$request = Application::getRequest();
		$context = $request->getContext();
		$this->_data = array(
			'displayAsSubtitle' => $this->_plugin->getSetting($this->_journalId, 'displayAsSubtitle'),
			'displayAsList' => $this->_plugin->getSetting($this->_journalId, 'displayAsList'),
			'onlyReserved' => $this->_plugin->getSetting($this->_journalId, 'onlyReserved'),
			'ofrNotifyEmail' => $this->_plugin->getSetting($this->_journalId, 'ofrNotifyEmail'),
			'ofrInstructions' => $context->getSetting('ofrInstructions'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('displayAsSubtitle', 'displayAsList', 'onlyReserved', 'ofrNotifyEmail', 'ofrInstructions'));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save settings.
	 */
	function execute(...$functionArgs) {
		$request = Application::getRequest();
		$context = $request->getContext();
		$this->_plugin->updateSetting($this->_journalId, 'displayAsSubtitle', $this->getData('displayAsSubtitle'), 'bool');
		$this->_plugin->updateSetting($this->_journalId, 'displayAsList', $this->getData('displayAsList'), 'bool');
		$this->_plugin->updateSetting($this->_journalId, 'onlyReserved', $this->getData('onlyReserved'), 'bool');
		$this->_plugin->updateSetting($this->_journalId, 'ofrNotifyEmail', $this->getData('ofrNotifyEmail'), 'string');
		$context->updateSetting('ofrInstructions', $this->getData('ofrInstructions'), 'object', true);
	}
}

?>
