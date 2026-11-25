import OfrManagementTable from "./Components/OfrManagementTable.vue";
import OfrSubmissionObjectsManager from "./Components/OfrSubmissionObjectsManager.vue";
import OfrWizardReview from "./Components/OfrWizardReview.vue";

pkp.registry.registerComponent("OfrManagementTable", OfrManagementTable);
pkp.registry.registerComponent(
  "OfrSubmissionObjectsManager",
  OfrSubmissionObjectsManager,
);
pkp.registry.registerComponent("OfrWizardReview", OfrWizardReview);

const MENU_ITEM = "objectsForReview";

// Workflow: add "Objects for Review" under the Publication menu
pkp.registry.storeExtend("workflow", (piniaContext) => {
  const workflowStore = piniaContext.store;
  const { useLocalize } = pkp.modules.useLocalize;
  const { t } = useLocalize();

  workflowStore.extender.extendFn("getMenuItems", (menuItems) => {
    const label = t("plugins.generic.objectsForReview.management.gridTitle");

    return menuItems.map((menuItem) => {
      if (menuItem.key !== "publication" || !Array.isArray(menuItem.items)) {
        return menuItem;
      }
      return {
        ...menuItem,
        items: [
          ...menuItem.items,
          {
            key: `publication_${MENU_ITEM}`,
            label,
            state: {
              primaryMenuItem: "publication",
              secondaryMenuItem: MENU_ITEM,
              title: `${t("semicolon", { label: t("submission.publication") })} ${label}`,
            },
          },
        ],
      };
    });
  });

  workflowStore.extender.extendFn("getPrimaryItems", (primaryItems, args) => {
    if (
      args?.selectedMenuState?.primaryMenuItem === "publication" &&
      args?.selectedMenuState?.secondaryMenuItem === MENU_ITEM
    ) {
      return [
        {
          component: "OfrSubmissionObjectsManager",
          props: { submissionId: args.submission.id },
        },
      ];
    }
    return primaryItems;
  });
});
