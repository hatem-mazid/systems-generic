<template>
    <Button
        size="large"
        rounded
        outlined
        severity="danger"
        icon="pi pi-trash"
        :loading="deleting"
        :disabled="deleting || !category?.id"
        :aria-label="$t('Delete')"
        @click="onDelete"
    />
</template>

<script setup>
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { categoriesService } from "../../../apis/services/categories/categories.apis";

const props = defineProps({
    category: {
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
        message: t("CategoriesList.DeleteMessage", {
            name: props.category?.name,
        }),
        header: t("CategoriesList.DeleteTitle"),
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
            categoriesService
                .deleteCategory(String(props.category.id))
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("CategoriesList.DeleteSuccess"),
                        life: 3000,
                    });
                    emit("deleted");
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("CategoriesList.DeleteError"),
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
