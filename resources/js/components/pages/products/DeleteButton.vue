<template>
    <Button
        size="large"
        rounded
        outlined
        severity="danger"
        :loading="deleting"
        :disabled="deleting || !product?.id"
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
import { productsService } from "../../../apis/services/products/products.apis.ts";

const props = defineProps({
    product: {
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
        message: t("ProductsList.DeleteMessage", {
            name: props.product?.name,
        }),
        header: t("ProductsList.DeleteTitle"),
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
            productsService
                .deleteProduct(String(props.product.id))
                .then(() => {
                    toast.add({
                        severity: "success",
                        summary: t("ProductsList.DeleteSuccess"),
                        life: 3000,
                    });
                    emit("deleted");
                })
                .catch(() => {
                    toast.add({
                        severity: "error",
                        summary: t("ProductsList.DeleteError"),
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
