<template>
    <Drawer
        v-model:visible="innerVisible"
        position="right"
        class="order-detail-drawer !w-full sm:!max-w-[min(100vw-0.5rem,40rem)]"
        :block-scroll="true"
        :dismissable="true"
        :pt="{
            content: { class: 'p-0 flex flex-col min-h-0 max-h-[100dvh]' },
        }"
        @hide="onHide"
    >
        <template #header>
            <div
                class="flex w-full min-w-0 flex-wrap items-center justify-between gap-2 pe-2"
            >
                <h2
                    v-if="order"
                    class="min-w-0 truncate text-lg font-semibold text-surface-900 dark:text-surface-0"
                >
                    {{ $t("OrderDetail.Title", { id: order.id }) }}
                </h2>
                <span v-else class="text-lg font-semibold text-surface-600">{{
                    $t("OrderDetail.Title", { id: "…" })
                }}</span>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button
                        v-if="order?.id"
                        type="button"
                        outlined
                        size="small"
                        severity="secondary"
                        :label="$t('OrderDetail.OpenFullPage')"
                        icon="pi pi-external-link"
                        class="min-h-[40px]"
                        @click="openFullPage"
                    />
                </div>
            </div>
        </template>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 pb-6 pt-2">
                <div v-if="loading" class="space-y-4">
                    <Skeleton height="8rem" class="rounded-xl" />
                    <Skeleton height="14rem" class="rounded-xl" />
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
                    <Card
                        class="mb-4 overflow-hidden rounded-xl border border-surface-200/80 shadow-sm dark:border-surface-700"
                    >
                        <template #title>
                            <span class="text-base font-semibold">{{
                                $t("OrderDetail.SummaryTitle")
                            }}</span>
                        </template>
                        <template #content>
                            <dl class="space-y-3 text-sm">
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700"
                                >
                                    <dt class="text-surface-600 dark:text-surface-400">
                                        {{ $t("OrdersList.ColumnStatus") }}
                                    </dt>
                                    <dd>
                                        <Tag
                                            v-if="order.status"
                                            :value="statusLabel(order.status)"
                                            :severity="statusSeverity(order.status)"
                                            class="capitalize"
                                        />
                                    </dd>
                                </div>
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700"
                                >
                                    <dt class="text-surface-600 dark:text-surface-400">
                                        {{ $t("OrdersList.ColumnTotal") }}
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold tabular-nums text-surface-900 dark:text-surface-50"
                                    >
                                        {{ formatMoney(order.total) }}
                                    </dd>
                                </div>
                                <div
                                    class="flex flex-wrap items-start justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700"
                                >
                                    <dt class="text-surface-600 dark:text-surface-400">
                                        {{ $t("OrdersList.ColumnUnit") }}
                                    </dt>
                                    <dd class="text-end font-medium">
                                        {{ order.unit_name ?? order.unit_id ?? "—" }}
                                    </dd>
                                </div>
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <dt class="text-surface-600 dark:text-surface-400">
                                        {{ $t("OrderDetail.OpenedAt") }}
                                    </dt>
                                    <dd class="text-end">
                                        {{ formatDateTime(order.opened_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </template>
                    </Card>

                    <div
                        v-if="canEditItems"
                        class="mb-4 rounded-xl border border-surface-200 bg-surface-50 p-4 dark:border-surface-600 dark:bg-surface-900/40"
                    >
                        <p
                            class="mb-3 text-sm font-medium text-surface-800 dark:text-surface-100"
                        >
                            {{ $t("OrderDetail.AddProduct") }}
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="min-w-0 flex-1">
                                <label class="mb-1 block text-xs text-surface-600 dark:text-surface-400">
                                    {{ $t("OrderDetail.SelectProduct") }}
                                </label>
                                <Select
                                    v-model="selectedProductId"
                                    size="large"
                                    :options="products"
                                    option-label="name"
                                    option-value="id"
                                    filter
                                    :loading="productsLoading"
                                    :placeholder="$t('OrderDetail.SelectProduct')"
                                    class="w-full"
                                    scroll-height="min(50vh, 16rem)"
                                    :show-clear="true"
                                />
                            </div>
                            <div class="w-full shrink-0 sm:w-32">
                                <label class="mb-1 block text-xs text-surface-600 dark:text-surface-400">
                                    {{ $t("OrderDetail.Qty") }}
                                </label>
                                <InputNumber
                                    v-model="addQuantity"
                                    size="large"
                                    :min="1"
                                    :max="9999"
                                    show-buttons
                                    class="w-full"
                                    input-class="w-full text-center"
                                />
                            </div>
                            <Button
                                type="button"
                                size="large"
                                icon="pi pi-plus"
                                :label="$t('OrderDetail.AddToOrder')"
                                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                                :loading="adding"
                                :disabled="selectedProductId == null || adding"
                                @click="onAddProduct"
                            />
                        </div>
                    </div>

                    <Message
                        v-else
                        severity="info"
                        class="mb-4"
                        :closable="false"
                    >
                        {{ $t("OrderDetail.NotEditable") }}
                    </Message>

                    <Card
                        class="overflow-hidden rounded-xl border border-surface-200/80 shadow-sm dark:border-surface-700"
                    >
                        <template #title>
                            <span class="text-base font-semibold">{{
                                $t("OrderDetail.LineItemsTitle")
                            }}</span>
                        </template>
                        <template #content>
                            <div
                                v-if="!order.items?.length"
                                class="rounded-xl border border-dashed border-surface-300 p-6 text-center text-sm text-surface-600 dark:border-surface-600 dark:text-surface-400"
                            >
                                {{ $t("OrderDetail.EmptyItems") }}
                            </div>
                            <ul v-else class="space-y-3">
                                <li
                                    v-for="(line, idx) in order.items"
                                    :key="line.id ?? `line-${idx}`"
                                    class="rounded-xl border border-surface-200/90 bg-surface-0 p-3 dark:border-surface-700 dark:bg-surface-900/50"
                                >
                                    <div class="flex flex-row items-start gap-3">
                                        <div class="h-16 w-16 shrink-0 sm:h-20 sm:w-20">
                                            <img
                                                v-if="
                                                    line.image &&
                                                    !lineImageFailed[
                                                        lineImageFailKey(line, idx)
                                                    ]
                                                "
                                                :src="line.image"
                                                :alt="line.name || ''"
                                                class="h-full w-full rounded-lg object-cover"
                                                loading="lazy"
                                                @error="onLineImageError(line, idx)"
                                            />
                                            <div
                                                v-else
                                                class="flex h-16 w-full items-center justify-center rounded-lg border border-dashed border-surface-300 bg-surface-50 sm:h-20 dark:border-surface-600 dark:bg-surface-800/80"
                                                aria-hidden="true"
                                            >
                                                <i
                                                    class="pi pi-image text-xl text-surface-400 dark:text-surface-500"
                                                />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div
                                                class="flex flex-wrap items-start justify-between gap-2"
                                            >
                                                <h3
                                                    class="text-sm font-semibold leading-snug text-surface-900 dark:text-surface-50"
                                                >
                                                    {{ line.name ?? "—" }}
                                                </h3>
                                                <Button
                                                    v-if="canEditItems && line.id != null"
                                                    type="button"
                                                    icon="pi pi-trash"
                                                    severity="danger"
                                                    rounded
                                                    text
                                                    size="small"
                                                    :aria-label="$t('Delete')"
                                                    :loading="removingId === line.id"
                                                    :disabled="removingId != null && removingId !== line.id"
                                                    @click="confirmRemoveLine(line)"
                                                />
                                            </div>
                                            <div
                                                class="flex flex-wrap justify-between gap-2 text-sm text-surface-600 dark:text-surface-400"
                                            >
                                                <span>
                                                    {{ $t("OrderDetail.Qty") }}:
                                                    <strong class="tabular-nums text-surface-900 dark:text-surface-100">{{
                                                        line.quantity ?? "—"
                                                    }}</strong>
                                                </span>
                                                <span
                                                    class="font-semibold tabular-nums text-surface-900 dark:text-surface-50"
                                                >
                                                    {{ formatMoney(line.total) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <Divider
                                v-if="order.items?.length"
                                class="my-4"
                                layout="horizontal"
                            />

                            <div
                                v-if="order.items?.length"
                                class="flex items-center justify-between rounded-lg bg-surface-100 px-3 py-2 dark:bg-surface-800"
                            >
                                <span class="font-medium text-surface-700 dark:text-surface-300">{{
                                    $t("OrderDetail.GrandTotal")
                                }}</span>
                                <span
                                    class="text-lg font-bold tabular-nums text-surface-900 dark:text-surface-50"
                                    >{{ formatMoney(order.total) }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </template>
            </div>
        </div>
    </Drawer>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Divider from "primevue/divider";
import Drawer from "primevue/drawer";
import InputNumber from "primevue/inputnumber";
import Message from "primevue/message";
import Select from "primevue/select";
import Skeleton from "primevue/skeleton";
import Tag from "primevue/tag";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { computed, reactive, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { ordersService } from "../../../apis/services/orders/orders.apis";
import { productsService } from "../../../apis/services/products/products.apis";
import { OrderStatus } from "../../../apis/services/orders/orders.type";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    orderId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(["update:visible", "updated"]);

const { t } = useI18n();
const router = useRouter();
const confirm = useConfirm();
const toast = useToast();

const innerVisible = computed({
    get: () => props.visible,
    set: (v) => emit("update:visible", v),
});

const loading = ref(false);
const loadError = ref(null);
const order = ref(null);
const lineImageFailed = reactive({});

const products = ref([]);
const productsLoading = ref(false);
const selectedProductId = ref(null);
const addQuantity = ref(1);
const adding = ref(false);
const removingId = ref(null);

const canEditItems = computed(
    () =>
        order.value &&
        (order.value.status === OrderStatus.Active ||
            order.value.status === OrderStatus.Open)
);

function pickOrderPayload(res) {
    const body = res.data;
    return body?.data ?? body;
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
        return iso;
    }
}

function statusLabel(status) {
    const key = `OrdersList.Statuses.${status}`;
    const translated = t(key);
    return translated === key ? status : translated;
}

function statusSeverity(status) {
    switch (status) {
        case OrderStatus.Active:
        case OrderStatus.Open:
            return "success";
        case OrderStatus.Reserved:
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

function lineImageFailKey(line, lineIdx) {
    return line.id != null ? String(line.id) : `idx-${lineIdx}`;
}

function onLineImageError(line, lineIdx) {
    lineImageFailed[lineImageFailKey(line, lineIdx)] = true;
}

async function fetchOrder() {
    const id = props.orderId;
    if (id == null || id === "") {
        loadError.value = t("OrderDetail.LoadError");
        loading.value = false;
        order.value = null;
        return;
    }

    loading.value = true;
    loadError.value = null;
    Object.keys(lineImageFailed).forEach((k) => delete lineImageFailed[k]);

    try {
        const res = await ordersService.getOrder(id);
        order.value = pickOrderPayload(res);
    } catch {
        loadError.value = t("OrderDetail.LoadError");
        order.value = null;
    } finally {
        loading.value = false;
    }
}

async function loadProducts() {
    productsLoading.value = true;
    try {
        const { data } = await productsService.getProducts({
            per_page: 100,
            active: 1,
        });
        products.value = data.items ?? [];
    } catch {
        products.value = [];
    } finally {
        productsLoading.value = false;
    }
}

async function onAddProduct() {
    if (!order.value?.id || selectedProductId.value == null) {
        return;
    }
    const qty = addQuantity.value != null ? Number(addQuantity.value) : 1;
    if (Number.isNaN(qty) || qty < 1) {
        return;
    }

    adding.value = true;
    try {
        const res = await ordersService.addOrderItem(order.value.id, {
            product_id: selectedProductId.value,
            quantity: qty,
        });
        order.value = pickOrderPayload(res);
        selectedProductId.value = null;
        addQuantity.value = 1;
        emit("updated");
        toast.add({
            severity: "success",
            summary: t("OrderDetail.AddToOrder"),
            life: 2000,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    } finally {
        adding.value = false;
    }
}

function confirmRemoveLine(line) {
    if (!order.value?.id || line.id == null) {
        return;
    }
    confirm.require({
        message: t("OrderDetail.ConfirmRemoveLine"),
        header: t("OrderDetail.RemoveLineTitle"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: t("Cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: t("Delete"),
            severity: "danger",
        },
        accept: () => removeLine(line.id),
    });
}

async function removeLine(itemId) {
    if (!order.value?.id) {
        return;
    }
    removingId.value = itemId;
    try {
        const res = await ordersService.removeOrderItem(order.value.id, itemId);
        order.value = pickOrderPayload(res);
        emit("updated");
        toast.add({
            severity: "success",
            summary: t("OrderDetail.RemoveLineTitle"),
            life: 2000,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    } finally {
        removingId.value = null;
    }
}

function onHide() {
    loadError.value = null;
}

function openFullPage() {
    if (!order.value?.id) {
        return;
    }
    innerVisible.value = false;
    router.push(`/orders/${order.value.id}`);
}

watch(
    () => [props.visible, props.orderId],
    ([vis, oid]) => {
        if (vis && oid != null && oid !== "") {
            fetchOrder();
            if (!products.value.length) {
                loadProducts();
            }
        }
        if (!vis) {
            order.value = null;
            selectedProductId.value = null;
            addQuantity.value = 1;
        }
    },
    { immediate: true }
);
</script>
