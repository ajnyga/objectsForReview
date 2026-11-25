<template>
  <PkpSideModalBody>
    <template #title>{{ title }}</template>
    <div class="ofr-form-modal">
      <div class="ofr-form-modal__panel">
        <PkpForm
          v-if="isReady"
          v-bind="form"
          @set="set"
          @success="onSaved"
        />
        <PkpSpinner v-else />
      </div>
    </div>
  </PkpSideModalBody>
</template>

<script setup>
import { ref } from "vue";

const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;
const { useForm } = pkp.modules.useForm;

const props = defineProps({
  /** Title of the side modal */
  title: { type: String, required: true },
  /** API URL the form is submitted to */
  action: { type: String, required: true },
  /** POST to add, PUT to edit */
  method: { type: String, default: "POST" },
  /** The object being edited, or null when adding */
  objectForReview: { type: Object, default: null },
  /** Called with the saved object when the form is submitted successfully */
  onSaved: { type: Function, required: true },
});

const { apiUrl: formUrl } = useUrl("objectsForReview/_components/objectForm");
const { data: formConfig, fetch: fetchForm } = useFetch(formUrl);
const { form, set, setValues } = useForm(formConfig);

const isReady = ref(false);

async function init() {
  await fetchForm();
  if (!form.value) {
    return;
  }
  form.value.action = props.action;
  form.value.method = props.method;

  if (props.objectForReview) {
    const o = props.objectForReview;
    setValues({
      resourceType: o.resourceType,
      identifierType: o.identifierType,
      identifier: o.identifier ?? "",
      authors: o.authors ?? "",
      title: o.title ?? "",
      publisher: o.publisher ?? "",
      year: o.year ?? "",
    });
  }
  isReady.value = true;
}

init();
</script>

<style scoped>
.ofr-form-modal {
  padding: var(--spacing-4);
}

.ofr-form-modal__panel {
  background-color: var(--background-color-secondary);
  padding: var(--spacing-4);
}
</style>
