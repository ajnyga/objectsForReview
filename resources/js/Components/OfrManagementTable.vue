<template>
  <div class="ofr-management">
    <PkpSpinner v-if="!data" />
    <OfrObjectsTable
      v-else
      :items="data.items"
      :can-edit="true"
      :show-reserved-by="true"
      :empty-text="t('plugins.generic.objectsForReview.management.noneAvailable')"
      @edit="openEdit"
      @delete="confirmDelete"
    >
      <template #label>
        <h3 class="ofr-management__heading">
          {{ t("plugins.generic.objectsForReview.management.gridTitle") }}
        </h3>
      </template>
      <template #top-controls>
        <PkpButton @click="openAdd">
          {{ t("plugins.generic.objectsForReview.addAvailableObjectForReview") }}
        </PkpButton>
      </template>
    </OfrObjectsTable>
  </div>
</template>

<script setup>
import OfrObjectsTable from "./OfrObjectsTable.vue";
import OfrObjectFormModal from "./OfrObjectFormModal.vue";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;
const { useModal } = pkp.modules.useModal;

const { t } = useLocalize();
const { openSideModal, closeSideModal, openDialog } = useModal();

const basePath = "objectsForReview";
const { apiUrl: listUrl } = useUrl(basePath);
const { data, fetch: fetchObjects } = useFetch(listUrl);

function openForm({ title, path, method, objectForReview = null }) {
  const { apiUrl } = useUrl(path);
  openSideModal(OfrObjectFormModal, {
    title,
    action: apiUrl.value,
    method,
    objectForReview,
    onSaved: async () => {
      closeSideModal(OfrObjectFormModal);
      await fetchObjects();
    },
  });
}

function openAdd() {
  openForm({
    title: t("plugins.generic.objectsForReview.addAvailableObjectForReview"),
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

function confirmDelete(item) {
  openDialog({
    title: t("common.delete"),
    message: t("plugins.generic.objectsForReview.delete.confirm"),
    actions: [
      {
        label: t("common.delete"),
        isWarnable: true,
        callback: async (close) => {
          const { apiUrl } = useUrl(`${basePath}/${item.id}`);
          const { fetch: fetchDelete } = useFetch(apiUrl, { method: "DELETE" });
          await fetchDelete();
          close();
          await fetchObjects();
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

fetchObjects();
</script>

<style scoped>
.ofr-management__heading {
  font: var(--font-xl-bold);
  color: var(--text-color-heading);
  margin: 0;
}
</style>
