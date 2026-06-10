<?php

/**
 * @file plugins/generic/objectsForReview/ObjectsForReviewPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewPlugin
 * @ingroup plugins_generic_objectsForReview
 * @brief Add objectsForReview data to the submission metadata and display them on the submission view page.
 *
 */

namespace APP\plugins\generic\objectsForReview;

use APP\core\Application;
use APP\pages\submission\SubmissionHandler;
use APP\plugins\generic\objectsForReview\classes\ObjectForReview;
use APP\plugins\generic\objectsForReview\classes\ObjectForReviewDAO;
use APP\plugins\generic\objectsForReview\classes\migration\install\SchemaMigration;

use APP\plugins\generic\objectsForReview\controllers\grid\ObjectsForReviewGridHandler;
use APP\plugins\generic\objectsForReview\controllers\grid\ObjectsForReviewManagementGridHandler;
use APP\plugins\generic\objectsForReview\pages\ObjectsForReviewHandler;

use APP\plugins\generic\objectsForReview\mailables\ObjectsForReviewNew;
use APP\plugins\generic\objectsForReview\mailables\ObjectsForReviewCancel;

use APP\facades\Repo;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\LinkAction;
use PKP\core\JSONMessage;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use Illuminate\Support\Facades\Mail;


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
     * @copydoc Plugin::getInstallMigration()
     */
    function getInstallMigration() {
		    error_log('getInstallMigration called, getName: ' . $this->getName());

        return new SchemaMigration();
    }

	/**
	 * @copydoc Plugin::getActions()
	 */
    public function getActions($request, $verb)
    {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() 
                ? [
                    new LinkAction(
                        'settings',
                        new AjaxModal(
                            $router->url(
                                $request,
                                null,
                                null,
                                'manage',
                                null,
                                [
                                    'verb'      => 'settings',
                                    'plugin'    => $this->getName(),
                                    'category'  => 'generic'
                                ]
                            ),
                            $this->getDisplayName()
                        ),
                        __('manager.plugins.settings'),
                        null
                    ),
                ] : [],
            parent::getActions($request, $verb)
        );
    }

	/**
	 * @copydoc Plugin::manage()
	 */
    public function manage($args, $request) 
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->registerPlugin('function', 'plugin_url', [$this, 'smartyPluginUrl']);
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

			$request = Application::get()->getRequest();
			$context = $request->getContext();

			$objectForReviewDao = new ObjectForReviewDAO();
			DAORegistry::registerDAO('ObjectForReviewDAO', $objectForReviewDao);

            Hook::add('TemplateManager::display', $this->addToSubmissionWizardSteps(...));
            Hook::add('Template::SubmissionWizard::Section', $this->addToSubmissionWizardTemplate(...));
            Hook::add('Template::SubmissionWizard::Section::Review', $this->addToSubmissionWizardReviewTemplate(...));

			#Hook::Add('Template::Workflow::Publication', array($this, 'addToPublicationForms'));

			Hook::Add('LoadComponentHandler', array($this, 'setupGridHandler'));
			Hook::Add('TemplateManager::display',array($this, 'addJs'));

			Hook::Add('Template::Settings::website', array($this, 'callbackShowWebsiteSettingsTabs'));

			// if list display is enabled
			if ($this->getSetting($context->getId(), 'displayAsList')) {
				Hook::Add('Templates::Article::Main', array($this, 'addSubmissionDisplay'));
				Hook::Add('Templates::Catalog::Book::Main', array($this, 'addSubmissionDisplay'));
			}

			// Handler for public objects for review page
			Hook::Add('LoadHandler', array($this, 'loadPageHandler'));
			Hook::Add('NavigationMenus::itemTypes', array($this, 'addMenuItemTypes'));
			Hook::Add('NavigationMenus::displaySettings', array($this, 'setMenuItemDisplayDetails'));
			Hook::Add('SitemapHandler::createJournalSitemap', array($this, 'addSitemapURLs'));

			Hook::add('Mailer::Mailables', [$this, 'addMailable']);



		}
		return $success;
	}

	/**
	 * Extend the website settings tabs to include objects for review tab
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$templateMgr = $args[1];
		$output =& $args[2];
		$output .= $templateMgr->fetch($this->getTemplateResource('objectsForReviewTab.tpl'));
		return false;
	}

	/**
	 * Permit requests to the ObjectsForReview grid handler
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		$componentInstance =& $params[2];

		if ($component === 'plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridHandler') {
			$componentInstance = new controllers\grid\ObjectsForReviewGridHandler($this);
			return true;
		}

		if ($component === 'plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewManagementGridHandler') {
			$componentInstance = new controllers\grid\ObjectsForReviewManagementGridHandler($this);
			return true;
		}
		
		return false;
	}

    /**
     * Inject a object for review section into the submission wizard steps UI.
     *
     * @param string $hookName
     * @param array $params
     *
     * @return bool Hook return value
     */
    function addToSubmissionWizardSteps($hookName, $params) {
        $request = Application::get()->getRequest();

        if ($request->getRequestedPage() !== 'submission') {
            return;
        }

        if ($request->getRequestedOp() === 'saved') {
            return;
        }

        $submission = $request
            ->getRouter()
            ->getHandler()
            ->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        if (!$submission || !$submission->getData('submissionProgress')) {
            return;
        }

        /** @var ObjectForReviewDAO $objectForReviewDao */
        $objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReviewResult = $objectForReviewDao->getBySubmissionId($submission->getId());

        $objectsForReview = [];
        while ($objectForReview = $objectForReviewResult->next()) {
            $objectsForReview[] = $this->getObjectForReviewData($objectForReview);
        }

        /** @var TemplateManager $templateMgr */
        $templateMgr = $params[0];

        $steps = $templateMgr->getState('steps');
        $steps = array_map(function($step) {
            if ($step['id'] === 'editors') {
                $step['sections'][] = [
                    'id' => 'objectsForReview',
                    'name' => __('plugins.generic.objectsForReview.submissionWizard.name'),
                    'type' => SubmissionHandler::SECTION_TYPE_TEMPLATE,
                ];
            }
            return $step;
        }, $steps);

        $templateMgr->setState([
            'objectsForReview' => $objectsForReview,
            'steps' => $steps,
        ]);

        $templateMgr->addJavaScript(
            'plugin-objectForReview-submission-wizard',
            $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js/SubmissionWizard.js',
            [
                'contexts' => 'backend',
                'priority' => TemplateManager::STYLE_SEQUENCE_LATE,
            ]
        );

        return false;
    }

    /**
     * Render the object for review section content inside the submission wizard.
     *
     * @param string $hookName
     * @param array $params
     *
     * @return bool Hook return value
     */
    function addToSubmissionWizardTemplate($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2];

        $output .= sprintf(
            '<template v-else-if="section.id === \'objectsForReview\'">%s</template>',
            $smarty->fetch($this->getTemplateResource('objectsForReviewGrid.tpl'))
        );

        return false;
    }

    /**
     * Format object for review entity as an associative array for frontend or API use.
     *
     * @param ObjectForReview $objectForReview
     *
     * @return array
     */
	public function getObjectForReviewData(ObjectForReview $objectForReview): array
	{
		return [
			'id' => $objectForReview->getId(),
			'identifier' => $objectForReview->getIdentifier(),
			'identifierType' => $objectForReview->getIdentifierType(),
			'resourceType' => $objectForReview->getResourceType(),
			'authors' => $objectForReview->getAuthors(),
			'title' => $objectForReview->getTitle(),
			'publisher' => $objectForReview->getPublisher(),
			'year' => $objectForReview->getYear(),
		];
	}

    /**
     * Add a review panel for the object for review data in the final step of the wizard.
     *
     * @param string $hookName
     * @param array $params
     *
     * @return bool Hook return value
     */
    function addToSubmissionWizardReviewTemplate($hookName, $params) {
        $submission = $params[0]['submission']; /** @var Submission $submission */
        $step = $params[0]['step']; /** @var string $step */
        $templateMgr = $params[1]; /** @var TemplateManager $templateMgr */
        $output =& $params[2];

        if ($step === 'editors') {
            $output .= $templateMgr->fetch($this->getTemplateResource('reviewObjectsForReview.tpl'));
        }

        return false;
    }

	/**
	 * Insert ObjectsForReview grid in the publication tabs
	 */
	function addToPublicationForms($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$submission = $smarty->getTemplateVars('submission');
		$smarty->assign([
			'submissionId' => $submission->getId(),
		]);

		$output .= sprintf(
			'<tab id="objectsForReviewGridInWorkflow" label="%s">%s</tab>',
			__('plugins.generic.objectsForReview.management.gridTitle'),
			$smarty->fetch($this->getTemplateResource('metadataForm.tpl'))
		);

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

		$submission = $templateMgr->getTemplateVars('monograph') ? $templateMgr->getTemplateVars('monograph') : $templateMgr->getTemplateVars('article');

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
		// NOTE Not working in 3.2, need to rewrite this
		$submission =& $params[0];

		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getBySubmissionId($submission->getId());

		if ($objectsForReview){
			$objects = array();
			while ($objectForReview = $objectsForReview->next()) {
				$objects[] = $objectForReview->getDescription();
			}

			if ($objects){
				$publication->setSubtitle(implode(" ▪ ", $objects), $publication->getLocale());
			}
		}

		return false;
	}

	/**
	 * Add custom js for backend and frontend
	 */
	function addJs($hookName, $params) {
		$templateMgr = $params[0];
		$template =& $params[1];
		$request = Application::get()->getRequest();

		$gridHandlerJs = $this->getJavaScriptURL($request, false) . DIRECTORY_SEPARATOR . 'ObjectsForReviewGridHandler.js';
		$templateMgr->addJavaScript(
			'ObjectsForReviewGridHandlerJs',
			$gridHandlerJs,
			array('contexts' => 'backend')
		);
		$templateMgr->addStylesheet(
			'ObjectsForReviewGridHandlerStyles',
			'#objectsForReviewGridInWorkflow { margin-top: 32px; }',
			[
				'inline' => true,
				'contexts' => 'backend',
			]
		);

		if (strpos($template, 'frontend/pages/forReview.tpl')) {
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
		$request = Application::get()->getRequest();
		return $request->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath() . DIRECTORY_SEPARATOR . 'js';
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
			$handler =& $args[3];
            $handler = new ObjectsForReviewHandler($this);
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
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : Application::CONTEXT_ID_NONE;
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
			$request = Application::get()->getRequest();
			$context = $request->getContext();
			if ($context){
				$dispatcher = $request->getDispatcher();
				$navigationMenuItem->setUrl($dispatcher->url(
					$request,
					Application::ROUTE_PAGE,
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
		$request = Application::get()->getRequest();
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
     * Add mailable to the list of mailables in the application
     */
    public function addMailable(string $hookName, array $args): void
    {
        $args[0]->push(ObjectsForReviewNew::class);
		$args[0]->push(ObjectsForReviewCancel::class);
    }
	
	/**
	 * Send mail to editor when object is reserved or cancelled
	 *
	 * @param User $user
	 * @param $object
	 * @param $template Send either the reserve or cancel mail
	 */
	public function notifyEditor($user, $objectDescription, $mailTemplate) {

		$request = Application::get()->getRequest();
		$context = $request->getContext();

		// This should only ever happen within a context, never site-wide.
		assert($context != null);
		$contextId = $context->getId();

        $emailParams = [
            'objectDescription' => htmlspecialchars($objectDescription),
            'senderUsername' => htmlspecialchars($user->getFullName()),
			'senderEmail' => htmlspecialchars($user->getEmail())		
        ];

		if ($mailTemplate == 'OFR_NEW_RESERVATION'){
			$mailable = new ObjectsForReviewNew($context, $emailParams);
		} elseif ($mailTemplate == 'OFR_CANCEL_RESERVATION'){
			$mailable = new ObjectsForReviewCancel($context, $emailParams);
		} 

		if ($this->getSetting($contextId, 'ofrNotifyEmail')){
			$mailTo = $this->getSetting($contextId, 'ofrNotifyEmail');
		} else{
			$mailTo = $context->getData('contactEmail');
		}

		$template = Repo::emailTemplate()->getByKey($context->getId(), $mailable::getEmailTemplateKey());
		$locale = $context->getPrimaryLocale();
		$mailable
			->sender($user)
			->to($mailTo)
			->subject($template->getLocalizedData('subject', $locale))
			->body($template->getLocalizedData('body', $locale));

		Mail::send($mailable);

	}
}
?>
