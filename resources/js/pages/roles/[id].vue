<template>
    <div class="touch-manipulation">
        <RoleForm :role-id="roleId" />
    </div>
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import RoleForm from "../../components/pages/roles/RoleForm.vue";

const route = useRoute();

const rawId = route?.params?.id;
const roleId: string | undefined =
    rawId === undefined ? undefined : Array.isArray(rawId) ? rawId[0] : rawId;
</script>
