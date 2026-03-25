<template>
    <div class="">
    <component :is="layout">
        <Toast />
        <ConfirmDialog />

        <div v-if="isLoading" class="grid place-content-center my-10">
            <loading />
        </div>
        <RouterView v-else></RouterView>
    </component>
</div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import DefaultLayout from './layout/default.vue';
import { ref, computed  } from 'vue';
import { useRoute } from 'vue-router'; // import route

const { locale } = useI18n();
const route = useRoute();
const layout = computed(() => route.meta?.layout ?? DefaultLayout);

const isLoading = ref(false);
</script>

