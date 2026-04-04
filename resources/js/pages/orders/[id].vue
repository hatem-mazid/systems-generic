<template>
    <div class="p-6 text-surface-800 dark:text-surface-100">
        <p class="text-lg font-semibold">
            {{ $t("Orders.PlaceholderTitle", { id: orderId }) }}
        </p>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const orderId = computed(() => route.params.id);
</script>
