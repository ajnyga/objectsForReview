<?php

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewPlugin.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewPlugin
 * @ingroup plugins_generic_objectsForReview

 * @brief Add objectsForReview data to the submission metadata and display them on the submission view page.
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ObjectsForReviewPlugin extends GenericPlugin {

	/**
	 * @copydoc Plugin::getName()
	 */
	function getName() {
		return 'ObjectsForReviewPlugin';
    }

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
    function getDisplayName() {
		return __('plugins.generic.objectsForReview.displayName');
    }

	/**
	 * @copydoc Plugin::getDescription()
	 */
    function getDescription() {
		return __('plugins.generic.objectsForReview.description');
    }

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();

				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));

				$this->import('ObjectsForReviewSettingsForm');
				$form = new ObjectsForReviewSettingsForm($this, $context->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
	 * @copydoc Plugin::register()
	 */
    function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if ($success && $this->getEnabled($mainContextId)) {

			$request = $this->getRequest();
			$context = $request->getContext();

			import('plugins.generic.objectsForReview.classes.ObjectForReviewDAO');
			$objectForReviewDao = new ObjectForReviewDAO();
			DAORegistry::registerDAO('ObjectForReviewDAO', $objectForReviewDao);

			// Handler for public objects for review page
			#HookRegistry::register('LoadHandler', array($this, 'loadPageHandler'));

			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));

			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			HookRegistry::register('TemplateManager::display',array($this, 'addGridhandlerJs'));

			HookRegistry::register('Templates::Management::Settings::website', array($this, 'callbackShowWebsiteSettingsTabs'));

			// if list display is enabled
			if ($this->getSetting($context->getId(), 'displayAsList')) {
				HookRegistry::register('Templates::Article::Main', array($this, 'addSubmissionDisplay'));
				HookRegistry::register('Templates::Catalog::Book::Main', array($this, 'addSubmissionDisplay'));
			}

			// If subtitle display is enabled
			if ($this->getSetting($context->getId(), 'displayAsSubtitle')) {
				HookRegistry::register('ArticleDAO::_fromRow', array($this, 'addSubtitleDisplay'));
				HookRegistry::register('MonographDAO::_fromRow', array($this, 'addSubtitleDisplay'));
			}
		}
		return $success;
	}

	/**
	 * Extend the website settings tabs to include custom locale
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$output =& $args[2];
		$request =& Registry::get('request');
		$dispatcher = $request->getDispatcher();
		$output .= '<li><a name="objectsForReviewManagement" href="' . $dispatcher->url($request, ROUTE_COMPONENT, null, 'plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewManagementGridHandler', 'fetchGrid') . '">' . __('plugins.generic.objectsForReview.managementLink') . '</a></li>';
		return false;
	}

	/**
	 * Permit requests to the ObjectsForReview grid handler
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridHandler') {
			import($component);
			ObjectsForReviewGridHandler::setPlugin($this);
			return true;
		}
		if ($component == 'plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewManagementGridHandler') {
			import($component);
			ObjectsForReviewManagementGridHandler::setPlugin($this);
			return true;
		}
		return false;
	}

	/**
	 * Insert ObjectsForReview grid in the submission metadata form
	 */
	function metadataFieldEdit($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$request = $this->getRequest();
		$output .= $smarty->fetch($this->getTemplateResource('metadataForm.tpl'));
		return false;
	}

	/**
	* Hook to Templates::Article::Details and Templates::Catalog::Book::Details and list object for review information
	* @param $hookName string
	* @param $params array
	*/
	function addSubmissionDisplay($hookName, $params) {
		$templateMgr = $params[1];
		$output =& $params[2];

		$submission = $templateMgr->get_template_vars('monograph') ? $templateMgr->get_template_vars('monograph') : $templateMgr->get_template_vars('article');

		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getBySubmissionId($submission->getId());

		if ($objectsForReview){
			$templateData = array();

			while ($objectForReview = $objectsForReview->next()) {
				$reviewId = $objectForReview->getId();
				$templateData[$reviewId] = array(
					'identifierType' => $objectForReview->getIdentifierType(),
					'identifier' => $objectForReview->getIdentifier(),
					'description' => $objectForReview->getDescription()
				);
			}

			if ($objectsForReview){
				$templateMgr->assign('objectsForReview', $templateData);
				$output .= $templateMgr->fetch($this->getTemplateResource('listReviews.tpl'));
			}

		}

		return false;
	}

	/**
	* Hook to ArticleDAO::_fromRow and MonographDAO::_fromRow and display objectForReview as subtitle
	* @param $hookName string
	* @param $params array
	*/ 
	function addSubtitleDisplay($hookName, $params) {
		$submission =& $params[0];

		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getBySubmissionId($submission->getId());

		if ($objectsForReview){
			$objects = array();
			while ($objectForReview = $objectsForReview->next()) {
				$objects[] = $objectForReview->getDescription();
			}

			if ($objects){
				$submission->setSubtitle(implode(" ▪ ", $objects), $submission->getLocale());
			}
		}

		return false;
	}

	/**
	 * @copydoc Plugin::getInstallSchemaFile()
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/schema.xml';
	}

	/**
	 * Add custom gridhandlerJS for backend
	 */
	function addGridhandlerJs($hookName, $params) {
		$templateMgr = $params[0];
		$request = $this->getRequest();
		$gridHandlerJs = $this->getJavaScriptURL($request, false) . DIRECTORY_SEPARATOR . 'ObjectsForReviewGridHandler.js';
		$templateMgr->addJavaScript(
			'ObjectsForReviewGridHandlerJs',
			$gridHandlerJs,
			array('contexts' => 'backend')
		);
		return false;
	}

	/**
	 * Get the JavaScript URL for this plugin.
	 */
	function getJavaScriptURL() {
		return Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath() . DIRECTORY_SEPARATOR . 'js';
	}

}

?>
