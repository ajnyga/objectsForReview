/**
 * @file plugins/generic/objectsForReview/js/SubmissionWizard.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionWizard
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Add information about objects for review to the root component of the submission
 *   wizard UI and keep the data in sync as objects are added, edited and removed
 */
(function() {
    if (typeof pkp === 'undefined' || typeof pkp.eventBus === 'undefined') {
        return;
    }

    var root;
    pkp.eventBus.$on('root:mounted', function(id, component) {
        root = component;
    });
    pkp.eventBus.$on('plugin:objectsForReview:added', function(data) {
        root.objectsForReview.push(data);
    });
    pkp.eventBus.$on('plugin:objectsForReview:edited', function(data) {
        root.objectsForReview = root.objectsForReview.map(function(objectForReview) {
            if (data.id === objectForReview.id) {
                return data;
            }
            return objectForReview;
        });
    });
    pkp.eventBus.$on('plugin:objectsForReview:deleted', function(data) {
        root.objectsForReview = root.objectsForReview.filter(function(objectForReview) {
            return data.id !== objectForReview.id;
        });
    });
}());