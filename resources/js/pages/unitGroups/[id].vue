<template>
    <div class="touch-manipulation">
        <UnitGroupsForm :unit-group-id="unitGroupId" />
    </div>
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import UnitGroupsForm from "../../components/pages/unitGroups/UnitGroupsForm.vue";

const route = useRoute();

const rawId = route?.params?.id;
const unitGroupId: string | undefined =
    rawId === undefined ? undefined : Array.isArray(rawId) ? rawId[0] : rawId;
</script>
