<template>
    <div class="order-detail touch-manipulation">
        <div
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="flex min-w-0 flex-wrap items-center gap-2 sm:gap-3">
                <Button
                    as="router-link"
                    to="/orders"
                    severity="secondary"
                    outlined
                    size="large"
                    class="shrink-0"
                    :aria-label="$t('OrderDetail.BackToList')"
                >
                    <span class="flex items-center gap-2">
                        <i class="pi pi-arrow-left" aria-hidden="true" />
                        <span class="hidden sm:inline">{{
                            $t("OrderDetail.BackToList")
                        }}</span>
                    </span>
                </Button>
                <h1
                    v-if="order"
                    class="text-xl font-semibold text-surface-800 dark:text-surface-100 sm:text-2xl"
                >
                    {{ $t("OrderDetail.Title", { id: order.id }) }}
                </h1>
                <Skeleton
                    v-else-if="loading"
                    width="12rem"
                    height="2rem"
                    class="rounded-lg"
                />
                <Tag
                    v-if="order?.status"
                    :value="statusLabel(order.status)"
                    :severity="statusSeverity(order.status)"
                    class="capitalize"
                />
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <Button
                    v-if="order"
                    type="button"
                    outlined
                    size="large"
                    icon="pi pi-print"
                    :label="$t('OrdersList.PrintInvoice')"
                    class="min-h-[48px]"
                    @click="onPrint"
                />
            </div>
        </div>

        <div v-if="loading" class="space-y-6">
            <Skeleton height="12rem" class="rounded-2xl" />
            <Skeleton height="20rem" class="rounded-2xl" />
        </div>

        <Message
            v-else-if="loadError"
            severity="error"
            class="max-w-xl"
            :closable="false"
        >
            {{ loadError }}
        </Message>

        <template v-else-if="order">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <Card
                    class="overflow-hidden rounded-2xl border border-surface-200/80 shadow-sm dark:border-surface-700 xl:col-span-1"
                >
                    <template #title>
                        <span class="text-lg font-semibold">{{
                            $t("OrderDetail.SummaryTitle")
                        }}</span>
                    </template>
                    <template #content>
                        <dl class="space-y-4 text-sm">
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrdersList.ColumnStatus") }}
                                </dt>
                                <dd>
                                    <Tag
                                        v-if="order.status"
                                        :value="statusLabel(order.status)"
                                        :severity="statusSeverity(order.status)"
                                        class="capitalize"
                                    />
                                    <span v-else>—</span>
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrdersList.ColumnTotal") }}
                                </dt>
                                <dd
                                    class="text-lg font-semibold tabular-nums text-surface-900 dark:text-surface-50"
                                >
                                    {{ formatMoney(order.total) }}
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrdersList.ColumnUnit") }}
                                </dt>
                                <dd class="text-end font-medium">
                                    {{
                                        order.unit_name ?? order.unit_id ?? "—"
                                    }}
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrdersList.ColumnUserName") }}
                                </dt>
                                <dd class="text-end font-medium">
                                    {{ order.user_name ?? "—" }}
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrderDetail.OpenedAt") }}
                                </dt>
                                <dd class="text-end">
                                    {{ formatDateTime(order.opened_at) }}
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 border-b border-surface-200 pb-3 dark:border-surface-700 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrderDetail.ClosedAt") }}
                                </dt>
                                <dd class="text-end">
                                    {{ formatDateTime(order.closed_at) }}
                                </dd>
                            </div>
                            <div
                                class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <dt
                                    class="text-surface-600 dark:text-surface-400"
                                >
                                    {{ $t("OrderDetail.CreatedAt") }}
                                </dt>
                                <dd class="text-end">
                                    {{ formatDateTime(order.created_at) }}
                                </dd>
                            </div>
                        </dl>
                    </template>
                </Card>

                <Card
                    class="overflow-hidden rounded-2xl border border-surface-200/80 shadow-sm dark:border-surface-700 xl:col-span-2"
                >
                    <template #title>
                        <span class="text-lg font-semibold">{{
                            $t("OrderDetail.LineItemsTitle")
                        }}</span>
                    </template>
                    <template #content>
                        <div
                            v-if="!order.items?.length"
                            class="rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-400"
                        >
                            {{ $t("OrderDetail.EmptyItems") }}
                        </div>
                        <ul v-else class="space-y-3">
                            <li
                                v-for="(line, idx) in order.items"
                                :key="line.id ?? `line-${idx}`"
                                class="rounded-xl border border-surface-200/90 bg-surface-0 p-3 sm:p-4 dark:border-surface-700 dark:bg-surface-900/50"
                            >
                                <div
                                    class="flex flex-row items-start gap-3 sm:gap-5"
                                >
                                    <div
                                        class="h-24 w-24 shrink-0 sm:h-32 sm:w-32"
                                    >
                                        <a
                                            v-if="
                                                line.image &&
                                                !lineImageFailed[
                                                    lineImageFailKey(
                                                        line,
                                                        idx
                                                    )
                                                ]
                                            "
                                            :href="line.image"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="group block h-24 w-24 overflow-hidden rounded-xl border border-surface-200 bg-surface-100 sm:h-32 sm:w-32 dark:border-surface-600 dark:bg-surface-800"
                                        >
                                            <img
                                                :src="line.image"
                                                :alt="
                                                    line.name ||
                                                    $t('OrdersList.LineName')
                                                "
                                                class="h-full w-full object-cover transition-opacity group-hover:opacity-90"
                                                loading="lazy"
                                                @error="
                                                    onLineImageError(line, idx)
                                                "
                                            />
                                        </a>
                                        <div
                                            v-else
                                            class="flex h-24 w-24 items-center justify-center rounded-xl border border-dashed border-surface-300 bg-surface-50 sm:h-32 sm:w-32 dark:border-surface-600 dark:bg-surface-800/80"
                                            aria-hidden="true"
                                        >
                                            <i
                                                class="pi pi-image text-2xl text-surface-400 sm:text-3xl dark:text-surface-500"
                                            />
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div
                                            class="flex flex-wrap items-start justify-between gap-2"
                                        >
                                            <h2
                                                class="text-base font-semibold leading-snug text-surface-900 dark:text-surface-50"
                                            >
                                                {{ line.name ?? "—" }}
                                            </h2>
                                            <Tag
                                                v-if="line.type"
                                                :value="
                                                    lineTypeLabel(line.type)
                                                "
                                                severity="secondary"
                                                class="shrink-0 text-xs capitalize"
                                            />
                                        </div>
                                        <p
                                            v-if="line.notes"
                                            class="text-sm leading-relaxed text-surface-600 dark:text-surface-400"
                                        >
                                            <span class="font-medium text-surface-700 dark:text-surface-300">{{
                                                $t("OrderDetail.Notes")
                                            }}</span>
                                            {{ line.notes }}
                                        </p>
                                        <pre
                                            v-if="
                                                line.meta &&
                                                Object.keys(line.meta).length
                                            "
                                            class="max-h-28 overflow-auto rounded-lg bg-surface-100 p-2.5 font-mono text-xs leading-relaxed text-surface-800 dark:bg-surface-800 dark:text-surface-200"
                                            >{{ formatMeta(line.meta) }}</pre
                                        >
                                        <div
                                            class="flex flex-wrap items-end justify-between gap-3 border-t border-surface-200 pt-3 dark:border-surface-700"
                                        >
                                            <div
                                                class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-surface-600 dark:text-surface-400"
                                            >
                                                <span>
                                                    {{ $t("OrderDetail.Qty") }}:
                                                    <strong
                                                        class="tabular-nums text-surface-900 dark:text-surface-100"
                                                        >{{
                                                            line.quantity ??
                                                            "—"
                                                        }}</strong
                                                    >
                                                </span>
                                                <span>
                                                    {{
                                                        $t(
                                                            "OrdersList.LinePrice"
                                                        )
                                                    }}:
                                                    <strong
                                                        class="tabular-nums text-surface-900 dark:text-surface-100"
                                                        >{{
                                                            formatMoney(
                                                                line.price
                                                            )
                                                        }}</strong
                                                    >
                                                </span>
                                            </div>
                                            <div
                                                class="text-end text-base font-semibold tabular-nums text-surface-900 dark:text-surface-50"
                                            >
                                                {{ formatMoney(line.total) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <Divider
                            v-if="order.items?.length"
                            class="my-6"
                            layout="horizontal"
                        />

                        <div
                            v-if="order.items?.length"
                            class="flex flex-col items-stretch justify-between gap-2 rounded-xl bg-surface-100 px-4 py-3 dark:bg-surface-800 sm:flex-row sm:items-center"
                        >
                            <span
                                class="font-medium text-surface-700 dark:text-surface-300"
                                >{{ $t("OrderDetail.GrandTotal") }}</span
                            >
                            <span
                                class="text-xl font-bold tabular-nums text-surface-900 dark:text-surface-50"
                                >{{ formatMoney(order.total) }}</span
                            >
                        </div>
                    </template>
                </Card>
            </div>
        </template>
    </div>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Divider from "primevue/divider";
import Message from "primevue/message";
import Skeleton from "primevue/skeleton";
import Tag from "primevue/tag";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import { ordersService } from "../../apis/services/orders/orders.apis";
import { OrderStatus } from "../../apis/services/orders/orders.type";
import { printOrderInvoice } from "../../components/pages/orders/printOrderInvoice";

const { t } = useI18n();
const route = useRoute();

const loading = ref(true);
const loadError = ref(null);
const order = ref(null);
/** Hide thumbnails that fail to load. */
const lineImageFailed = reactive({});

const orderId = computed(() => String(route.params.id ?? ""));

watch(
    () => route.params.id,
    () => {
        fetchOrder();
    }
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
        return iso;
    }
}

function formatMeta(meta) {
    try {
        return JSON.stringify(meta, null, 2);
    } catch {
        return String(meta);
    }
}

function statusLabel(status) {
    const key = `OrdersList.Statuses.${status}`;
    const translated = t(key);
    return translated === key ? status : translated;
}

function statusSeverity(status) {
    switch (status) {
        case OrderStatus.Open:
            return "success";
        case OrderStatus.Pending:
            return "warn";
        case OrderStatus.Closed:
            return "secondary";
        case OrderStatus.Cancelled:
            return "danger";
        default:
            return "secondary";
    }
}

function lineTypeLabel(type) {
    const key = `OrderDetail.ItemTypes.${type}`;
    const translated = t(key);
    return translated === key ? type : translated;
}

function lineImageFailKey(line, lineIdx) {
    return line.id != null ? String(line.id) : `idx-${lineIdx}`;
}

function onLineImageError(line, lineIdx) {
    lineImageFailed[lineImageFailKey(line, lineIdx)] = true;
}

function onPrint() {
    if (!order.value) {
        return;
    }
    printOrderInvoice(order.value, {
        title: t("OrdersList.InvoiceTitle"),
        unit: t("OrdersList.ColumnUnit"),
        waiter: t("OrdersList.ColumnWaiter"),
        status: t("OrdersList.ColumnStatus"),
        openedAt: t("OrdersList.OpenedShort"),
        createdAt: t("OrdersList.CreatedShort"),
        product: t("OrdersList.LineName"),
        qty: t("OrdersList.LineQty"),
        price: t("OrdersList.LinePrice"),
        lineTotal: t("OrdersList.LineTotal"),
        total: t("OrdersList.ColumnTotal"),
    });
}

async function fetchOrder() {
    const id = orderId.value;
    if (!id) {
        loadError.value = t("OrderDetail.LoadError");
        loading.value = false;
        order.value = null;
        return;
    }

    loading.value = true;
    loadError.value = null;
    order.value = null;
    Object.keys(lineImageFailed).forEach((k) => delete lineImageFailed[k]);

    try {
        const { data } = await ordersService.getOrder(id);
        order.value = data?.data ?? data;
    } catch {
        loadError.value = t("OrderDetail.LoadError");
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchOrder();
});
</script>
