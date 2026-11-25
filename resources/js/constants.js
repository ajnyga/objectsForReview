/**
 * Event emitted on pkp.eventBus after the objects of a submission change,
 * with the submission ID as payload. The wizard review step listens to it.
 */
export const OBJECTS_CHANGED_EVENT = "objectsForReview:changed";
