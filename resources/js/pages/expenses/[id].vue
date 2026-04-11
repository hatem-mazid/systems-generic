<template>
    <div class="touch-manipulation">
        <ExpenseForm :expense-id="expenseId" />
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";
import ExpenseForm from "../../components/pages/expenses/ExpenseForm.vue";

const route = useRoute();

const expenseId = computed(() => {
    const raw = route.params.id;
    const id = Array.isArray(raw) ? raw[0] : raw;
    return id != null ? String(id) : undefined;
});
</script>
