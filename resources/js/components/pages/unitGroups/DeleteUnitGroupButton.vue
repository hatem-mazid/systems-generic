<template>
    <Button
        size="large"
        rounded
        outlined
        severity="danger"
        :loading="deleting"
        :disabled="deleting || !unitGroup?.id"
        :aria-label="$t('Delete')"
        @click="onDelete"
    >
        <template #icon>
            <AppIcon name="pi pi-trash" />
        </template>
    </Button>
</template>

<script setup>
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { unitGroupsService } from "../../../apis/services/unitGroups/unitGroups.apis";

const props = defineProps({
    unitGroup: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["deleted"]);

const confirm = useConfirm();
const toast = useToast();
const { t } = useI18n();
const deleting = ref(false);

const onDelete = () => {
    confirm.require({
        message: t("UnitGroupsList.DeleteMessage", {
            name: props.unitGroup?.name,
        }),
        header: t("UnitGroupsList.DeleteTitle"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: t("Cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: t("Delete"),
            severity: "danger",
        },
        accept: () => {
            deleting.value = true;
            unitGroupsService
                .deleteUnitGroup(String(props.unitGroup.id))
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("UnitGroupsList.DeleteSuccess"),
                        life: 3000,
                    });
                    emit("deleted");
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("UnitGroupsList.DeleteError"),
                        life: 4000,
                    });
                })
                .finally(() => {
                    deleting.value = false;
                });
        },
    });
};
</script>
