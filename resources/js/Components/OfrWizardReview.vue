<template>
  <div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
      <h3 :id="headingId">
        {{ t("plugins.generic.objectsForReview.submissionWizard.name") }}
      </h3>
    </div>
    <div class="submissionWizard__reviewPanel__body">
      <PkpSpinner v-if="!data" />
      <p v-else-if="!data.items.length" class="ofr-review__empty">
        {{ t("plugins.generic.objectsForReview.noneCreated") }}
      </p>
      <ul v-else class="ofr-review__list" :aria-labelledby="headingId">
        <li v-for="item in data.items" :key="item.id" class="ofr-review__item">
          <span v-strip-unsafe-html="item.description" />
          <span class="ofr-review__identifier">
            {{ item.identifierType }}: {{ item.identifier }}
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount } from "vue";
import { OBJECTS_CHANGED_EVENT } from "../constants.js";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;

const { t } = useLocalize();

const props = defineProps({
  submissionId: { type: [Number, String], required: true },
});

const headingId = `ofr-review-${props.submissionId}`;

const { apiUrl } = useUrl(`objectsForReview/submissions/${props.submissionId}`);
const { data, fetch: fetchObjects } = useFetch(apiUrl);

function onObjectsChanged(submissionId) {
  if (submissionId === Number(props.submissionId)) {
    fetchObjects();
  }
}

pkp.eventBus.$on(OBJECTS_CHANGED_EVENT, onObjectsChanged);
onBeforeUnmount(() => pkp.eventBus.$off(OBJECTS_CHANGED_EVENT, onObjectsChanged));

fetchObjects();
</script>

<style scoped>
.ofr-review__list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.ofr-review__item {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-1);
  padding: var(--spacing-2) 0;
  font: var(--font-base-normal);
}

.ofr-review__item + .ofr-review__item {
  border-top: 1px solid var(--border-color-light);
}

.ofr-review__identifier,
.ofr-review__empty {
  color: var(--text-color-secondary);
}
</style>
