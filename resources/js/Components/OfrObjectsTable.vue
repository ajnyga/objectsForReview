<template>
  <PkpTable class="ofr-table">
    <template v-if="$slots.label" #label>
      <slot name="label" />
    </template>
    <template v-if="$slots.description" #description>
      <slot name="description" />
    </template>
    <template v-if="$slots['top-controls']" #top-controls>
      <slot name="top-controls" />
    </template>
    <PkpTableHeader>
      <PkpTableColumn>
        {{ t("plugins.generic.objectsForReview.itemDescription") }}
      </PkpTableColumn>
      <PkpTableColumn>
        {{ t("plugins.generic.objectsForReview.itemIdentifier") }}
      </PkpTableColumn>
      <PkpTableColumn v-if="showReservedBy">
        {{ t("plugins.generic.objectsForReview.userName") }}
      </PkpTableColumn>
      <PkpTableColumn v-if="canEdit">
        <span class="sr-only">{{ t("common.moreActions") }}</span>
      </PkpTableColumn>
    </PkpTableHeader>
    <PkpTableBody :empty-text="emptyText">
      <PkpTableRow v-for="item in items" :key="item.id">
        <PkpTableCell :is-row-header="true">
          <span
            class="ofr-table__description"
            v-strip-unsafe-html="item.description"
          />
        </PkpTableCell>
        <PkpTableCell>
          <span class="ofr-table__identifier">
            {{ item.identifierType }}: {{ item.identifier }}
          </span>
        </PkpTableCell>
        <PkpTableCell v-if="showReservedBy">
          <span v-if="item.reservedBy">
            {{ item.reservedBy.fullName }} ({{ item.reservedBy.email }})
          </span>
          <span v-else>—</span>
        </PkpTableCell>
        <PkpTableCell v-if="canEdit">
          <DropdownActions
            :actions="getActions(item)"
            :label="t('common.moreActions')"
            button-variant="ellipsis"
            direction="left"
            @action="(actionName) => emit(actionName, item)"
          />
        </PkpTableCell>
      </PkpTableRow>
    </PkpTableBody>
  </PkpTable>
</template>

<script setup>
const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

const props = defineProps({
  /** Objects for review, in the shape returned by the plugin's API */
  items: { type: Array, required: true },
  /** Show the edit and delete actions */
  canEdit: { type: Boolean, default: false },
  /** Show who has reserved each object */
  showReservedBy: { type: Boolean, default: false },
  /** Label of the delete action, e.g. Delete or Remove */
  deleteLabel: { type: String, default: "" },
  /** Text shown when there are no items */
  emptyText: { type: String, default: "" },
});

const emit = defineEmits(["edit", "delete"]);

function getActions() {
  return [
    { label: t("common.edit"), name: "edit", icon: "Edit" },
    {
      label: props.deleteLabel || t("common.delete"),
      name: "delete",
      icon: "Cancel",
      isWarnable: true,
    },
  ];
}
</script>

<style scoped>
.ofr-table__description,
.ofr-table__identifier {
  font: var(--font-base-normal);
  color: var(--text-color-default);
}

.ofr-table__identifier {
  word-break: break-all;
}
</style>
