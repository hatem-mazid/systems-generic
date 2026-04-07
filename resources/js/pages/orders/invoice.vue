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

                <div v-else-if="order" class="space-y-6">
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
                                    v-for="(line, idx) in order.items ?? []"
                                    :key="line.id ?? idx"
                                    class="border-t border-surface-200 dark:border-surface-700"
                                >
                                    <td class="px-3 py-2">{{ line.name ?? "—" }}</td>
                                    <td class="px-3 py-2 text-center">{{ line.quantity ?? "—" }}</td>
                                    <td class="px-3 py-2 text-right">{{ formatMoney(line.price) }}</td>
                                    <td class="px-3 py-2 text-right">{{ formatMoney(line.total) }}</td>
                                </tr>
                                <tr v-if="!(order.items?.length)">
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
            </template>
        </Card>
    </div>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Message from "primevue/message";
import Skeleton from "primevue/skeleton";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { ordersService } from "../../apis/services/orders/orders.apis";

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const loadError = ref("");
const order = ref(null);

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
@media print {
    .no-print {
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
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
