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
                        class="min-h-[40px]"
                        @click="openFullPage"
                    >
                        <template #icon>
                            <AppIcon name="hi-external-link" />
                        </template>
                    </Button>
                    <Button
                        v-if="order?.items?.length"
                        type="button"
                        outlined
                        size="small"
                        severity="secondary"
                        :label="$t('OrderDetail.ViewBatchBreakdown')"
                        class="min-h-[40px]"
                        @click="patchPreviewVisible = true"
                    >
                        <template #icon>
                            <AppIcon name="pi pi-list" />
                        </template>
                    </Button>
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
                                    :key="'quick-product-skeleton-' + idx"
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
                                            <AppIcon name="pi pi-image" class="text-lg text-surface-400 dark:text-surface-500" />
                                        </div>
                                        <p class="truncate text-sm font-semibold text-surface-900 dark:text-surface-50">
                                            {{ product.name ?? "—" }}
                                        </p>
                                        <p class="text-sm text-surface-700 dark:text-surface-300">
                                            {{ formatMoney(product.price) }}
                                        </p>
                                        <p
                                            v-if="isOutOfStock(product)"
                                            class="text-xs font-medium text-red-600 dark:text-red-400"
                                        >
                                            ({{ $t("OrderDetail.OutOfStock") }})
                                        </p>
                                    </button>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <span class="text-xs text-surface-600 dark:text-surface-400">Qty</span>
                                        <div class="flex items-center gap-1">
                                            <Button
                                                type="button"
                                                size="small"
                                                outlined
                                                :disabled="adding || isOutOfStock(product)"
                                                @click="adjustQuickQty(product, -1)"
                                            >
                                                <template #icon>
                                                    <AppIcon name="pi pi-minus" />
                                                </template>
                                            </Button>
                                            <span
                                                class="inline-flex min-w-8 justify-center rounded border border-surface-300 px-2 py-1 text-xs font-medium dark:border-surface-600"
                                            >
                                                {{ quickQty(product) }}
                                            </span>
                                            <Button
                                                type="button"
                                                size="small"
                                                outlined
                                                :disabled="adding || isOutOfStock(product)"
                                                @click="adjustQuickQty(product, 1)"
                                            >
                                                <template #icon>
                                                    <AppIcon name="pi pi-plus" />
                                                </template>
                                            </Button>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        size="small"
                                        label="Add"
                                        class="mt-2 w-full"
                                        :loading="adding && selectedProductId === product.id"
                                        :disabled="adding || isOutOfStock(product)"
                                        @click="onQuickAddProduct(product)"
                                    >
                                        <template #icon>
                                            <AppIcon name="pi pi-plus" />
                                        </template>
                                    </Button>
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

                            <Card
                                v-if="pendingOrderItems.length"
                                class="overflow-hidden rounded-xl border border-orange-200/80 shadow-sm dark:border-orange-700/70"
                            >
                                <template #title>
                                    <span class="text-base font-semibold">{{ $t("OrderDetail.OrderBatchTitle") }}</span>
                                </template>
                                <template #content>
                                    <div class="space-y-4">
                                        <div
                                            v-for="patch in pendingPatches"
                                            :key="'pending-patch-' + patch.batchKey"
                                            class="overflow-hidden rounded-lg border border-orange-200 dark:border-orange-700/70"
                                        >
                                            <div
                                                class="bg-orange-100/90 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-orange-950 dark:bg-orange-900/50 dark:text-orange-100"
                                            >
                                                {{
                                                    patch.isOpenPatch
                                                        ? $t("OrderDetail.BatchOpenLabel")
                                                        : $t("OrderDetail.BatchLabel", { n: patch.batch_no })
                                                }}
                                            </div>
                                            <div
                                                class="sticky top-0 z-10 grid grid-cols-[2.5rem_minmax(0,1.4fr)_4rem_5rem] items-center gap-1 bg-orange-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-orange-900 dark:bg-orange-900/40 dark:text-orange-100"
                                            >
                                                <span class="sr-only">{{ $t("OrderDetail.LineActionsColumn") }}</span>
                                                <span>Product</span>
                                                <span class="text-center">Qty</span>
                                                <span class="text-end">Batch</span>
                                            </div>
                                            <div
                                                v-for="(line, idx) in patch.items"
                                                :key="line.id ?? ('pending-line-' + patch.batchKey + '-' + idx)"
                                                class="grid grid-cols-[2.5rem_minmax(0,1.4fr)_4rem_5rem] items-center gap-1 border-t border-orange-200 px-3 py-2 text-sm dark:border-orange-700/70"
                                            >
                                                <div class="flex justify-center">
                                                    <Button
                                                        v-if="canEditItems && canDeleteOrderItems && line.id != null"
                                                        type="button"
                                                        size="small"
                                                        text
                                                        severity="danger"
                                                        :loading="removingId === line.id"
                                                        :disabled="removingId != null && removingId !== line.id"
                                                        @click="confirmRemoveLine(line)"
                                                    >
                                                        <template #icon>
                                                            <AppIcon name="pi pi-trash" />
                                                        </template>
                                                    </Button>
                                                </div>
                                                <div class="min-w-0 truncate font-medium text-surface-900 dark:text-surface-100">
                                                    {{ line.name ?? "—" }}
                                                </div>
                                                <div class="text-center tabular-nums">{{ line.quantity ?? "—" }}</div>
                                                <div class="text-end tabular-nums text-surface-700 dark:text-surface-300">
                                                    {{ line.batch_no ?? "—" }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end border-t border-orange-200 pt-3 dark:border-orange-700/70">
                                        <Button
                                            type="button"
                                            size="small"
                                            :label="$t('OrderDetail.ConfirmBatchInCard')"
                                            :loading="printing"
                                            :disabled="printing"
                                            @click="confirmOperationInvoice"
                                        >
                                            <template #icon>
                                                <AppIcon name="hi-printer" />
                                            </template>
                                        </Button>
                                    </div>
                                </template>
                            </Card>

                            <Card class="overflow-hidden rounded-xl border border-surface-200/80 shadow-sm dark:border-surface-700">
                                <template #title>
                                    <span class="text-base font-semibold">{{ $t("OrderDetail.OtherOrderItemsTitle") }}</span>
                                </template>
                                <template #content>
                                    <div
                                        v-if="!mergedPrintedOrderItems.length"
                                        class="rounded-xl border border-dashed border-surface-300 p-6 text-center text-sm text-surface-600 dark:border-surface-600 dark:text-surface-400"
                                    >
                                        {{ $t("OrderDetail.EmptyItems") }}
                                    </div>
                                    <div v-else class="overflow-hidden rounded-lg border border-surface-200 dark:border-surface-700">
                                        <div
                                            class="sticky top-0 z-10 grid grid-cols-[2.5rem_minmax(0,1.4fr)_minmax(0,1fr)_4rem] items-center gap-1 bg-surface-100 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-surface-700 dark:bg-surface-800 dark:text-surface-300"
                                        >
                                            <span class="sr-only">{{ $t("OrderDetail.LineActionsColumn") }}</span>
                                            <span>Product</span>
                                            <span class="text-end">Unit Price</span>
                                            <span class="text-center">Qty</span>
                                        </div>
                                        <div
                                            v-for="(line, idx) in mergedPrintedOrderItems"
                                            :key="line.key ?? ('merged-line-' + idx)"
                                            class="grid grid-cols-[2.5rem_minmax(0,1.4fr)_minmax(0,1fr)_4rem] items-center gap-1 border-t border-surface-200 px-3 py-2 text-sm dark:border-surface-700"
                                        >
                                            <div class="flex justify-center">
                                                <Button
                                                    v-if="canEditItems && canDeleteOrderItems && line.canRemove && line.removeId != null"
                                                    type="button"
                                                    size="small"
                                                    text
                                                    severity="danger"
                                                    :loading="removingId === line.removeId"
                                                    :disabled="removingId != null && removingId !== line.removeId"
                                                    @click="confirmRemoveLine({ id: line.removeId })"
                                                >
                                                    <template #icon>
                                                        <AppIcon name="pi pi-trash" />
                                                    </template>
                                                </Button>
                                            </div>
                                            <div class="min-w-0 truncate font-medium text-surface-900 dark:text-surface-100">
                                                {{ line.name ?? "—" }}
                                            </div>
                                            <div class="text-end text-surface-700 dark:text-surface-300">
                                                {{ formatMoney(line.price) }}
                                            </div>
                                            <div class="text-center tabular-nums">{{ line.quantity ?? "—" }}</div>
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

        <Dialog
            v-model:visible="patchPreviewVisible"
            modal
            :header="$t('OrderDetail.BatchPreviewTitle')"
            :style="{ width: 'min(96vw, 42rem)' }"
        >
            <div v-if="!patchPreviewPatches.length" class="text-sm text-surface-600 dark:text-surface-300">
                {{ $t("OrderDetail.EmptyItems") }}
            </div>
            <div v-else class="max-h-[min(70vh,520px)] space-y-4 overflow-y-auto pe-1">
                <div
                    v-for="patch in patchPreviewPatches"
                    :key="'pv-patch-' + patch.batchKey"
                    class="rounded-xl border border-surface-200 p-3 dark:border-surface-700"
                >
                    <p class="mb-3 text-sm font-semibold text-surface-900 dark:text-surface-100">
                        {{
                            patch.isOpenPatch
                                ? $t("OrderDetail.BatchOpenLabel")
                                : $t("OrderDetail.BatchLabel", { n: patch.batch_no })
                        }}
                    </p>
                    <div class="space-y-3">
                        <article
                            v-for="section in patch.sections"
                            :key="'pv-' + patch.batchKey + '-' + section.section_code"
                            class="rounded-lg border border-surface-200/90 p-3 dark:border-surface-600"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-surface-900 dark:text-surface-100">
                                        {{ section.section_name || section.section_code }}
                                    </p>
                                    <p class="text-xs text-surface-500 dark:text-surface-400">
                                        {{ section.items.length }} item(s)
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    size="small"
                                    outlined
                                    :label="$t('OrderDetail.PrintSectionAction')"
                                    @click="printOperationSection(section, patch.batch_no)"
                                />
                            </div>
                            <ul class="space-y-1 text-sm">
                                <li
                                    v-for="row in section.items"
                                    :key="row.id"
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span class="truncate">{{ row.name || "—" }}</span>
                                    <span class="shrink-0 tabular-nums">x{{ row.quantity ?? "—" }}</span>
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="operationInvoiceVisible"
            modal
            :header="$t('OrderDetail.SubmitBatchResultTitle')"
            :style="{ width: 'min(96vw, 42rem)' }"
        >
            <div v-if="!operationPatches.length" class="text-sm text-surface-600 dark:text-surface-300">
                {{ $t("OrderDetail.PrintBySectionNothing") }}
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="patch in operationPatches"
                    :key="'op-patch-' + (patch.batch_no ?? 'open')"
                    class="rounded-xl border border-surface-200 p-3 dark:border-surface-700"
                >
                    <p class="mb-3 text-sm font-semibold text-surface-900 dark:text-surface-100">
                        {{
                            patch.batch_no != null
                                ? $t("OrderDetail.BatchLabel", { n: patch.batch_no })
                                : $t("OrderDetail.BatchOpenLabel")
                        }}
                    </p>
                    <div class="space-y-3">
                        <article
                            v-for="section in patch.sections"
                            :key="(patch.batch_no ?? 'open') + '-' + section.section_code"
                            class="rounded-lg border border-surface-200/90 p-3 dark:border-surface-600"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-surface-900 dark:text-surface-100">
                                        {{ section.section_name || section.section_code }}
                                    </p>
                                    <p class="text-xs text-surface-500 dark:text-surface-400">
                                        {{ section.items.length }} item(s)
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    size="small"
                                    outlined
                                    :label="$t('OrderDetail.PrintSectionAction')"
                                    @click="printOperationSection(section, patch.batch_no)"
                                />
                            </div>
                            <ul class="space-y-1 text-sm">
                                <li v-for="line in section.items" :key="line.id" class="flex items-center justify-between gap-2">
                                    <span class="truncate">{{ line.name || "—" }}</span>
                                    <span class="shrink-0 tabular-nums">x{{ line.quantity ?? "—" }}</span>
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
        </Dialog>
    </Drawer>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
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
import { useUserStore } from "../../../stores/user";

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
const userStore = useUserStore();

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
const printing = ref(false);
const quickQuantities = reactive({});
const operationInvoiceVisible = ref(false);
const operationPatches = ref([]);
const patchPreviewVisible = ref(false);

const canEditItems = computed(
    () =>
        order.value &&
        (order.value.status === OrderStatus.Active ||
            order.value.status === OrderStatus.Open)
);
const canDeleteOrderItems = computed(() => userStore.hasPermission("order item delete"));
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
const pendingOrderItems = computed(() =>
    (order.value?.items ?? []).filter((line) => !line?.is_printed)
);
const printedOrderItems = computed(() =>
    (order.value?.items ?? []).filter((line) => !!line?.is_printed)
);

/** Unprinted lines grouped by batch (one patch can include multiple lines). */
const PENDING_PATCH_KEY = "__open__";

const pendingPatches = computed(() => {
    const byBatch = new Map();
    for (const line of pendingOrderItems.value) {
        const b = line.batch_no == null ? PENDING_PATCH_KEY : line.batch_no;
        if (!byBatch.has(b)) {
            byBatch.set(b, []);
        }
        byBatch.get(b).push(line);
    }
    return [...byBatch.entries()]
        .sort((a, b) => {
            if (a[0] === PENDING_PATCH_KEY) {
                return -1;
            }
            if (b[0] === PENDING_PATCH_KEY) {
                return 1;
            }
            return a[0] - b[0];
        })
        .map(([batchKey, items]) => ({
            batchKey,
            batch_no: batchKey === PENDING_PATCH_KEY ? null : batchKey,
            isOpenPatch: batchKey === PENDING_PATCH_KEY,
            items,
        }));
});

/** All lines: patch → section (read-only preview modal). */
const patchPreviewPatches = computed(() => {
    const items = order.value?.items ?? [];
    if (!items.length) {
        return [];
    }
    const byBatch = new Map();
    for (const line of items) {
        const b = line.batch_no == null ? PENDING_PATCH_KEY : line.batch_no;
        if (!byBatch.has(b)) {
            byBatch.set(b, []);
        }
        byBatch.get(b).push(line);
    }
    return [...byBatch.entries()]
        .sort((a, b) => {
            if (a[0] === PENDING_PATCH_KEY) {
                return -1;
            }
            if (b[0] === PENDING_PATCH_KEY) {
                return 1;
            }
            return a[0] - b[0];
        })
        .map(([batchKey, lines]) => {
            const bySec = new Map();
            for (const line of lines) {
                const code = line.section_code || "general";
                if (!bySec.has(code)) {
                    bySec.set(code, {
                        section_code: code,
                        section_name: line.section_name || (code === "general" ? "General" : code),
                        items: [],
                    });
                }
                bySec.get(code).items.push({
                    id: line.id,
                    name: line.name,
                    quantity: line.quantity,
                    notes: line.notes ?? null,
                });
            }
            return {
                batchKey,
                batch_no: batchKey === PENDING_PATCH_KEY ? null : batchKey,
                isOpenPatch: batchKey === PENDING_PATCH_KEY,
                sections: [...bySec.values()],
            };
        });
});

/** Printed lines merged by product + price + name (qty and totals summed). */
const mergedPrintedOrderItems = computed(() => {
    const map = new Map();
    for (const line of printedOrderItems.value) {
        const key = `${line.product_id ?? "n"}|${String(line.price ?? "")}|${String(line.name ?? "")}`;
        const qty = Number(line.quantity ?? 0) || 0;
        const lineTotal = Number(line.total);
        const t = Number.isFinite(lineTotal) ? lineTotal : 0;
        let g = map.get(key);
        if (!g) {
            g = {
                key,
                name: line.name,
                price: line.price,
                quantity: 0,
                total: 0,
                sourceIds: [],
            };
            map.set(key, g);
        }
        g.quantity += qty;
        g.total += t;
        if (line.id != null) {
            g.sourceIds.push(line.id);
        }
    }
    return [...map.values()].map((g) => ({
        key: g.key,
        name: g.name,
        price: g.price,
        quantity: g.quantity,
        total: g.total,
        canRemove: g.sourceIds.length === 1,
        removeId: g.sourceIds[0],
    }));
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
    const maxQty = availableStock(product);
    const clampedMin = next < 1 ? 1 : next;
    quickQuantities[key] = maxQty == null ? clampedMin : Math.min(clampedMin, Math.max(1, maxQty));
}

function availableStock(product) {
    if (!product?.is_limited) {
        return null;
    }
    const stock = Number(product?.stock_quantity ?? 0);
    if (!Number.isFinite(stock)) {
        return 0;
    }
    return Math.max(0, Math.floor(stock));
}

function isOutOfStock(product) {
    const stock = availableStock(product);
    return stock !== null && stock < 1;
}

function decreaseLocalProductStock(productId, quantity) {
    const product = products.value.find((item) => item?.id === productId);
    if (!product?.is_limited) {
        return;
    }
    const current = Number(product.stock_quantity ?? 0);
    if (!Number.isFinite(current)) {
        product.stock_quantity = 0;
        return;
    }
    product.stock_quantity = Math.max(0, Math.floor(current) - quantity);

    const key = String(productId ?? "");
    if (key) {
        const currentQty = quickQty(product);
        const maxQty = availableStock(product);
        if (maxQty !== null && currentQty > maxQty) {
            quickQuantities[key] = maxQty < 1 ? 1 : maxQty;
        }
    }
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
    const targetProduct = products.value.find((item) => item?.id === resolvedProductId);
    if (targetProduct && targetProduct.is_limited) {
        const stock = availableStock(targetProduct);
        if (stock !== null && stock < qty) {
            toast.add({
                severity: "warn",
                summary: t("OrderDetail.OutOfStock"),
                life: 3000,
            });
            return;
        }
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
        decreaseLocalProductStock(resolvedProductId, qty);
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

async function confirmOperationInvoice() {
    if (!order.value?.id || printing.value) {
        return;
    }
    confirm.require({
        message: t("OrderDetail.SubmitBatchConfirmMessage"),
        header: t("OrderDetail.SubmitBatchConfirmTitle"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: t("Cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: t("OrderDetail.SubmitBatchConfirmAction"),
            severity: "primary",
        },
        accept: () => processOperationInvoice(),
    });
}

async function processOperationInvoice() {
    if (!order.value?.id || printing.value) {
        return;
    }

    printing.value = true;
    try {
        const { data } = await ordersService.printOrder(order.value.id);
        const payload = data?.data ?? data;
        const printedCount = Number(payload?.printed_items_count ?? 0);
        const sectionsCount = Array.isArray(payload?.sections)
            ? payload.sections.length
            : 0;

        toast.add({
            severity: "success",
            summary:
                printedCount > 0
                    ? t("OrderDetail.PrintBySectionSuccess", {
                          sections: sectionsCount,
                          count: printedCount,
                      })
                    : t("OrderDetail.PrintBySectionNothing"),
            life: 2500,
        });

        let patches = Array.isArray(payload?.patches) ? payload.patches : [];
        if (!patches.length && Array.isArray(payload?.sections) && payload.sections.length) {
            patches = [{ batch_no: 1, sections: payload.sections }];
        }
        operationPatches.value = patches;
        operationInvoiceVisible.value = patches.length > 0;
        await fetchOrder();
        emit("updated");
    } catch {
        toast.add({
            severity: "error",
            summary: t("OrderDetail.PrintBySectionError"),
            life: 4000,
        });
    } finally {
        printing.value = false;
    }
}

function printOperationSection(section, batchNo) {
    const w = window.open("", "_blank", "width=520,height=760");
    if (!w) {
        return;
    }
    const sectionTitle = section?.section_name || section?.section_code || "Section";
    const title =
        batchNo != null ? `${t("OrderDetail.BatchLabel", { n: batchNo })} · ${sectionTitle}` : sectionTitle;
    const rows = (section?.items ?? [])
        .map((line) => {
            const name = String(line?.name ?? "—");
            const qty = String(line?.quantity ?? "—");
            const notes = line?.notes ? `<div style="font-size:11px;color:#666;">${String(line.notes)}</div>` : "";
            return `<tr><td style="padding:8px 6px;border-bottom:1px solid #ddd;">${name}${notes}</td><td style="padding:8px 6px;border-bottom:1px solid #ddd;text-align:center;width:72px;">${qty}</td></tr>`;
        })
        .join("");
    const html = `<!doctype html><html><head><meta charset="utf-8"><title>${title}</title></head><body style="font-family:Arial,sans-serif;padding:16px"><h3 style="margin:0 0 10px;">${title}</h3><table style="width:100%;border-collapse:collapse"><thead><tr><th style="text-align:left;padding:8px 6px;border-bottom:1px solid #000">Item</th><th style="text-align:center;padding:8px 6px;border-bottom:1px solid #000">Qty</th></tr></thead><tbody>${rows || '<tr><td colspan="2" style="padding:8px 6px;text-align:center;color:#666">—</td></tr>'}</tbody></table><scr` + `ipt>window.onload=function(){window.focus();window.print();}</scr` + `ipt></body></html>`;
    w.document.open();
    w.document.write(html);
    w.document.close();
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
