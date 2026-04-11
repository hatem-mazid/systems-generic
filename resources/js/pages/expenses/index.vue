<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Sidebar.Expenses") }}
            </h1>

            <Button
                v-if="canCreate"
                to="/expenses/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add Expense')"
            >
                <template #icon>
                    <AppIcon name="pi pi-plus" />
                </template>
            </Button>
        </div>

        <div
            class="mt-6 rounded-2xl border border-surface-200/80 bg-surface-0 p-5 shadow-sm sm:p-6 dark:border-surface-700 dark:bg-surface-900"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:gap-4"
            >
                <div class="min-w-0 sm:min-w-[12rem]">
                    <label
                        class="mb-2 block text-sm font-medium text-surface-700 dark:text-surface-200"
                        for="exp-filter-type"
                    >
                        {{ $t("ExpensesList.FilterType") }}
                    </label>
                    <Select
                        id="exp-filter-type"
                        v-model="filters.type"
                        :options="typeFilterOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                        fluid
                        size="large"
                        @update:model-value="() => fetchExpenses(1)"
                    />
                </div>
                <div class="min-w-0 sm:min-w-[11rem]">
                    <label
                        class="mb-2 block text-sm font-medium text-surface-700 dark:text-surface-200"
                        for="exp-filter-from"
                    >
                        {{ $t("ExpensesList.DateFrom") }}
                    </label>
                    <DatePicker
                        id="exp-filter-from"
                        v-model="filters.dateFrom"
                        date-format="yy-mm-dd"
                        show-icon
                        fluid
                        size="large"
                        show-clear
                        @update:model-value="scheduleFetch"
                    />
                </div>
                <div class="min-w-0 sm:min-w-[11rem]">
                    <label
                        class="mb-2 block text-sm font-medium text-surface-700 dark:text-surface-200"
                        for="exp-filter-to"
                    >
                        {{ $t("ExpensesList.DateTo") }}
                    </label>
                    <DatePicker
                        id="exp-filter-to"
                        v-model="filters.dateTo"
                        date-format="yy-mm-dd"
                        show-icon
                        fluid
                        size="large"
                        show-clear
                        @update:model-value="scheduleFetch"
                    />
                </div>
                <div class="flex gap-2 pb-0.5">
                    <Button
                        type="button"
                        :label="$t('ExpensesList.Apply')"
                        size="large"
                        @click="fetchExpenses(1)"
                    />
                </div>
            </div>
        </div>

        <div class="mt-8 min-w-0">
            <DataTable
                :value="expenses"
                :loading="isLoading"
                striped-rows
                table-style="min-width: 50rem"
            >
                <Column
                    field="expense_date"
                    :header="$t('ExpensesList.ColumnDate')"
                />
                <Column :header="$t('ExpensesList.ColumnDescription')">
                    <template #body="{ data }">
                        <span class="line-clamp-2 max-w-xs">{{
                            data.description
                        }}</span>
                    </template>
                </Column>
                <Column :header="$t('ExpensesList.ColumnType')">
                    <template #body="{ data }">
                        {{ $t(expenseTypeLabelKey(data.type)) }}
                    </template>
                </Column>
                <Column :header="$t('ExpensesList.ColumnAmount')">
                    <template #body="{ data }">
                        {{ formatCurrency(data.amount) }}
                    </template>
                </Column>
                <Column :header="$t('ExpensesList.ColumnExpenseBy')">
                    <template #body="{ data }">
                        {{ data.expense_by?.name ?? "—" }}
                    </template>
                </Column>
                <Column :header="$t('ExpensesList.ColumnCreatedBy')">
                    <template #body="{ data }">
                        {{ data.created_by?.name ?? "—" }}
                    </template>
                </Column>
                <Column
                    v-if="canEdit"
                    :header="$t('OrdersList.ColumnActions')"
                    style="width: 120px"
                >
                    <template #body="{ data }">
                        <Button
                            as="router-link"
                            :to="`/expenses/${data.id}`"
                            severity="secondary"
                            size="small"
                            :label="$t('Edit')"
                        />
                    </template>
                </Column>
            </DataTable>

            <Paginator
                class="pagination-touch mt-6 bg-transparent"
                :rows="paginator.per_page"
                :total-records="paginator.total"
                :first="(paginator.current_page - 1) * paginator.per_page"
                @page="onPageChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import DatePicker from "primevue/datepicker";
import Paginator from "primevue/paginator";
import Select from "primevue/select";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import {
    expensesService,
    type Expense,
} from "../../apis/services/expenses/expenses.apis";
import { expenseTypeLabelKey } from "../../constants/expenseTypes";
import { formatCurrency } from "../../utils/formatCurrency";
import { useUserStore } from "../../stores/user";

const { t } = useI18n();
const { hasPermission } = useUserStore();
const canCreate = hasPermission("expenses create");
const canEdit = hasPermission("expenses edit");

const isLoading = ref(true);
const expenses = ref<Expense[]>([]);
const paginator = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
});

const filters = ref<{
    type: string;
    dateFrom: Date | null;
    dateTo: Date | null;
}>({
    type: "",
    dateFrom: null,
    dateTo: null,
});

const typeFilterOptions = computed(() => [
    { value: "", label: t("ExpensesList.FilterTypeAll") },
    { value: "rent", label: t(expenseTypeLabelKey("rent")) },
    { value: "supplies", label: t(expenseTypeLabelKey("supplies")) },
    { value: "payroll", label: t(expenseTypeLabelKey("payroll")) },
    { value: "utilities", label: t(expenseTypeLabelKey("utilities")) },
    { value: "marketing", label: t(expenseTypeLabelKey("marketing")) },
    { value: "other", label: t(expenseTypeLabelKey("other")) },
]);

function toYmd(d: Date | null): string | null {
    if (!d) {
        return null;
    }
    const dt = d instanceof Date ? d : new Date(d);
    if (Number.isNaN(dt.getTime())) {
        return null;
    }
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, "0");
    const day = String(dt.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
}

let fetchTimer: ReturnType<typeof setTimeout> | null = null;
function scheduleFetch() {
    if (fetchTimer) {
        clearTimeout(fetchTimer);
    }
    fetchTimer = setTimeout(() => fetchExpenses(1), 300);
}

function buildParams(page: number) {
    return {
        page,
        per_page: paginator.value.per_page,
        type: filters.value.type ? filters.value.type : undefined,
        date_from: toYmd(filters.value.dateFrom) ?? undefined,
        date_to: toYmd(filters.value.dateTo) ?? undefined,
    };
}

const fetchExpenses = (page = 1, showLoading = true) => {
    if (showLoading) {
        isLoading.value = true;
    }
    expensesService
        .getExpenses(buildParams(page))
        .then((response) => {
            const data = response.data;
            expenses.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((e) => console.error(e))
        .finally(() => {
            if (showLoading) {
                isLoading.value = false;
            }
        });
};

const onPageChange = (event: { page: number; rows: number }) => {
    paginator.value.per_page = event.rows;
    fetchExpenses(event.page + 1);
};

onMounted(() => {
    fetchExpenses();
});
</script>
