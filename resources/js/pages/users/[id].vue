<template>
    <div class="touch-manipulation">
        <UserForm :userId="userId" />
    </div>
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import UserForm from "../../components/pages/users/userForm.vue";

const route = useRoute();

const rawId = route?.params?.id;
const userId: string | undefined =
    rawId === undefined ? undefined : Array.isArray(rawId) ? rawId[0] : rawId;
</script>
