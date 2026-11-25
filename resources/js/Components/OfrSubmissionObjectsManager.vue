<template>
  <div class="ofr-manager">
    <PkpSpinner v-if="!data" />
    <template v-else>
      <OfrObjectsTable
        :items="data.items"
        :can-edit="data.canEdit"
        :delete-label="t('common.remove')"
        :empty-text="t('plugins.generic.objectsForReview.noneCreated')"
        @edit="openEdit"
        @delete="confirmRemove"
      >
        <template #label>
          <h3 class="ofr-manager__heading">
            {{ t("plugins.generic.objectsForReview.objectsForReviewTitle") }}
          </h3>
        </template>
        <template v-if="data.canEdit && !data.onlyReserved" #top-controls>
          <PkpButton @click="openAdd">
            {{ t("plugins.generic.objectsForReview.addObjectForReview") }}
          </PkpButton>
        </template>
      </OfrObjectsTable>

      <div v-if="data.canEdit && data.reserved.length" class="ofr-reserved">
        <h4 class="ofr-reserved__heading">
          {{ t("plugins.generic.objectsForReview.reservedObjects") }}
        </h4>
        <p class="ofr-reserved__description">
          {{ t("plugins.generic.objectsForReview.reservedObjects.description") }}
        </p>
        <ul class="ofr-reserved__list">
          <li
            v-for="item in data.reserved"
            :key="item.id"
            class="ofr-reserved__item"
          >
            <span
              class="ofr-reserved__text"
              v-strip-unsafe-html="item.description"
            />
            <PkpButton :is-disabled="isAttaching" @click="attach(item)">
              {{
                t(
                  "plugins.generic.objectsForReview.objectsForReviewData.addReservedObject",
                )
              }}
            </PkpButton>
          </li>
        </ul>
      </div>
      <p
        v-else-if="data.canEdit && data.onlyReserved"
        class="ofr-reserved__description"
      >
        {{ t("plugins.generic.objectsForReview.noReservedObjects") }}
      </p>
    </template>
  </div>
</template>

<script setup>
import { ref } from "vue";
import OfrObjectsTable from "./OfrObjectsTable.vue";
import OfrObjectFormModal from "./OfrObjectFormModal.vue";
import { OBJECTS_CHANGED_EVENT } from "../constants.js";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;
const { useModal } = pkp.modules.useModal;

const { t } = useLocalize();
const { openSideModal, closeSideModal, openDialog } = useModal();

const props = defineProps({
  submissionId: { type: [Number, String], required: true },
});

const basePath = `objectsForReview/submissions/${props.submissionId}`;
const { apiUrl: listUrl } = useUrl(basePath);
const { data, fetch: fetchObjects } = useFetch(listUrl);

const isAttaching = ref(false);

async function refresh() {
  await fetchObjects();
  pkp.eventBus.$emit(OBJECTS_CHANGED_EVENT, Number(props.submissionId));
}

function openForm({ title, path, method, objectForReview = null }) {
  const { apiUrl } = useUrl(path);
  openSideModal(OfrObjectFormModal, {
    title,
    action: apiUrl.value,
    method,
    objectForReview,
    onSaved: async () => {
      closeSideModal(OfrObjectFormModal);
      await refresh();
    },
  });
}

function openAdd() {
  openForm({
    title: t("plugins.generic.objectsForReview.addObjectForReview"),
    path: basePath,
    method: "POST",
  });
}

function openEdit(item) {
  openForm({
    title: t("plugins.generic.objectsForReview.editObjectForReview"),
    path: `${basePath}/${item.id}`,
    method: "PUT",
    objectForReview: item,
  });
}

function confirmRemove(item) {
  openDialog({
    title: t("common.remove"),
    message: t("plugins.generic.objectsForReview.remove.confirm"),
    actions: [
      {
        label: t("common.remove"),
        isWarnable: true,
        callback: async (close) => {
          const { apiUrl } = useUrl(`${basePath}/${item.id}`);
          const { fetch: fetchRemove } = useFetch(apiUrl, { method: "DELETE" });
          await fetchRemove();
          close();
          await refresh();
        },
      },
      {
        label: t("common.cancel"),
        callback: (close) => close(),
      },
    ],
    modalStyle: "negative",
  });
}

async function attach(item) {
  isAttaching.value = true;
  const { apiUrl } = useUrl(`${basePath}/attach/${item.id}`);
  const { fetch: fetchAttach } = useFetch(apiUrl, { method: "POST" });
  await fetchAttach();
  await refresh();
  isAttaching.value = false;
}

fetchObjects();
</script>

<style scoped>
.ofr-manager__heading {
  font: var(--font-xl-bold);
  color: var(--text-color-heading);
  margin: 0;
}

.ofr-reserved {
  margin-top: var(--spacing-6);
}

.ofr-reserved__heading {
  font: var(--font-lg-bold);
  color: var(--text-color-heading);
  margin: 0 0 var(--spacing-2) 0;
}

.ofr-reserved__description {
  font: var(--font-base-normal);
  color: var(--text-color-secondary);
  margin: 0 0 var(--spacing-3) 0;
}

.ofr-reserved__list {
  list-style: none;
  margin: 0;
  padding: 0;
  border: 1px solid var(--border-color-light);
}

.ofr-reserved__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-4);
  padding: var(--spacing-3);
}

.ofr-reserved__item + .ofr-reserved__item {
  border-top: 1px solid var(--border-color-light);
}

.ofr-reserved__text {
  font: var(--font-base-normal);
  color: var(--text-color-default);
}
</style>
