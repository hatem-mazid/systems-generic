<template>
    <div class="touch-manipulation p-4 text-surface-800 dark:text-surface-100 sm:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold sm:text-3xl">
                    {{ $t("ReportsExpenses.Title") }}
                </h1>
                <p class="mt-1 text-surface-600 dark:text-surface-400">
                    {{ $t("ReportsExpenses.Subtitle") }}
                </p>
            </div>
        </div>

        <div
            class="mt-6 rounded-2xl border border-surface-200/80 bg-surface-0 p-5 shadow-sm sm:p-6 dark:border-surface-700 dark:bg-surface-900"
        >
            <div
                class="flex flex-col gap-5 lg:flex-row lg:flex-wrap lg:items-end lg:gap-6"
            >
                <div class="min-w-0 sm:min-w-[11rem] lg:w-52">
                    <label
                        class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                        for="exp-report-date-from"
                    >
                        {{ $t("ReportsExpenses.DateFrom") }}
                    </label>
                    <DatePicker
                        id="exp-report-date-from"
                        v-model="filters.dateFrom"
                        date-format="yy-mm-dd"
                        show-time
                        hour-format="24"
                        show-icon
                        fluid
                        size="large"
                        :show-clear="!!filters.dateFrom"
                        @update:model-value="scheduleFetch"
                    />
                </div>
                <div class="min-w-0 sm:min-w-[11rem] lg:w-52">
                    <label
                        class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                        for="exp-report-date-to"
                    >
                        {{ $t("ReportsExpenses.DateTo") }}
                    </label>
                    <DatePicker
                        id="exp-report-date-to"
                        v-model="filters.dateTo"
                        date-format="yy-mm-dd"
                        show-time
                        hour-format="24"
                        show-icon
                        fluid
                        size="large"
                        :show-clear="!!filters.dateTo"
                        @update:model-value="scheduleFetch"
                    />
                </div>
                <div class="min-w-0 sm:min-w-[12rem] lg:w-56">
                    <label
                        class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                        for="exp-report-group-by"
                    >
                        {{ $t("ReportsExpenses.GroupBy") }}
                    </label>
                    <Select
                        id="exp-report-group-by"
                        v-model="filters.groupBy"
                        size="large"
                        :options="groupByOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                        @update:model-value="scheduleFetch"
                    />
                </div>
                <div class="flex gap-2 pb-0.5">
                    <Button
                        type="button"
                        :label="$t('ReportsExpenses.Apply')"
                        size="large"
                        @click="fetchReport"
                    />
                </div>
            </div>
        </div>

        <div v-if="loading" class="mt-10 grid place-content-center py-16">
            <ProgressSpinner
                class="!size-12"
                stroke-width="4"
                aria-label="Loading"
            />
            <span class="mt-3 text-surface-600 dark:text-surface-400">{{
                $t("ReportsExpenses.Loading")
            }}</span>
        </div>

        <div
            v-else-if="!series.length"
            class="mt-8 rounded-2xl border border-dashed border-surface-300 p-10 text-center text-surface-600 dark:border-surface-600 dark:text-surface-400"
        >
            {{ $t("ReportsExpenses.Empty") }}
        </div>

        <div v-else class="mt-8 grid gap-8 lg:grid-cols-1">
            <div
                class="rounded-2xl border border-surface-200/80 bg-surface-0 p-4 shadow-sm dark:border-surface-700 dark:bg-surface-900 sm:p-6"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-surface-800 dark:text-surface-100"
                >
                    {{ $t("ReportsExpenses.ChartAmountTitle") }}
                </h2>
                <div class="h-80 w-full min-w-0">
                    <Chart
                        type="bar"
                        :data="amountChartData"
                        :options="chartOptions"
                        class="h-full w-full"
                    />
                </div>
            </div>
            <div
                class="rounded-2xl border border-surface-200/80 bg-surface-0 p-4 shadow-sm dark:border-surface-700 dark:bg-surface-900 sm:p-6"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-surface-800 dark:text-surface-100"
                >
                    {{ $t("ReportsExpenses.ChartCountTitle") }}
                </h2>
                <div class="h-80 w-full min-w-0">
                    <Chart
                        type="line"
                        :data="countChartData"
                        :options="chartOptionsQuantity"
                        class="h-full w-full"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reportsService } from "@/apis/services/reports/reports.apis";
