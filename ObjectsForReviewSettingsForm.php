<?php

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewSettingsForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewSettingsForm
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Form for journal managers to modify Objects for Review plugin settings
 */

namespace APP\plugins\generic\objectsForReview;

use APP\template\TemplateManager;


class ObjectsForReviewSettingsForm extends \PKP\form\Form {

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

		$this->addCheck(new \PKP\form\validation\FormValidatorEmail($this, 'ofrNotifyEmail', 'optional'));
		$this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
		$this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$this->_data = array(
			'displayAsList' => $this->_plugin->getSetting($this->_journalId, 'displayAsList'),
			'onlyReserved' => $this->_plugin->getSetting($this->_journalId, 'onlyReserved'),
			'ofrNotifyEmail' => $this->_plugin->getSetting($this->_journalId, 'ofrNotifyEmail'),
			'ofrInstructions' => $this->_plugin->getSetting($this->_journalId, 'ofrInstructions'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('displayAsList', 'onlyReserved', 'ofrNotifyEmail', 'ofrInstructions'));
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
		$this->_plugin->updateSetting($this->_journalId, 'displayAsList', $this->getData('displayAsList'), 'bool');
		$this->_plugin->updateSetting($this->_journalId, 'onlyReserved', $this->getData('onlyReserved'), 'bool');
		$this->_plugin->updateSetting($this->_journalId, 'ofrNotifyEmail', $this->getData('ofrNotifyEmail'), 'string');
		$this->_plugin->updateSetting($this->_journalId, 'ofrInstructions', $this->getData('ofrInstructions'));

	}
}
