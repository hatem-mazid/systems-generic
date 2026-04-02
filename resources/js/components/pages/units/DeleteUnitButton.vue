<template>
    <Button
        size="large"
        rounded
        outlined
        severity="danger"
        icon="pi pi-trash"
        :loading="deleting"
        :disabled="deleting || !unit?.id"
        :aria-label="$t('Delete')"
        @click="onDelete"
    />
</template>

<script setup>
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { unitsService } from "../../../apis/services/units/units.apis";

const props = defineProps({
    unit: {
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
        message: t("Units.DeleteMessage", {
            name: props.unit?.name || t("Units.ThisUnit"),
        }),
        header: t("Units.DeleteTitle"),
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
            unitsService
                .deleteUnit(String(props.unit.id))
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("Units.DeleteSuccess"),
                        life: 3000,
                    });
                    emit("deleted");
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("Units.DeleteError"),
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
