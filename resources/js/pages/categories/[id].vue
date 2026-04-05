<template>
    <div class="touch-manipulation">
        <CategoryForm :category-id="categoryId" />
    </div>
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import CategoryForm from "../../components/pages/categories/CategoryForm.vue";

const route = useRoute();

const rawId = route?.params?.id;
const categoryId: string | undefined =
    rawId === undefined ? undefined : Array.isArray(rawId) ? rawId[0] : rawId;
</script>