import Button from "primevue/button";
import Chart from "primevue/chart";
import DatePicker from "primevue/datepicker";
import ProgressSpinner from "primevue/progressspinner";
import Select from "primevue/select";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { configService } from "@/apis/services/config/config.apis";
import {
    buildFilterRangeFromConfig,
    toDateTimeParam,
} from "@/utils/configDefaults";

const { t, locale } = useI18n();
const router = useRouter();

const loading = ref(true);
const series = ref([]);
const meta = ref({ group_by: "day" });

const filters = ref({
    dateFrom: null,
    dateTo: null,
    groupBy: "day",
});

const groupByOptions = computed(() => [
    { label: t("ReportsExpenses.GroupByDay"), value: "day" },
    { label: t("ReportsExpenses.GroupByWeek"), value: "week" },
    { label: t("ReportsExpenses.GroupByMonth"), value: "month" },
]);

function formatPeriodLabel(period, groupBy) {
    if (!period) {
        return "";
    }
    const parts = period.split("-").map((p) => parseInt(p, 10));
    const [y, mo, d] = parts;
    const date = new Date(y, (mo || 1) - 1, d || 1);
    const loc = locale.value === "ar" ? "ar" : undefined;
    if (groupBy === "month") {
        return date.toLocaleDateString(loc, { year: "numeric", month: "short" });
    }
    if (groupBy === "week") {
        const end = new Date(date);
        end.setDate(end.getDate() + 6);
        const a = date.toLocaleDateString(loc, { month: "short", day: "numeric" });
        const b = end.toLocaleDateString(loc, {
            month: "short",
            day: "numeric",
        });
        return `${a} – ${b}`;
    }
    return date.toLocaleDateString(loc, {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

const chartTextColor = computed(() =>
    document.documentElement.classList.contains("dark")
        ? "#e2e8f0"
        : "#475569"
);
const chartGridColor = computed(() =>
    document.documentElement.classList.contains("dark")
        ? "rgba(148, 163, 184, 0.2)"
        : "rgba(100, 116, 139, 0.2)"
);

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            ticks: { color: chartTextColor.value },
            grid: { color: chartGridColor.value },
        },
        y: {
            ticks: { color: chartTextColor.value },
            grid: { color: chartGridColor.value },
        },
    },
}));

const chartOptionsQuantity = computed(() => {
    const base = chartOptions.value;
    return {
        ...base,
        scales: {
            x: base.scales.x,
            y: {
                ...base.scales.y,
                ticks: {
                    ...base.scales.y.ticks,
                    stepSize: 1,
                },
            },
        },
    };
});

const labels = computed(() =>
    series.value.map((row) =>
        formatPeriodLabel(row.period, meta.value.group_by)
    )
);

const amountChartData = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: t("ReportsExpenses.ChartAmountTitle"),
            data: series.value.map((r) => r.total_amount),
            backgroundColor: "rgba(99, 102, 241, 0.65)",
            borderColor: "rgb(99, 102, 241)",
            borderWidth: 1,
        },
    ],
}));

const countChartData = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: t("ReportsExpenses.ChartCountTitle"),
            data: series.value.map((r) => r.expense_count),
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.15)",
            tension: 0.25,
            fill: true,
        },
    ],
}));

let fetchTimer = null;
function scheduleFetch() {
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(() => fetchReport(), 300);
}

async function fetchReport() {
    loading.value = true;
    try {
        const params = {
            group_by: filters.value.groupBy,
            date_from: toDateTimeParam(filters.value.dateFrom, "start"),
            date_to: toDateTimeParam(filters.value.dateTo, "end"),
        };
        const { data } = await reportsService.getExpensesReport(params);
        meta.value = {
            group_by: data.group_by,
        };
        series.value = data.series ?? [];
    } catch (e) {
        if (e.response?.status === 403) {
            await router.replace("/");
            return;
        }
        console.error(e);
        series.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    initializeDefaultsAndFetch();
});

async function initializeDefaultsAndFetch() {
    try {
        const { data } = await configService.getConfig();
        const range = buildFilterRangeFromConfig(data);
        filters.value.dateFrom = range.from;
        filters.value.dateTo = range.to;
    } catch (error) {
        console.error("Error loading config:", error);
    } finally {
        fetchReport();
    }
}
</script>
