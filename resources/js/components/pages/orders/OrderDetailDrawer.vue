<template>
    <Drawer
        v-model:visible="innerVisible"
        position="right"
        class="order-detail-drawer !w-full sm:!max-w-[min(100vw-0.5rem,64rem)]"
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
            <div class="min-h-0 flex-1 overflow-hidden overscroll-contain px-4 pb-6 pt-0">
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
                    <div class="grid gap-4 lg:h-full lg:grid-cols-[minmax(0,1fr)_480px] lg:items-start">
                        <section
                            v-if="canEditItems"
                            class="rounded-xl border border-surface-200 bg-surface-50 p-3 pt-0 dark:border-surface-600 dark:bg-surface-900/40 max-h-full overflow-y-auto pe-2"
                        >
                            <div class="sticky top-0 z-10 pt-3 bg-surface-50 dark:bg-surface-900/40">
                                <p class="mb-2 text-sm font-semibold text-surface-900 dark:text-surface-100">
                                    Product Catalog
                                </p>
                                <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                                    <Button
                                        v-for="tab in productCategoryTabs"
                                        :key="tab.key"
                                        type="button"
                                        size="small"
                                        rounded
                                        :outlined="activeProductCategory !== tab.key"
                                        :severity="activeProductCategory === tab.key ? 'primary' : 'secondary'"
                                        class="whitespace-nowrap"
                                        :label="tab.label"
                                        @click="activeProductCategory = tab.key"
                                    />
                                </div>
                            </div>


                            <div v-if="productsLoading" class="grid lg:grid-cols-2 grid-cols-3 gap-3">
                                <Skeleton
                                    v-for="idx in 6"
                                    :key="`quick-product-skeleton-${idx}`"
                                    height="10rem"
                                    class="rounded-lg"
                                />
                            </div>
                            <div
                                v-else-if="!filteredProducts.length"
                                class="rounded-xl border border-dashed border-surface-300 p-4 text-center text-sm text-surface-600 dark:border-surface-600 dark:text-surface-400"
                            >
                                {{ $t("OrderDetail.EmptyItems") }}
                            </div>
                            <div v-else class="grid lg:grid-cols-2 grid-cols-3 gap-3">
                                <article
                                    v-for="product in filteredProducts"
                                    :key="product.id"
                                    class="rounded-lg border border-surface-300 bg-white p-2 dark:border-surface-600 dark:bg-surface-900"
                                >
                                    <button
                                        type="button"
                                        class="mb-2 block w-full text-start"
                                        @click="selectQuickProduct(product)"
                                    >
                                        <img
                                            v-if="productImageUrl(product)"
                                            :src="productImageUrl(product)"
                                            :alt="product.name || ''"
                                            class="mb-2 aspect-video w-full rounded-md object-cover"
                                            loading="lazy"
                                        />
                                        <div
                                            v-else
                                            class="mb-2 flex aspect-video w-full items-center justify-center rounded-md border border-dashed border-surface-300 bg-surface-50 dark:border-surface-600 dark:bg-surface-800/80"
                                            aria-hidden="true"
                                        >
                                            <i class="pi pi-image text-lg text-surface-400 dark:text-surface-500" />
                                        </div>
                                        <p class="truncate text-sm font-semibold text-surface-900 dark:text-surface-50">
                                            {{ product.name ?? "—" }}
                                        </p>
                                        <p class="text-sm text-surface-700 dark:text-surface-300">
                                            {{ formatMoney(product.price) }}
                                        </p>
                                    </button>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <span class="text-xs text-surface-600 dark:text-surface-400">Qty</span>
                                        <div class="flex items-center gap-1">
                                            <Button
                                                type="button"
                                                size="small"
                                                outlined
                                                icon="pi pi-minus"
                                                :disabled="adding"
                                                @click="adjustQuickQty(product, -1)"
                                            />
                                            <span
                                                class="inline-flex min-w-8 justify-center rounded border border-surface-300 px-2 py-1 text-xs font-medium dark:border-surface-600"
                                            >
                                                {{ quickQty(product) }}
                                            </span>
                                            <Button
                                                type="button"
                                                size="small"
                                                outlined
                                                icon="pi pi-plus"
                                                :disabled="adding"
                                                @click="adjustQuickQty(product, 1)"
                                            />
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        size="small"
                                        icon="pi pi-plus"
                                        label="Add"
                                        class="mt-2 w-full"
                                        :loading="adding && selectedProductId === product.id"
                                        :disabled="adding"
                                        @click="onQuickAddProduct(product)"
                                    />
                                </article>
                            </div>
                        </section>

                        <Message v-else severity="info" :closable="false">
                            {{ $t("OrderDetail.NotEditable") }}
                        </Message>

                        <section class="space-y-4 lg:sticky lg:top-0 lg:max-h-full lg:overflow-y-auto">
                            <Card
                                class="overflow-hidden rounded-xl border border-surface-200/80 shadow-sm dark:border-surface-700 xl:sticky xl:top-0 xl:z-20"
                            >
                                <template #title>
                                    <span class="text-base font-semibold">{{ $t("OrderDetail.SummaryTitle") }}</span>
                                </template>
                                <template #content>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700">
                                            <dt class="text-surface-600 dark:text-surface-400">{{ $t("OrdersList.ColumnTotal") }}</dt>
                                            <dd class="text-lg font-semibold tabular-nums text-surface-900 dark:text-surface-50">{{ formatMoney(order.total) }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700">
                                            <dt class="text-surface-600 dark:text-surface-400">{{ $t("OrderDetail.OpenedAt") }}</dt>
                                            <dd class="text-end">{{ formatDateTime(order.opened_at) }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-surface-200 pb-2 dark:border-surface-700">
                                            <dt class="text-surface-600 dark:text-surface-400">{{ $t("OrdersList.ColumnUnit") }}</dt>
                                            <dd class="text-end font-medium">{{ order.unit_name ?? order.unit_id ?? "—" }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-surface-600 dark:text-surface-400">{{ $t("OrdersList.ColumnStatus") }}</dt>
                                            <dd>
                                                <Tag v-if="order.status" :value="statusLabel(order.status)" :severity="statusSeverity(order.status)" class="capitalize" />
                                            </dd>
                                        </div>
                                    </dl>
                                </template>
                            </Card>

                            <Card class="overflow-hidden rounded-xl border border-surface-200/80 shadow-sm dark:border-surface-700">
                                <template #title>
                                    <span class="text-base font-semibold">{{ $t("OrderDetail.LineItemsTitle") }}</span>
                                </template>
                                <template #content>
                                    <div
                                        v-if="!order.items?.length"
                                        class="rounded-xl border border-dashed border-surface-300 p-6 text-center text-sm text-surface-600 dark:border-surface-600 dark:text-surface-400"
                                    >
                                        {{ $t("OrderDetail.EmptyItems") }}
                                    </div>
                                    <div v-else class="overflow-hidden rounded-lg border border-surface-200 dark:border-surface-700">
                                        <div
                                            class="sticky top-0 z-10 grid grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_7rem_8rem] bg-surface-100 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-surface-700 dark:bg-surface-800 dark:text-surface-300"
                                        >
                                            <span>Product</span>
                                            <span class="text-end">Unit Price</span>
                                            <span class="text-center">Qty</span>
                                            <span class="text-end">Subtotal</span>
                                        </div>
                                        <div
                                            v-for="(line, idx) in order.items"
                                            :key="line.id ?? `line-${idx}`"
                                            class="grid grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_7rem_8rem] items-center border-t border-surface-200 px-3 py-2 text-sm dark:border-surface-700"
                                        >
                                            <div class="truncate font-medium text-surface-900 dark:text-surface-100">
                                                {{ line.name ?? "—" }}
                                            </div>
                                            <div class="text-end text-surface-700 dark:text-surface-300">
                                                {{ formatMoney(line.price) }}
                                            </div>
                                            <div class="flex items-center justify-center gap-1">
                                                <Button
                                                    v-if="canEditItems && line.id != null"
                                                    type="button"
                                                    size="small"
                                                    text
                                                    icon="pi pi-trash"
                                                    severity="danger"
                                                    :loading="removingId === line.id"
                                                    :disabled="removingId != null && removingId !== line.id"
                                                    @click="confirmRemoveLine(line)"
                                                />
                                                <span class="min-w-6 text-center tabular-nums">{{ line.quantity ?? "—" }}</span>
                                            </div>
                                            <div class="text-end font-semibold tabular-nums text-surface-900 dark:text-surface-50">
                                                {{ formatMoney(line.total) }}
                                            </div>
                                        </div>
                                        <div
                                            class="sticky bottom-0 z-10 flex items-center justify-between border-t border-surface-200 bg-surface-50 px-3 py-2 dark:border-surface-700 dark:bg-surface-800/95"
                                        >
                                            <span class="font-medium">{{ $t("OrderDetail.GrandTotal") }}</span>
                                            <span class="text-lg font-bold tabular-nums">{{ formatMoney(order.total) }}</span>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>
                    </div>
                </template>
            </div>
        </div>
    </Drawer>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Drawer from "primevue/drawer";
import Message from "primevue/message";
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
const quickQuantities = reactive({});

const canEditItems = computed(
    () =>
        order.value &&
        (order.value.status === OrderStatus.Active ||
            order.value.status === OrderStatus.Open)
);
const activeProductCategory = ref("all");
const productCategoryTabs = computed(() => {
    const tabs = [{ key: "all", label: "All" }];
    const seen = new Set();
    products.value.forEach((product) => {
        (product?.categories ?? []).forEach((category) => {
            const key = String(category?.id ?? "").trim();
            if (!key || seen.has(key)) {
                return;
            }
            seen.add(key);
            tabs.push({ key, label: category?.name || `#${key}` });
        });
    });
    return tabs;
});
const filteredProducts = computed(() => {
    if (activeProductCategory.value === "all") {
        return products.value;
    }
    return products.value.filter((product) =>
        (product?.categories ?? []).some(
            (category) => String(category?.id ?? "").trim() === activeProductCategory.value
        )
    );
});

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

function productImageUrl(product) {
    const media = Array.isArray(product?.media) ? product.media : [];
    const image =
        media.find((item) => item?.is_default && item?.url) || media.find((item) => item?.url);
    return image?.url || null;
}

function selectQuickProduct(product) {
    if (product?.id == null) {
        return;
    }
    selectedProductId.value = product.id;
}

function quickQty(product) {
    const key = String(product?.id ?? "");
    const value = Number(quickQuantities[key] ?? 1);
    return Number.isFinite(value) && value > 0 ? value : 1;
}

function adjustQuickQty(product, delta) {
    const key = String(product?.id ?? "");
    if (!key) {
        return;
    }
    const next = quickQty(product) + delta;
    quickQuantities[key] = next < 1 ? 1 : next;
}

async function onAddProduct(overrideProductId = null, overrideQuantity = null) {
    const resolvedProductId = overrideProductId ?? selectedProductId.value;
    if (!order.value?.id || resolvedProductId == null) {
        return;
    }
    const qtySource = overrideQuantity ?? addQuantity.value;
    const qty = qtySource != null ? Number(qtySource) : 1;
    if (Number.isNaN(qty) || qty < 1) {
        return;
    }

    adding.value = true;
    try {
        const res = await ordersService.addOrderItem(order.value.id, {
            product_id: resolvedProductId,
            quantity: qty,
        });
        order.value = pickOrderPayload(res);
        if (overrideProductId == null) {
            selectedProductId.value = null;
            addQuantity.value = 1;
        }
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

async function onQuickAddProduct(product) {
    if (product?.id == null) {
        return;
    }
    const qty = quickQty(product);
    selectedProductId.value = product.id;
    await onAddProduct(product.id, qty);
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

watch(
    productCategoryTabs,
    (tabs) => {
        if (!tabs.some((tab) => tab.key === activeProductCategory.value)) {
            activeProductCategory.value = "all";
        }
    },
    { immediate: true }
);
</script>
