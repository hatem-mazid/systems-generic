<template>
	<h1>Cafe Spark 🚀</h1>
	<Button to="/" as="router-link" label="home" icon="pi pi-play" />
	<Button to="/users" as="router-link" label="users" icon="pi pi-play" />


    <button @click="locale = 'en'">EN</button>
    <button @click="locale = 'ar'">AR</button>
    {{ $t("start") }}
    <router-view />
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button';

const { locale } = useI18n();
</script>


<script setup>
</script>
