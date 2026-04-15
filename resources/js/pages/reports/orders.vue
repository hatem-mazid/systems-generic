<template>
    <div class="touch-manipulation p-4 text-surface-800 dark:text-surface-100 sm:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold sm:text-3xl">
                    {{ $t("ReportsOrders.Title") }}
                </h1>
                <p class="mt-1 text-surface-600 dark:text-surface-400">
                    {{ $t("ReportsOrders.Subtitle") }}
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
                        for="report-date-from"
                    >
                        {{ $t("ReportsOrders.DateFrom") }}
                    </label>
                    <DatePicker
                        id="report-date-from"
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
                        for="report-date-to"
                    >
                        {{ $t("ReportsOrders.DateTo") }}
                    </label>
                    <DatePicker
                        id="report-date-to"
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
                        for="report-group-by"
                    >
                        {{ $t("ReportsOrders.GroupBy") }}
                    </label>
                    <Select
                        id="report-group-by"
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
                        :label="$t('ReportsOrders.Apply')"
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
                $t("ReportsOrders.Loading")
            }}</span>
        </div>

        <div
            v-else-if="!series.length"
            class="mt-8 rounded-2xl border border-dashed border-surface-300 p-10 text-center text-surface-600 dark:border-surface-600 dark:text-surface-400"
        >
            {{ $t("ReportsOrders.Empty") }}
        </div>

        <div v-else class="mt-8 grid gap-8 lg:grid-cols-1">
            <div
                class="rounded-2xl border border-surface-200/80 bg-surface-0 p-4 shadow-sm dark:border-surface-700 dark:bg-surface-900 sm:p-6"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-surface-800 dark:text-surface-100"
                >
                    {{ $t("ReportsOrders.UnitGroupTakeawayTitle") }}
                </h2>

                <div
                    v-if="!unitGroupBreakdownRows.length"
                    class="rounded-xl border border-dashed border-surface-300 px-4 py-6 text-center text-surface-600 dark:border-surface-600 dark:text-surface-400"
                >
                    {{ $t("ReportsOrders.Empty") }}
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-surface-200 text-surface-600 dark:border-surface-700 dark:text-surface-300"
                            >
                                <th class="px-3 py-2 font-semibold">
                                    {{ $t("ReportsOrders.UnitGroupColumn") }}
                                </th>
                                <th class="px-3 py-2 font-semibold">
                                    {{ $t("ReportsOrders.ChartValueTitle") }}
                                </th>
                                <th class="px-3 py-2 font-semibold">
                                    {{ $t("ReportsOrders.ChartOrderCountTitle") }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in unitGroupBreakdownRows"
                                :key="
                                    row.is_takeaway
                                        ? 'takeaway'
                                        : `${row.unit_group_id ?? row.group_name}`
                                "
                                class="border-b border-surface-200/70 text-surface-700 dark:border-surface-700/80 dark:text-surface-200"
                            >
                                <td class="px-3 py-2">{{ row.group_name }}</td>
                                <td class="px-3 py-2">
                                    {{ formatMoney(row.total_value) }}
                                </td>
                                <td class="px-3 py-2">{{ row.order_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div
                class="rounded-2xl border border-surface-200/80 bg-surface-0 p-4 shadow-sm dark:border-surface-700 dark:bg-surface-900 sm:p-6"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-surface-800 dark:text-surface-100"
                >
                    {{ $t("ReportsOrders.ChartValueTitle") }}
                </h2>
                <div class="h-80 w-full min-w-0">
                    <Chart
                        type="bar"
                        :data="valueChartData"
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
                    {{ $t("ReportsOrders.ChartOrderCountTitle") }}
                </h2>
                <div class="h-80 w-full min-w-0">
                    <Chart
                        type="line"
                        :data="orderCountChartData"
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
const unitGroupBreakdown = ref([]);
const meta = ref({ group_by: "day" });

const filters = ref({
    dateFrom: null,
    dateTo: null,
    groupBy: "day",
});

const groupByOptions = computed(() => [
    { label: t("ReportsOrders.GroupByDay"), value: "day" },
    { label: t("ReportsOrders.GroupByWeek"), value: "week" },
    { label: t("ReportsOrders.GroupByMonth"), value: "month" },
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

const takeawayLabel = computed(() => t("ReportsOrders.TakeawayGroup"));

const unitGroupBreakdownRows = computed(() =>
    unitGroupBreakdown.value.map((row) => ({
        ...row,
        group_name: row.is_takeaway ? takeawayLabel.value : row.group_name,
    }))
);

const valueChartData = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: t("ReportsOrders.ChartValueTitle"),
            data: series.value.map((r) => r.total_value),
            backgroundColor: "rgba(99, 102, 241, 0.65)",
            borderColor: "rgb(99, 102, 241)",
            borderWidth: 1,
        },
    ],
}));

const orderCountChartData = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: t("ReportsOrders.ChartOrderCountTitle"),
            data: series.value.map((r) => r.order_count),
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
        const { data } = await reportsService.getOrdersReport(params);
        meta.value = {
            group_by: data.group_by,
        };
        series.value = data.series ?? [];
        unitGroupBreakdown.value = data.unit_group_breakdown ?? [];
    } catch (e) {
        if (e.response?.status === 403) {
            await router.replace("/");
            return;
        }
        console.error(e);
        series.value = [];
        unitGroupBreakdown.value = [];
    } finally {
        loading.value = false;
    }
}

function formatMoney(value) {
    const numericValue = Number(value ?? 0);
    const safeValue = Number.isFinite(numericValue) ? numericValue : 0;
    const localeKey = locale.value === "ar" ? "ar" : "en";
    return new Intl.NumberFormat(localeKey, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(safeValue);
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
