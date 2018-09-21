<?php

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewPlugin
 * @ingroup plugins_generic_objectsForReview

 * @brief Add objectsForReview data to the submission metadata, consider them in the Crossref export,
 * and display them on the submission view page.
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

			import('plugins.generic.objectsForReview.classes.ObjectForReviewDAO');
			$objectForReviewDao = new ObjectForReviewDAO();
			DAORegistry::registerDAO('ObjectForReviewDAO', $objectForReviewDao);

			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));

			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));

			HookRegistry::register('Templates::Article::Details', array($this, 'addSubmissionDisplay'));
			HookRegistry::register('Templates::Catalog::Book::Details', array($this, 'addSubmissionDisplay'));

		}
		return $success;
	}


	/**
	 * Permit requests to the Funder grid handler
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
		return false;
	}

	/**
	 * Insert funder grid in the submission metadata form
	 */
	function metadataFieldEdit($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$request = $this->getRequest();

		$output .= $smarty->fetch($this->getTemplateResource('metadataForm.tpl'));


		return false;
	}

	/**
	* Hook to Templates::Article::Details and Templates::Catalog::Book::Details and list funder information
	* @param $hookName string
	* @param $params array
	*/
	function addSubmissionDisplay($hookName, $params) {
		$templateMgr = $params[1];
		$output =& $params[2];

		$submission = $templateMgr->get_template_vars('monograph') ? $templateMgr->get_template_vars('monograph') : $templateMgr->get_template_vars('article');

		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');

		$objectsForReview = $objectForReviewDao->getBySubmissionId($submission->getId());

		/*
		$funderData = array();
		while ($funder = $funders->next()) {
			$funderId = $funder->getId();
			$funderAwards = $funderAwardDao->getFunderAwardNumbersByFunderId($funderId);
			$funderData[$funderId] = array(
				'funderName' => $funder->getFunderName(),
				'funderIdentification' => $funder->getFunderIdentification(),
				'funderAwards' => implode(";", $funderAwards)
			);
		}

		if ($funderData){
			$templateMgr->assign('funderData', $funderData);
			$output .= $templateMgr->fetch($this->getTemplatePath() . 'listFunders.tpl');
		}
		*/
		return false;

	}

	/**
		 * @copydoc Plugin::getInstallSchemaFile()
		 */
		function getInstallSchemaFile() {
			return $this->getPluginPath() . '/schema.xml';
		}



		/**
	 * @copydoc PKPPlugin::getTemplatePath

	function getTemplatePath($inCore = false) {
		return $this->getTemplateResource('plugins/generic/objectsForReview/templates/');
	}

*/
}

?>
