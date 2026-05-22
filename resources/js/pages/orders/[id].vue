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
                        <AppIcon name="pi pi-arrow-left" aria-hidden="true" />
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
                    v-if="order && canEditOrderItems && isOrdering"
                    type="button"
                    size="large"
                    severity="secondary"
                    outlined
                    class="min-h-[48px]"
                    :label="$t('OrderDetail.EditItems')"
                    @click="editorVisible = true"
                >
                    <template #icon>
                        <AppIcon name="pi pi-pencil" />
                    </template>
                </Button>
                <Button
                    v-if="order && canEditOrderItems && isOrdering"
                    type="button"
                    size="large"
                    class="min-h-[48px]"
                    :loading="submittingTakeaway"
                    :disabled="submittingTakeaway"
                    :label="$t('OrderDetail.SubmitTakeaway')"
                    @click="submitTakeaway"
                >
                    <template #icon>
                        <AppIcon name="pi pi-check" />
                    </template>
                </Button>
                <Button
                    v-if="order?.items?.length"
                    type="button"
                    outlined
                    size="large"
                    severity="secondary"
                    :label="$t('OrderDetail.ViewBatchBreakdown')"
                    class="min-h-[48px]"
                    @click="patchPreviewVisible = true"
                >
                    <template #icon>
                        <AppIcon name="pi pi-list" />
                    </template>
                </Button>
                <Button
                    v-if="order"
                    type="button"
                    outlined
                    size="large"
                    :label="$t('OrdersList.PrintInvoice')"
                    class="min-h-[48px]"
                    as="router-link"
                    :to="`/orders/${order.id}/invoice`"
                >
                    <template #icon>
                        <AppIcon name="pi pi-print" />
                    </template>
                </Button>
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
                            v-if="!mergedOrderLines.length"
                            class="rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-400"
                        >
                            {{ $t("OrderDetail.EmptyItems") }}
                        </div>
                        <ul v-else class="space-y-3">
                            <li
                                v-for="(line, idx) in mergedOrderLines"
                                :key="line.key"
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
                                                    mergedLineImageKey(line, idx)
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
                                                    onMergedLineImageError(line, idx)
                                                "
                                            />
                                        </a>
                                        <div
                                            v-else
                                            class="flex h-24 w-24 items-center justify-center rounded-xl border border-dashed border-surface-300 bg-surface-50 sm:h-32 sm:w-32 dark:border-surface-600 dark:bg-surface-800/80"
                                            aria-hidden="true"
                                        >
                                            <AppIcon
                                                name="pi pi-image"
                                                class="text-2xl text-surface-400 sm:text-3xl dark:text-surface-500"
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
                                        <dl
                                            class="mt-2 grid grid-cols-1 gap-x-4 gap-y-1.5 rounded-lg border border-surface-200/80 bg-surface-50/80 p-3 text-xs text-surface-600 dark:border-surface-700 dark:bg-surface-800/40 dark:text-surface-400 sm:grid-cols-2"
                                        >
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-2 sm:col-span-2"
                                            >
                                                <dt class="font-medium text-surface-700 dark:text-surface-300">
                                                    {{ $t("OrderDetail.ItemSection") }}
                                                </dt>
                                                <dd class="text-end">
                                                    <template v-if="line.section_name || line.section_code">
                                                        {{ line.section_name || line.section_code }}
                                                    </template>
                                                    <template v-else-if="line.multiple_sections">
                                                        {{ $t("OrderDetail.SectionVarious") }}
                                                    </template>
                                                    <template v-else>
                                                        —
                                                    </template>
                                                </dd>
                                            </div>
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <dt class="font-medium text-surface-700 dark:text-surface-300">
                                                    {{ $t("OrderDetail.ItemBatch") }}
                                                </dt>
                                                <dd class="tabular-nums text-end">
                                                    {{ line.batch_label }}
                                                </dd>
                                            </div>
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <dt class="font-medium text-surface-700 dark:text-surface-300">
                                                    {{ $t("OrderDetail.ItemPrintStatus") }}
                                                </dt>
                                                <dd class="text-end">
                                                    <Tag
                                                        v-if="line.is_printed === true"
                                                        :value="$t('OrderDetail.ItemPrinted')"
                                                        severity="success"
                                                        class="text-xs"
                                                    />
                                                    <Tag
                                                        v-else-if="line.is_printed === 'mixed'"
                                                        :value="$t('OrderDetail.ItemPrintStatusMixed')"
                                                        severity="secondary"
                                                        class="text-xs"
                                                    />
                                                    <Tag
                                                        v-else
                                                        :value="$t('OrderDetail.ItemPendingPrint')"
                                                        severity="warn"
                                                        class="text-xs"
                                                    />
                                                </dd>
                                            </div>
                                            <div
                                                v-if="line.product_id != null"
                                                class="flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <dt class="font-medium text-surface-700 dark:text-surface-300">
                                                    {{ $t("OrderDetail.ItemProductId") }}
                                                </dt>
                                                <dd class="tabular-nums text-end">
                                                    {{ line.product_id }}
                                                </dd>
                                            </div>
                                            <div
                                                v-if="line.sourceLineCount > 1"
                                                class="flex flex-wrap items-center justify-between gap-2 sm:col-span-2"
                                            >
                                                <dt class="font-medium text-surface-700 dark:text-surface-300">
                                                    {{ $t("OrderDetail.MergedLinesCount") }}
                                                </dt>
                                                <dd class="tabular-nums text-end">
                                                    {{ line.sourceLineCount }}
                                                </dd>
                                            </div>
                                        </dl>
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
                            v-if="mergedOrderLines.length"
                            class="my-6"
                            layout="horizontal"
                        />

                        <div
                            v-if="mergedOrderLines.length"
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
        <OrderDetailDrawer
            v-model:visible="editorVisible"
            :order-id="orderId"
            @updated="fetchOrder"
        />

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
    </div>
