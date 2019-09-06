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
define('OBJECTSFORREVIEW_NMI_TYPE', 'OBJECTSFORREVIEW_NMI');

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
	 * @see PKPPlugin::getInstallEmailTemplatesFile()
	 */
	function getInstallEmailTemplatesFile() {
		return ($this->getPluginPath() . '/emailTemplates.xml');
	}

	/**
	 * @see PKPPlugin::getInstallEmailTemplateDataFile()
	 */
	function getInstallEmailTemplateDataFile() {
		return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
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

			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));

			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			HookRegistry::register('TemplateManager::display',array($this, 'addJs'));

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

			// Handler for public objects for review page
			HookRegistry::register('LoadHandler', array($this, 'loadPageHandler'));
			HookRegistry::register('NavigationMenus::itemTypes', array($this, 'addMenuItemTypes'));
			HookRegistry::register('NavigationMenus::displaySettings', array($this, 'setMenuItemDisplayDetails'));
			HookRegistry::register('SitemapHandler::createJournalSitemap', array($this, 'addSitemapURLs'));


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
				$objectId = $objectForReview->getId();
				$templateData[$objectId] = array(
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
	 * Add custom js for backend and frontend
	 */
	function addJs($hookName, $params) {
		$templateMgr = $params[0];
		$template =& $params[1];
		$request = $this->getRequest();

		$gridHandlerJs = $this->getJavaScriptURL($request, false) . DIRECTORY_SEPARATOR . 'ObjectsForReviewGridHandler.js';
		$templateMgr->addJavaScript(
			'ObjectsForReviewGridHandlerJs',
			$gridHandlerJs,
			array('contexts' => 'backend')
		);

		#error_log(print_r($template, true));
		if ($template == 'plugins-plugins-generic-objectsForReview-generic-objectsForReview:frontend/pages/forReview.tpl') {
			$tablesortJs = $this->getJavaScriptURL($request, false) . DIRECTORY_SEPARATOR . '/tablesort/src/tablesort.js';
			$templateMgr->addJavaScript(
				'TableSortJs',
				$tablesortJs,
				array('contexts' => 'frontend')
			);
			$tablesortCss = $request->getBaseUrl() . '/plugins/generic/objectsForReview/style.css';
			$templateMgr->addStyleSheet('tablesortCss', $tablesortCss);
		}

		return false;
	}

	/**
	 * Get the JavaScript URL for this plugin.
	 */
	function getJavaScriptURL() {
		return Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath() . DIRECTORY_SEPARATOR . 'js';
	}

	/**
	 * Load the handler to deal with browse by section page requests
	 *
	 * @param $hookName string `LoadHandler`
	 * @param $args array [
	 * 		@option string page
	 * 		@option string op
	 * 		@option string sourceFile
	 * ]
	 * @return bool
	 */
	public function loadPageHandler($hookName, $args) {
		$page = $args[0];
		if ($this->getEnabled() && $page === 'for-review') {
			$this->import('pages/ObjectsForReviewHandler');
			define('HANDLER_CLASS', 'ObjectsForReviewHandler');
			return true;
		}
		return false;
	}

	/**
	 * Add Navigation Menu Item types for linking to objects for review page
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option array Existing menu item types
	 * ]
	 */
	public function addMenuItemTypes($hookName, $args) {
		$types =& $args[0];
		$request = Application::getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$types[OBJECTSFORREVIEW_NMI_TYPE] = array(
			'title' => __('plugins.generic.objectsForReview.navMenuItem'),
			'description' => __('plugins.generic.objectsForReview.navMenuItem.description'),
		);
	}

	/**
	 * Set the display details for the custom menu item types
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option NavigationMenuItem
	 * ]
	 */
	public function setMenuItemDisplayDetails($hookName, $args) {
		$navigationMenuItem =& $args[0];
		if ($navigationMenuItem->getType() == OBJECTSFORREVIEW_NMI_TYPE) {
			$request = Application::getRequest();
			$context = $request->getContext();
			if ($context){
				$dispatcher = $request->getDispatcher();
				$navigationMenuItem->setUrl($dispatcher->url(
					$request,
					ROUTE_PAGE,
					null,
					'for-review'
				));
			}
		}
	}

	/**
	 * Add the objects for review page URL to the sitemap
	 *
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function addSitemapURLs($hookName, $args) {
		$doc = $args[0];
		$rootNode = $doc->documentElement;
		$request = Application::getRequest();
		$context = $request->getContext();
		if ($context) {
			// Create and append sitemap XML "url" element
			$url = $doc->createElement('url');
			$url->appendChild($doc->createElement('loc', htmlspecialchars($request->url($context->getPath(), 'for-review'), ENT_COMPAT, 'UTF-8')));
			$rootNode->appendChild($url);
		}
		return false;
	}

	/**
	 * Get the URL for JQuery JS.
	 * @param $request PKPRequest
	 * @return string
	 */
	private function _getJQueryUrl($request) {
		$min = Config::getVar('general', 'enable_minified') ? '.min' : '';
		if (Config::getVar('general', 'enable_cdn')) {
			return '//ajax.googleapis.com/ajax/libs/jquery/' . CDN_JQUERY_VERSION . '/jquery' . $min . '.js';
		} else {
			return $request->getBaseUrl() . '/lib/pkp/lib/vendor/components/jquery/jquery' . $min . '.js';
		}
	}

	/**
	 * Instantiate a MailTemplate
	 *
	 * @param string $emailKey
	 * @param Context $context
	 *
	 * @return MailTemplate
	 */
	function getMailTemplate($emailKey, $context = null) {
		import('lib.pkp.classes.mail.MailTemplate');
		return new MailTemplate($emailKey, null, $context, false);
	}

	/**
	 * Send mail to editor when object is reserved or cancelled
	 *
	 * @param User $user
	 * @param $object 
	 * @param $template Send either the reserve or cancel mail
	 */
	public function notifyEditor($user, $objectDescription, $mailTemplate) {

		$request = PKPApplication::getRequest();
		$context = $request->getContext();

		// This should only ever happen within a context, never site-wide.
		assert($context != null);
		$contextId = $context->getId();

		$mail = $this->getMailTemplate($mailTemplate, $context);

		// Set From to user
		$mail->setFrom($user->getData('email'), $user->getFullName());

		// Set To to editor or the email given in plugin settings
		if ($this->getSetting($contextId, 'ofrNotifyEmail')){
			$mail->setRecipients(array(array('name' =>  __('plugins.generic.objectsForReview.notifyDefaultName'), 'email' => $this->getSetting($contextId, 'ofrNotifyEmail'))));
		} else{
			$mail->setRecipients(array(array('name' => $context->getData('contactName'), 'email' => $context->getData('contactEmail'))));
		}

		// Send the mail with parameters
		$mail->sendWithParams(array(
			'objectDescription' => $objectDescription,
			'userName' => $user->getFullName(),
			'userEmail' => $user->getData('email'),
		));

	}
}
?>
