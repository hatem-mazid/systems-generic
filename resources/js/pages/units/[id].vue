<template>
    <UnitForm :unit-id="unitId" />
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import UnitForm from "../../components/pages/units/UnitForm.vue";

const route = useRoute();
const rawId = route?.params?.id;
const unitId: string | undefined =
    rawId === undefined ? undefined : Array.isArray(rawId) ? rawId[0] : rawId;
</script>
