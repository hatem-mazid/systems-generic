<template>
    <div class="invoice-page mx-auto max-w-4xl p-4 sm:p-6">
        <div class="no-print mb-4 flex items-center justify-between gap-2">
            <Button
                type="button"
                outlined
                severity="secondary"
                :label="$t('OrderDetail.BackToList')"
                @click="goBack"
            >
                <template #icon>
                    <AppIcon name="hi-arrow-left" />
                </template>
            </Button>

            <Button
                v-if="order"
                type="button"
                :label="$t('OrdersList.PrintInvoice')"
                @click="onPrint"
            >
                <template #icon>
                    <AppIcon name="hi-printer" />
                </template>
            </Button>
        </div>

        <Card class="rounded-2xl border border-surface-200/80 shadow-sm dark:border-surface-700">
            <template #content>
                <div v-if="loading" class="space-y-4">
                    <Skeleton height="2rem" />
                    <Skeleton height="10rem" />
                    <Skeleton height="14rem" />
                </div>

                <Message v-else-if="loadError" severity="error" :closable="false">
                    {{ loadError }}
                </Message>

                <div v-else-if="order">
                    <!-- Thermal POS receipt (print only) -->
                    <div class="thermal-receipt">
                        <div class="thermal-header">
                            <div class="thermal-title">
                                {{ $t("OrdersList.InvoiceTitle") }} #{{ order.id }}
                            </div>
                            <div class="thermal-subtitle">
                                {{ formatDateTime(order.created_at) }}
                            </div>
                        </div>

                        <div class="thermal-divider" />

                        <div class="thermal-meta">
                            <div class="thermal-row">
                                <span>{{ $t("OrdersList.ColumnUnit") }}</span>
                                <span>{{ order.unit_name ?? order.unit_id ?? "—" }}</span>
                            </div>
                            <div class="thermal-row">
                                <span>{{ $t("OrdersList.ColumnWaiter") }}</span>
                                <span>{{ order.user_name ?? "—" }}</span>
                            </div>
                            <div class="thermal-row">
                                <span>{{ $t("OrdersList.ColumnStatus") }}</span>
                                <span>{{ statusLabel(order.status) }}</span>
                            </div>
                            <div class="thermal-row">
                                <span>{{ $t("OrdersList.OpenedShort") }}</span>
                                <span>{{ formatDateTime(order.opened_at) }}</span>
                            </div>
                        </div>

                        <div class="thermal-divider" />

                        <table class="thermal-items">
                            <thead>
                                <tr>
                                    <th class="name">{{ $t("OrdersList.LineName") }}</th>
                                    <th class="qty">{{ $t("OrdersList.LineQty") }}</th>
                                    <th class="price">{{ $t("OrdersList.LinePrice") }}</th>
                                    <th class="total-col">{{ $t("OrdersList.LineTotal") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(line, idx) in mergedInvoiceLines"
                                    :key="line.key + '-' + idx"
                                >
                                    <td class="name">{{ line.name ?? "—" }}</td>
                                    <td class="qty">{{ line.quantity ?? "—" }}</td>
                                    <td class="price">{{ formatMoney(line.price) }}</td>
                                    <td class="total-col">{{ formatMoney(line.total) }}</td>
                                </tr>
                                <tr v-if="!mergedInvoiceLines.length">
                                    <td colspan="4" class="thermal-empty">—</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="thermal-divider" />

                        <div class="thermal-total">
                            <span>{{ $t("OrdersList.ColumnTotal") }}</span>
                            <span>{{ formatMoney(order.total) }}</span>
                        </div>
                    </div>

                    <!-- Screen layout -->
                    <div class="screen-invoice space-y-6">
                    <header class="border-b border-dashed border-surface-300 pb-4 dark:border-surface-600">
                        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">
                            {{ $t("OrdersList.InvoiceTitle") }} #{{ order.id }}
                        </h1>
                        <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">
                            {{ formatDateTime(order.created_at) }}
                        </p>
                    </header>

                    <section class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-lg bg-surface-50 p-3 dark:bg-surface-800/60">
                            <p class="text-surface-500 dark:text-surface-400">{{ $t("OrdersList.ColumnUnit") }}</p>
                            <p class="font-semibold text-surface-900 dark:text-surface-0">{{ order.unit_name ?? order.unit_id ?? "—" }}</p>
                        </div>
                        <div class="rounded-lg bg-surface-50 p-3 dark:bg-surface-800/60">
                            <p class="text-surface-500 dark:text-surface-400">{{ $t("OrdersList.ColumnWaiter") }}</p>
                            <p class="font-semibold text-surface-900 dark:text-surface-0">{{ order.user_name ?? "—" }}</p>
                        </div>
                        <div class="rounded-lg bg-surface-50 p-3 dark:bg-surface-800/60">
                            <p class="text-surface-500 dark:text-surface-400">{{ $t("OrdersList.ColumnStatus") }}</p>
                            <p class="font-semibold capitalize text-surface-900 dark:text-surface-0">{{ statusLabel(order.status) }}</p>
                        </div>
                        <div class="rounded-lg bg-surface-50 p-3 dark:bg-surface-800/60">
                            <p class="text-surface-500 dark:text-surface-400">{{ $t("OrdersList.OpenedShort") }}</p>
                            <p class="font-semibold text-surface-900 dark:text-surface-0">{{ formatDateTime(order.opened_at) }}</p>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-xl border border-surface-200 dark:border-surface-700">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-100 dark:bg-surface-800">
                                <tr class="text-left text-surface-700 dark:text-surface-300">
                                    <th class="px-3 py-2">{{ $t("OrdersList.LineName") }}</th>
                                    <th class="px-3 py-2 text-center">{{ $t("OrdersList.LineQty") }}</th>
                                    <th class="px-3 py-2 text-right">{{ $t("OrdersList.LinePrice") }}</th>
                                    <th class="px-3 py-2 text-right">{{ $t("OrdersList.LineTotal") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(line, idx) in mergedInvoiceLines"
                                    :key="line.key + '-' + idx"
                                    class="border-t border-surface-200 dark:border-surface-700"
                                >
                                    <td class="px-3 py-2">{{ line.name ?? "—" }}</td>
                                    <td class="px-3 py-2 text-center">{{ line.quantity ?? "—" }}</td>
                                    <td class="px-3 py-2 text-right">{{ formatMoney(line.price) }}</td>
                                    <td class="px-3 py-2 text-right">{{ formatMoney(line.total) }}</td>
                                </tr>
                                <tr
                                    v-if="mergedInvoiceLines.length"
                                    class="border-t-2 border-surface-300 bg-surface-50 dark:border-surface-600 dark:bg-surface-800/60"
                                >
                                    <td colspan="3" class="px-3 py-2 text-right font-semibold">{{ $t("OrdersList.ColumnTotal") }}</td>
                                    <td class="px-3 py-2 text-right font-bold">{{ formatMoney(invoiceLinesTotal) }}</td>
                                </tr>
                                <tr v-if="!mergedInvoiceLines.length">
                                    <td colspan="4" class="px-3 py-4 text-center text-surface-500">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <footer class="flex justify-end border-t border-dashed border-surface-300 pt-4 dark:border-surface-600">
                        <div class="rounded-lg bg-surface-100 px-4 py-3 text-right dark:bg-surface-800">
                            <p class="text-sm text-surface-500 dark:text-surface-400">{{ $t("OrdersList.ColumnTotal") }}</p>
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ formatMoney(order.total) }}</p>
                        </div>
                    </footer>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Message from "primevue/message";
import Skeleton from "primevue/skeleton";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { ordersService } from "../../apis/services/orders/orders.apis";
import { mergeOrderItems } from "../../utils/orderItemsMerge";

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const loadError = ref("");
const order = ref(null);

const mergedInvoiceLines = computed(() => mergeOrderItems(order.value?.items));
const invoiceLinesTotal = computed(() =>
    mergedInvoiceLines.value.reduce((sum, line) => {
        const lineTotal = Number(line?.total);
        return sum + (Number.isNaN(lineTotal) ? 0 : lineTotal);
    }, 0),
);

function formatMoney(value) {
    if (value === undefined || value === null || value === "") {
        return "—";
    }
    const n = Number(value);
    if (Number.isNaN(n)) {
        return String(value);
    }
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "IQD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(n);
}

function formatDateTime(iso) {
    if (!iso) {
        return "—";
    }
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return String(iso);
    }
}

function statusLabel(status) {
    const key = `OrdersList.Statuses.${status}`;
    const translated = t(key);
    return translated === key ? status : translated;
}

async function fetchOrder() {
    const id = String(route.params.id ?? "");
    if (!id) {
        loadError.value = t("OrderDetail.LoadError");
        loading.value = false;
        return;
    }
    loading.value = true;
    loadError.value = "";
    try {
        const { data } = await ordersService.getOrder(id);
        order.value = data?.data ?? data;
    } catch {
        loadError.value = t("OrderDetail.LoadError");
    } finally {
        loading.value = false;
    }
}

function onPrint() {
    window.print();
}

function goBack() {
    router.push("/orders");
}

onMounted(fetchOrder);
</script>

<style>
.thermal-receipt {
    display: none;
}

@media print {
    @page {
        size: 80mm auto;
        margin: 0;
    }

    html,
    body {
        width: 80mm;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .no-print,
    .screen-invoice {
        display: none !important;
    }

    .default-layout > div:first-child,
    .default-layout > div:last-child > .p-4,
    .default-layout footer {
        display: none !important;
    }

    .default-layout > div:last-child {
        padding-left: 0 !important;
        min-height: auto !important;
    }

    .default-layout main {
        padding: 0 !important;
    }

    .invoice-page {
        max-width: 80mm !important;
        width: 80mm !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .invoice-page .p-card,
    .invoice-page .p-card-content {
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    .thermal-receipt {
        display: block;
        width: 76mm;
        margin: 0 auto;
        padding: 2mm 1mm 3mm;
        font-family: "Courier New", Courier, monospace;
        font-size: 11px;
        line-height: 1.35;
        color: #000 !important;
        background: #fff !important;
    }

    .thermal-title {
        font-size: 14px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.02em;
    }

    .thermal-subtitle {
        margin-top: 1mm;
        font-size: 10px;
        text-align: center;
    }

    .thermal-divider {
        margin: 2mm 0;
        border-top: 1px dashed #000;
    }

    .thermal-row {
        display: flex;
        justify-content: space-between;
        gap: 2mm;
        margin-bottom: 0.8mm;
        font-size: 10px;
    }

    .thermal-row span:last-child {
        font-weight: 700;
        text-align: right;
        word-break: break-word;
    }

    .thermal-items {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        table-layout: fixed;
    }

    .thermal-items th {
        padding: 1mm 0;
        border-bottom: 1px solid #000;
        font-weight: 700;
        text-align: left;
    }

    .thermal-items td {
        padding: 1.2mm 0;
        vertical-align: top;
    }

    .thermal-items .name {
        width: 38%;
        word-break: break-word;
    }

    .thermal-items .qty {
        width: 12%;
        text-align: center;
    }

    .thermal-items .price,
    .thermal-items .total-col {
        width: 25%;
        text-align: right;
        white-space: nowrap;
    }

    .thermal-empty {
        padding: 2mm 0 !important;
        text-align: center;
    }

    .thermal-total {
        display: flex;
        justify-content: space-between;
        gap: 2mm;
        font-size: 14px;
        font-weight: 700;
    }
}
</style>