</template>

<script setup>
import { Button } from "primevue";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import Divider from "primevue/divider";
import Message from "primevue/message";
import Skeleton from "primevue/skeleton";
import Tag from "primevue/tag";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import { ordersService } from "../../apis/services/orders/orders.apis";
import { OrderStatus } from "../../apis/services/orders/orders.type";
import OrderDetailDrawer from "../../components/pages/orders/OrderDetailDrawer.vue";
import { useUserStore } from "../../stores/user";
import { mergeOrderItems } from "../../utils/orderItemsMerge";
import {
    buildOperationPrintLabels,
    printOperationInvoice,
} from "../../components/pages/orders/printOperationInvoice";

const { t, locale } = useI18n();
const route = useRoute();
const toast = useToast();
const userStore = useUserStore();

const loading = ref(true);
const loadError = ref(null);
const order = ref(null);
const editorVisible = ref(false);
const submittingTakeaway = ref(false);
const patchPreviewVisible = ref(false);

const PENDING_PATCH_KEY = "__open__";
/** Auto-open editor once per visit for new takeaway orders. */
const hasAutoOpenedEditor = ref(false);
/** Hide thumbnails that fail to load. */
const lineImageFailed = reactive({});

const orderId = computed(() => String(route.params.id ?? ""));
const isOrdering = computed(() => order.value?.status === OrderStatus.Ordering);
const isTakeawayOrder = computed(
    () => order.value != null && order.value.unit_id == null
);
const canEditOrderItems = computed(() => userStore.hasPermission("order edit"));

const mergedOrderLines = computed(() => mergeOrderItems(order.value?.items));

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
            if (a[0] === PENDING_PATCH_KEY) return -1;
            if (b[0] === PENDING_PATCH_KEY) return 1;
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

watch(
    () => route.params.id,
    () => {
        hasAutoOpenedEditor.value = false;
        fetchOrder();
    }
);

function maybeOpenTakeawayEditor() {
    if (
        hasAutoOpenedEditor.value ||
        !canEditOrderItems.value ||
        !isOrdering.value ||
        !isTakeawayOrder.value
    ) {
        return;
    }
    hasAutoOpenedEditor.value = true;
    editorVisible.value = true;
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
        case OrderStatus.Active:
        case OrderStatus.Open:
        case OrderStatus.Ordering:
            return "success";
        case OrderStatus.Takeaway:
            return "info";
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

function lineTypeLabel(type) {
    const key = `OrderDetail.ItemTypes.${type}`;
    const translated = t(key);
    return translated === key ? type : translated;
}

function mergedLineImageKey(line, lineIdx) {
    return "m-" + line.key + "-" + lineIdx;
}

function onMergedLineImageError(line, lineIdx) {
    lineImageFailed[mergedLineImageKey(line, lineIdx)] = true;
}

async function submitTakeaway() {
    const id = orderId.value;
    if (!id || submittingTakeaway.value || !isOrdering.value) {
        return;
    }

    if (!window.confirm(t("OrderDetail.SubmitTakeawayConfirm"))) {
        return;
    }

    submittingTakeaway.value = true;
    try {
        const { data } = await ordersService.submitTakeawayOrder(id);
        order.value = data?.data ?? data;
        toast.add({
            severity: "success",
            summary: t("OrderDetail.SubmitTakeawaySuccess"),
            life: 3000,
        });
    } catch (error) {
        const message =
            error?.response?.data?.message ??
            t("OrderDetail.SubmitTakeawayError");
        toast.add({
            severity: "error",
            summary: message,
            life: 5000,
        });
    } finally {
        submittingTakeaway.value = false;
    }
}

function printOperationSection(section, batchNo) {
    printOperationInvoice(
        section,
        buildOperationPrintLabels(order.value, batchNo, section, t, locale.value),
    );
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
        maybeOpenTakeawayEditor();
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
