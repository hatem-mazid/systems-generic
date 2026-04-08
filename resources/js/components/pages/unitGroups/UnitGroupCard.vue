<template>
    <section
        class="rounded-xl border flex flex-col gap-5 border-surface-200 p-4 dark:border-surface-700"
    >
        <UnitGroupPanelContent
            :unit-group="unitGroup"
            :management-mode="managementMode"
            :view-mode="viewMode"
            @deleted="$emit('deleted')"
        />
    </section>
</template>

<script setup>
import UnitGroupPanelContent from "./UnitGroupPanelContent.vue";

defineEmits(["deleted"]);

defineProps({
    unitGroup: {
        type: Object,
        required: true,
    },
    managementMode: {
        type: Boolean,
        default: false,
    },
    viewMode: {
        type: String,
        default: "comfortable",
    },
});
</script>
