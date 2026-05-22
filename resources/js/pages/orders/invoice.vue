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
                    <div
                        class="thermal-receipt"
                        :dir="receiptDir"
                    >
                        <header class="thermal-brand">
                            <div class="thermal-business">{{ businessName }}</div>
                            <div class="thermal-table">
                                {{ $t("OrdersList.ReceiptTableNo", { n: order.unit_name ?? order.unit_id ?? "—" }) }}
                            </div>
                        </header>

                        <div class="thermal-meta-grid">
                            <div class="thermal-meta-box">
                                {{ $t("OrdersList.ReceiptOrderNo", { n: order.id }) }}
                            </div>
                            <div class="thermal-meta-box">
                                {{ $t("OrdersList.ReceiptCashier", { name: order.user_name ?? "—" }) }}
                            </div>
                            <div class="thermal-meta-box">
                                {{ $t("OrdersList.ReceiptTime", { time: receiptTime }) }}
                            </div>
                            <div class="thermal-meta-box">
                                {{ $t("OrdersList.ReceiptDate", { date: receiptDate }) }}
                            </div>
                        </div>

                        <div class="thermal-items-list">
                            <div
                                v-for="(line, idx) in mergedInvoiceLines"
                                :key="line.key + '-' + idx"
                                class="thermal-item"
                            >
                                <div class="thermal-item-head">
                                    <span class="thermal-item-name">{{ line.name ?? "—" }}</span>
                                    <span class="thermal-item-total">{{ formatReceiptMoney(line.total) }}</span>
                                </div>
                                <div class="thermal-item-detail">
                                    {{ $t("OrdersList.ReceiptQtyPrice", {
                                        qty: line.quantity ?? "—",
                                        price: formatReceiptMoney(line.price),
                                    }) }}
                                </div>
                            </div>
                            <div v-if="!mergedInvoiceLines.length" class="thermal-empty-items">—</div>
                        </div>

                        <div class="thermal-total-box">
                            {{ $t("OrdersList.ReceiptTotal", { amount: formatReceiptMoney(order.total) }) }}
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
import {
    formatReceiptDate,
    formatReceiptMoney as formatReceiptMoneyUtil,
    formatReceiptTime,
    getReceiptBusinessName,
} from "../../utils/thermalReceipt";

const { t, locale } = useI18n();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const loadError = ref("");
const order = ref(null);

const mergedInvoiceLines = computed(() => mergeOrderItems(order.value?.items));
const businessName = getReceiptBusinessName();
const receiptDir = computed(() => (locale.value === "ar" ? "rtl" : "ltr"));
const receiptTimestamp = computed(() =>
    order.value?.opened_at ?? order.value?.created_at ?? null,
);
const receiptDate = computed(() =>
    formatReceiptDate(receiptTimestamp.value, locale.value),
);
const receiptTime = computed(() =>
    formatReceiptTime(receiptTimestamp.value, locale.value),
);
const invoiceLinesTotal = computed(() =>
    mergedInvoiceLines.value.reduce((sum, line) => {
        const lineTotal = Number(line?.total);
        return sum + (Number.isNaN(lineTotal) ? 0 : lineTotal);
    }, 0),
);

function formatReceiptMoney(value) {
    return formatReceiptMoneyUtil(value, locale.value);
}

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
        width: 72mm;
        margin: 0 auto;
        padding: 3mm 2mm 4mm;
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #000 !important;
        background: #fff !important;
    }

    .thermal-brand {
        text-align: center;
        margin-bottom: 3mm;
    }

    .thermal-business {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .thermal-table {
        margin-top: 2mm;
        font-size: 12px;
        font-weight: 600;
    }

    .thermal-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2mm;
        margin-bottom: 4mm;
    }

    .thermal-meta-box {
        border: 1px solid #bbb;
        border-radius: 6px;
        padding: 2mm 1.5mm;
        font-size: 10px;
        text-align: center;
        line-height: 1.35;
        word-break: break-word;
    }

    .thermal-items-list {
        margin-bottom: 3mm;
    }

    .thermal-item {
        margin-bottom: 3.5mm;
    }

    .thermal-item-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 2mm;
        font-weight: 700;
        font-size: 11px;
    }

    .thermal-item-name {
        flex: 1;
        word-break: break-word;
    }

    .thermal-item-total {
        flex-shrink: 0;
        white-space: nowrap;
    }

    .thermal-item-detail {
        margin-top: 0.6mm;
        font-size: 10px;
        color: #333;
    }

    .thermal-receipt[dir="rtl"] .thermal-item-detail {
        text-align: right;
    }

    .thermal-receipt[dir="ltr"] .thermal-item-detail {
        text-align: left;
    }

    .thermal-empty-items {
        padding: 3mm 0;
        text-align: center;
        color: #666;
        font-size: 10px;
    }

    .thermal-total-box {
        border: 1px solid #333;
        border-radius: 10px;
        padding: 3mm 2mm;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }
}
</style>
