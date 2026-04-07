<template>
    <div class="overflow-hidden rounded-2xl border border-surface-200 bg-white dark:border-surface-700 dark:bg-surface-800">
        <div class="flex items-center justify-between gap-3 px-4 py-3" :class="cardHeaderClass">
            <div class="min-w-0 flex items-center gap-2">
                <p class="truncate text-xl font-semibold text-surface-900 dark:text-surface-0">
                    {{ unit.name || "-" }}
                </p>
                <span
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-surface-800/90 dark:text-surface-100 border border-surface-300 dark:border-surface-600"
                >
                    <AppIcon :name="typeIconClass" class="!text-sm" />
                    {{ $t(`Units.Types.${typeLabel}`) }}
                </span>
            </div>
            <Button
                type="button"
                size="small"
                rounded
                text
                severity="secondary"
                :aria-label="$t('UnitsManagement.more')"
                :disabled="!menuItems.length"
                @click="toggleMenu"
            >
                <template #icon>
                    <AppIcon name="pi pi-ellipsis-h" />
                </template>
            </Button>
            <Menu
                ref="menuRef"
                :model="menuItems"
                popup
                :pt="touchMenuPt"
            />
        </div>

        <div class="space-y-4 px-4 py-4">
            <template v-if="statusTagKey === 'occupied'">
                <div class="flex items-center gap-3">
                    <AppIcon name="pi pi-clock" class="!text-3xl text-orange-600" />
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-surface-600 dark:text-surface-300">
                            {{ $t("UnitsManagement.card.duration") }}
                        </p>
                        <p class="text-3xl font-bold leading-none text-surface-900 dark:text-surface-0">
                            {{ occupiedDurationLabel }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <AppIcon name="pi pi-receipt" class="!text-3xl text-rose-600" />
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-surface-600 dark:text-surface-300">
                            {{ $t("UnitsManagement.card.totalOrder") }}
                        </p>
                        <p class="text-3xl font-bold leading-none text-surface-900 dark:text-surface-0">
                            {{ orderTotalLabel }}
                        </p>
                    </div>
                </div>
            </template>

            <template v-else-if="statusTagKey === 'reserved'">
                <div class="flex items-center gap-3">
                    <AppIcon name="pi pi-clock" class="!text-3xl text-orange-600" />
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-surface-600 dark:text-surface-300">
                            {{ $t("UnitsManagement.card.reservationTime") }}
                        </p>
                        <p class="text-3xl font-bold leading-none text-surface-900 dark:text-surface-0">
                            {{ reservedTimeLabel }}
                        </p>
                    </div>
                </div>
                <p class="text-xl text-surface-700 dark:text-surface-200">
                    <span class="font-medium">{{ $t("UnitsManagement.card.bookedBy") }}:</span>
                    {{ reservedByLabel }}
                </p>
            </template>

            <template v-else>
                <div class="flex items-center gap-3">
                    <AppIcon name="pi pi-users" class="!text-3xl text-teal-600" />
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-surface-600 dark:text-surface-300">
                            {{ $t("UnitsManagement.card.capacity") }}
                        </p>
                        <p class="text-3xl font-bold leading-none text-surface-900 dark:text-surface-0">
                            {{ capacityLabel }}
                        </p>
                    </div>
                </div>
                <p class="text-xl text-surface-700 dark:text-surface-200">
                    {{ $t("UnitsManagement.card.readyForSeating") }}
                </p>
            </template>

            <div>
                <span
                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                    :class="statusChipClass"
                >
                    {{ statusTagDisplay }}
                </span>
            </div>
        </div>

        <OrderDetailDrawer
            v-model:visible="orderDrawerVisible"
            :order-id="orderDrawerOrderId"
            @updated="notifyAction"
        />

        <Dialog
            v-model:visible="reservationModalVisible"
            modal
            :header="
                reservationEditMode
                    ? $t('UnitsManagement.actions.editReservationTime')
                    : $t('UnitsManagement.actions.addReservation')
            "
            class="w-[min(100vw-2rem,28rem)]"
        >
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-surface-700 dark:text-surface-200">
                        {{ $t("UnitsManagement.reservationCustomerName") }}
                    </label>
                    <input
                        v-model.trim="reservationCustomerName"
                        type="text"
                        class="h-12 w-full rounded-md border border-surface-300 bg-surface-0 px-3 text-base text-surface-900 outline-none transition focus:border-primary dark:border-surface-600 dark:bg-surface-900 dark:text-surface-0"
                        :placeholder="$t('UnitsManagement.reservationCustomerName')"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-surface-700 dark:text-surface-200">
                        {{ $t("UnitsManagement.reservationDateTime") }}
                    </label>
                    <input
                        v-model="reservationDateTime"
                        type="datetime-local"
                        step="300"
                        class="h-12 w-full rounded-md border border-surface-300 bg-surface-0 px-3 text-base text-surface-900 outline-none transition focus:border-primary dark:border-surface-600 dark:bg-surface-900 dark:text-surface-0"
                    />
                    <div class="grid grid-cols-3 gap-2">
                        <Button
                            type="button"
                            size="small"
                            outlined
                            label="-15m"
                            @click="shiftReservationMinutes(-15)"
                        />
                        <Button
                            type="button"
                            size="small"
                            outlined
                            label="+15m"
                            @click="shiftReservationMinutes(15)"
                        />
                        <Button
                            type="button"
                            size="small"
                            outlined
                            label="+30m"
                            @click="shiftReservationMinutes(30)"
                        />
                    </div>
                </div>

                <Button
                    type="button"
                    :label="
                        reservationEditMode
                            ? $t('UnitsManagement.actions.editReservationTime')
                            : $t('UnitsManagement.submitReservation')
                    "
                    :loading="reservationSubmitting"
                    :disabled="!reservationDateTime || reservationUnitId == null"
                    @click="submitReservation"
                >
                    <template #icon>
                        <AppIcon name="pi pi-check" />
                    </template>
                </Button>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="transferModalVisible"
            modal
            :header="$t('UnitsManagement.actions.transferGuests')"
            class="w-[min(100vw-2rem,30rem)]"
        >
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-surface-700 dark:text-surface-200">
                        {{ $t("UnitsManagement.selectTransferUnit") }}
                    </label>
                    <Select
                        v-model="transferTargetUnitId"
                        :options="transferUnitOptions"
                        option-label="label"
                        option-value="value"
                        :loading="transferLoading"
                        class="w-full"
                        size="large"
                        :placeholder="$t('UnitsManagement.selectTransferUnit')"
                        :show-clear="transferTargetUnitId != null"
                    />
                    <small
                        v-if="!transferLoading && !transferUnitOptions.length"
                        class="text-surface-500"
                    >
                        {{ $t("UnitsManagement.noAvailableTransferUnits") }}
                    </small>
                </div>

                <Button
                    type="button"
                    :label="$t('UnitsManagement.submitTransferGuests')"
                    :loading="transferSubmitting"
                    :disabled="
                        transferSourceUnitId == null ||
                        transferTargetUnitId == null ||
                        transferSubmitting ||
                        transferLoading
                    "
                    @click="submitTransferGuests"
                >
                    <template #icon>
                        <AppIcon name="pi pi-users" />
                    </template>
                </Button>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="checkoutModalVisible"
            modal
            :header="$t('UnitsManagement.actions.completeCheckout')"
            class="w-[min(100vw-2rem,30rem)]"
        >
            <div class="flex flex-col gap-4">
                <div class="rounded-xl border border-surface-200 bg-surface-50 p-4 dark:border-surface-600 dark:bg-surface-900/40">
                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-surface-600 dark:text-surface-300">
                                {{ $t("UnitsManagement.actions.viewOrder") }}
                            </dt>
                            <dd class="font-semibold text-surface-900 dark:text-surface-0">
                                #{{ checkoutOrderIdLabel }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-surface-600 dark:text-surface-300">
                                {{ $t("UnitsManagement.card.duration") }}
                            </dt>
                            <dd class="font-semibold text-surface-900 dark:text-surface-0">
                                {{ occupiedDurationLabel }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-surface-600 dark:text-surface-300">
                                {{ $t("UnitsManagement.card.totalOrder") }}
                            </dt>
                            <dd class="font-semibold text-surface-900 dark:text-surface-0">
                                {{ checkoutOrderTotalLabel }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-3 border-t border-surface-200 pt-3 dark:border-surface-600">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-surface-600 dark:text-surface-300">
                            {{ $t("UnitsManagement.checkout.productItemsOverview") }}
                        </p>
                        <p
                            v-if="checkoutLoading"
                            class="text-sm text-surface-500 dark:text-surface-400"
                        >
                            Loading...
                        </p>
                        <ul
                            v-else-if="checkoutProductOverview.length"
                            class="space-y-1.5 text-sm"
                        >
                            <li
                                v-for="item in checkoutProductOverview"
                                :key="item.key"
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="truncate text-surface-700 dark:text-surface-200">
                                    {{ item.name }}
                                </span>
                                <span class="shrink-0 font-semibold text-surface-900 dark:text-surface-0">
                                    x{{ item.qty }}
                                </span>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="text-sm text-surface-500 dark:text-surface-400"
                        >
                            {{ $t("UnitsManagement.checkout.noProductsFound") }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        :label="$t('Cancel')"
                        :disabled="checkoutSubmitting"
                        @click="checkoutModalVisible = false"
                    />
                    <Button
                        type="button"
                        :label="$t('UnitsManagement.actions.completeCheckout')"
                        :loading="checkoutSubmitting"
                        :disabled="checkoutUnitId == null || checkoutSubmitting || checkoutLoading"
                        @click="submitCheckout"
                    >
                        <template #icon>
                            <AppIcon name="pi pi-check-circle" />
                        </template>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { UnitStatus, UnitType } from "../../../apis/services/units/units.type";
import { unitsService } from "../../../apis/services/units/units.apis";
import { ordersService } from "../../../apis/services/orders/orders.apis";
import OrderDetailDrawer from "../orders/OrderDetailDrawer.vue";
import { formatCurrency } from "../../../utils/formatCurrency";

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["action"]);

const { t } = useI18n();
const router = useRouter();
const confirm = useConfirm();
const toast = useToast();

const menuRef = ref();

const orderDrawerVisible = ref(false);
const orderDrawerOrderId = ref(null);
const reservationModalVisible = ref(false);
const reservationSubmitting = ref(false);
const reservationUnitId = ref(null);
const reservationDateTime = ref("");
const reservationCustomerName = ref("");
const reservationEditMode = ref(false);
const transferModalVisible = ref(false);
const transferLoading = ref(false);
const transferSubmitting = ref(false);
const transferSourceUnitId = ref(null);
const transferTargetUnitId = ref(null);
const transferUnitOptions = ref([]);
const checkoutModalVisible = ref(false);
const checkoutSubmitting = ref(false);
const checkoutLoading = ref(false);
const checkoutUnitId = ref(null);
const checkoutOrder = ref(null);
const durationNowMs = ref(Date.now());
let durationTimer = null;

/** Larger rows / hit targets for touch screens (popup menu is portaled). */
const touchMenuPt = {
    root: { class: "touch-manipulation" },
    list: { class: "min-w-[min(100vw-2rem,20rem)] py-1" },
    item: { class: "my-0.5" },
    itemLink: {
        class: "min-h-[48px] items-center gap-3 py-3 pe-4 ps-3 text-base leading-snug",
    },
    itemIcon: {
        class: "!text-xl !leading-none",
    },
    itemLabel: {
        class: "!text-base",
    },
};

const toggleMenu = (event) => {
    menuRef.value?.toggle(event);
};

const normalizedType = computed(() =>
    String(props.unit?.type || "")
        .trim()
        .toLowerCase()
);

const typeIconClass = computed(() => {
    if (normalizedType.value === UnitType.Room) {
        return "pi pi-home";
    }
    if (normalizedType.value === UnitType.Table) {
        return "md-tablerestaurant-outlined";
    }
    return "pi pi-box";
});

const statusLabel = computed(() => {
    const value = props.unit?.status;
    if (value !== undefined && value !== null && String(value).trim() !== "") {
        const raw = String(value).trim().toLowerCase();
        if (raw === UnitStatus.Available) {
            return "available";
        }
        if (raw === UnitStatus.Reserved) {
            return "reserved";
        }
        if (raw === UnitStatus.Occupied) {
            return "occupied";
        }
        if (raw === UnitStatus.Inactive) {
            return "inactive";
        }
        return String(value);
    }
    return props.unit?.active ? "available" : "inactive";
});

const statusTagKey = computed(() => {
    const known = ["available", "reserved", "occupied", "inactive"];
    const lower = String(statusLabel.value).trim().toLowerCase();
    if (known.includes(lower)) {
        return lower;
    }
    return "unknown";
});

const statusTagDisplay = computed(() => {
    if (statusTagKey.value === "unknown") {
        return String(props.unit?.status ?? statusLabel.value ?? "-");
    }
    return t(`Units.Statuses.${statusTagKey.value}`);
});

const statusTagSeverity = computed(() => {
    switch (statusTagKey.value) {
        case "available":
            return "success";
        case "reserved":
            return "warn";
        case "occupied":
            return "danger";
        case "inactive":
            return "info";
        default:
            return "secondary";
    }
});

const cardHeaderClass = computed(() => {
    switch (statusTagKey.value) {
        case "occupied":
            return "bg-rose-50 dark:bg-rose-900/20";
        case "reserved":
            return "bg-amber-50 dark:bg-amber-900/20";
        case "available":
            return "bg-emerald-50 dark:bg-emerald-900/20";
        default:
            return "bg-surface-50 dark:bg-surface-700/40";
    }
});

const statusChipClass = computed(() => {
    switch (statusTagKey.value) {
        case "occupied":
            return "bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200";
        case "reserved":
            return "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200";
        case "available":
            return "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200";
        default:
            return "bg-surface-100 text-surface-700 dark:bg-surface-700 dark:text-surface-200";
    }
});

const capacityLabel = computed(() => {
    const n = props.unit?.capacity;
    if (n == null || Number.isNaN(Number(n))) {
        return "-";
    }
    return `${n} ${Number(n) === 1 ? t("UnitsManagement.card.person") : t("UnitsManagement.card.people")}`;
});

const orderTotalLabel = computed(() => {
    const total = props.unit?.current_order?.total;
    return formatCurrency(total);
});

const checkoutOrderTotalLabel = computed(() => {
    const total = checkoutOrder.value?.total ?? props.unit?.current_order?.total;
    return formatCurrency(total);
});

const occupiedDurationLabel = computed(() => {
    const openedAt = props.unit?.current_order?.opened_at;
    if (!openedAt) {
        return "0h 00m";
    }
    const start = new Date(openedAt);
    if (Number.isNaN(start.getTime())) {
        return "0h 00m";
    }
    const diffMs = durationNowMs.value - start.getTime();
    if (diffMs <= 0) {
        return "0h 00m";
    }
    const mins = Math.floor(diffMs / 60000);
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return `${h}h ${String(m).padStart(2, "0")}m`;
});

const reservedByLabel = computed(() => props.unit?.reserved_by || "-");

const reservedTimeLabel = computed(() => {
    const iso =
        props.unit?.reserved_at ||
        props.unit?.current_order?.reserved_at ||
        props.unit?.current_order?.opened_at;
    if (!iso) {
        return "--:--";
    }
    const dt = new Date(iso);
    if (Number.isNaN(dt.getTime())) {
        return "--:--";
    }
    const now = new Date();
    const sameDay =
        dt.getFullYear() === now.getFullYear() &&
        dt.getMonth() === now.getMonth() &&
        dt.getDate() === now.getDate();
    const time = dt.toLocaleTimeString([], {
        hour: "numeric",
        minute: "2-digit",
    });
    return sameDay ? `${time} (${t("UnitsManagement.card.today")})` : time;
});

const typeLabel = computed(() => {
    if (normalizedType.value === UnitType.Room) {
        return "room";
    }
    if (normalizedType.value === UnitType.Table) {
        return "table";
    }
    return "unknown";
});

const typeSeverity = computed(() => {
    if (normalizedType.value === UnitType.Table) {
        return "info";
    }
    if (normalizedType.value === UnitType.Room) {
        return "warn";
    }
    return "secondary";
});

const checkoutOrderIdLabel = computed(
    () => checkoutOrder.value?.id ?? props.unit?.current_order_id ?? props.unit?.current_order?.id ?? "-"
);

const checkoutProductOverview = computed(() => {
    const items = Array.isArray(checkoutOrder.value?.items)
        ? checkoutOrder.value.items
        : Array.isArray(props.unit?.current_order?.items)
          ? props.unit.current_order.items
        : [];
    return items.slice(0, 5).map((item, idx) => ({
        key: item?.id ?? `item-${idx}`,
        name: item?.name || "-",
        qty: Number(item?.quantity ?? 0) || 0,
    }));
});

function pickOrderPayload(res) {
    const body = res?.data;
    return body?.data ?? body;
}

function notifyAction() {
    emit("action");
}

async function runApi(
    label,
    fn
) {
    try {
        await fn();
        toast.add({
            severity: "success",
            summary: label,
            life: 2500,
        });
        notifyAction();
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    }
}

function resolveReservationDateTime() {
    const iso =
        props.unit?.reserved_at ||
        props.unit?.current_order?.reserved_at ||
        props.unit?.current_order?.opened_at;
    if (!iso) {
        return "";
    }
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) {
        return "";
    }
    return formatDateTimeLocal(date);
}

function openReservationModal(id, editMode = false) {
    reservationUnitId.value = id;
    reservationEditMode.value = editMode;
    reservationDateTime.value = editMode
        ? resolveReservationDateTime() ||
          formatDateTimeLocal(roundToNearestMinutes(new Date(), 5))
        : formatDateTimeLocal(roundToNearestMinutes(new Date(), 5));
    reservationCustomerName.value = editMode ? props.unit?.reserved_by || "" : "";
    reservationModalVisible.value = true;
}

function roundToNearestMinutes(date, minutes) {
    const ms = 1000 * 60 * minutes;
    return new Date(Math.ceil(date.getTime() / ms) * ms);
}

function formatDateTimeLocal(date) {
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function shiftReservationMinutes(deltaMinutes) {
    const base = reservationDateTime.value ? new Date(reservationDateTime.value) : new Date();
    if (Number.isNaN(base.getTime())) {
        return;
    }
    base.setMinutes(base.getMinutes() + deltaMinutes);
    reservationDateTime.value = formatDateTimeLocal(base);
}

async function submitReservation() {
    if (reservationUnitId.value == null || !reservationDateTime.value) {
        return;
    }

    reservationSubmitting.value = true;
    try {
        const selectedDate = new Date(reservationDateTime.value);
        if (Number.isNaN(selectedDate.getTime())) {
            throw new Error("Invalid reservation datetime");
        }

        await unitsService.reserveUnit(reservationUnitId.value, {
            reserved_at: selectedDate.toISOString(),
            reserved_by: reservationCustomerName.value || null,
        });
        toast.add({
            severity: "success",
            summary: reservationEditMode.value
                ? t("UnitsManagement.actions.editReservationTime")
                : t("UnitsManagement.actions.addReservation"),
            life: 2500,
        });
        reservationModalVisible.value = false;
        reservationUnitId.value = null;
        reservationEditMode.value = false;
        reservationCustomerName.value = "";
        notifyAction();
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    } finally {
        reservationSubmitting.value = false;
    }
}

async function loadTransferUnits(sourceUnitId) {
    transferLoading.value = true;
    transferUnitOptions.value = [];
    try {
        const allUnits = [];
        let page = 1;
        let lastPage = 1;

        do {
            const { data } = await unitsService.getUnits({
                page,
                per_page: 100,
            });
            const items = data?.items ?? [];
            allUnits.push(...items);
            lastPage = Number(data?.meta?.last_page ?? 1);
            page += 1;
        } while (page <= lastPage);

        transferUnitOptions.value = allUnits
            .filter((x) => Number(x?.id) !== Number(sourceUnitId))
            .filter((x) => String(x?.status || "").toLowerCase() === UnitStatus.Available)
            .filter((x) => Boolean(x?.active))
            .map((x) => ({
                value: x.id,
                label: x?.name || `#${x?.id}`,
            }));
    } catch {
        transferUnitOptions.value = [];
    } finally {
        transferLoading.value = false;
    }
}

async function openTransferModal(sourceUnitId) {
    transferSourceUnitId.value = sourceUnitId;
    transferTargetUnitId.value = null;
    transferModalVisible.value = true;
    await loadTransferUnits(sourceUnitId);
}

async function submitTransferGuests() {
    if (transferSourceUnitId.value == null || transferTargetUnitId.value == null) {
        return;
    }

    transferSubmitting.value = true;
    try {
        await unitsService.transferGuests(transferSourceUnitId.value, {
            target_unit_id: Number(transferTargetUnitId.value),
        });
        toast.add({
            severity: "success",
            summary: t("UnitsManagement.actions.transferGuests"),
            life: 2500,
        });
        transferModalVisible.value = false;
        transferSourceUnitId.value = null;
        transferTargetUnitId.value = null;
        notifyAction();
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    } finally {
        transferSubmitting.value = false;
    }
}

async function openCheckoutModal(id) {
    const orderId = props.unit?.current_order_id ?? props.unit?.current_order?.id;
    checkoutLoading.value = true;
    checkoutOrder.value = null;
    if (orderId != null && orderId !== "") {
        try {
            const res = await ordersService.getOrder(orderId);
            checkoutOrder.value = pickOrderPayload(res);
        } catch {
            toast.add({
                severity: "error",
                summary: t("OrderDetail.LoadError"),
                life: 3000,
            });
        }
    }
    checkoutLoading.value = false;
    checkoutUnitId.value = id;
    checkoutModalVisible.value = true;
}

async function submitCheckout() {
    if (checkoutUnitId.value == null) {
        return;
    }
    checkoutSubmitting.value = true;
    try {
        await unitsService.closeUnit(checkoutUnitId.value);
        toast.add({
            severity: "success",
            summary: t("UnitsManagement.actions.completeCheckout"),
            life: 2500,
        });
        checkoutModalVisible.value = false;
        checkoutUnitId.value = null;
        checkoutOrder.value = null;
        notifyAction();
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsManagement.actionError"),
            life: 4000,
        });
    } finally {
        checkoutSubmitting.value = false;
    }
}

const menuItems = computed(() => {
    const u = props.unit;
    const id = u?.id;
    if (id == null) {
        return [];
    }

    const key = statusTagKey.value;
    const orderId = u?.current_order_id;

    const menuEntry = (labelKey, icon, run) => ({
        label: t(labelKey),
        icon,
        command: run,
    });

    if (key === "available") {
        const addReservation =
            normalizedType.value === UnitType.Table
                ? menuEntry(
                      "UnitsManagement.actions.addReservation",
                      "pi pi-calendar-plus",
                      () => openReservationModal(id)
                  )
                : menuEntry(
                      "UnitsManagement.actions.addReservation",
                      "pi pi-calendar-plus",
                      () => {
                          router.push({
                              path: "/reservations/create",
                              query: { unit_id: String(id) },
                          });
                      }
                  );

        const startService = () => unitsService.startOrder(id);

        return [
            addReservation,
            normalizedType.value === UnitType.Table
                ? menuEntry(
                      "UnitsManagement.actions.openTable",
                      "pi pi-unlock",
                      () =>
                          runApi(t("UnitsManagement.actions.openTable"), startService)
                  )
                : menuEntry(
                      "UnitsManagement.actions.startOrder",
                      "pi pi-shopping-cart",
                      () =>
                          runApi(t("UnitsManagement.actions.startOrder"), startService)
                  ),
        ];
    }

    if (key === "reserved") {
        const startService = () => unitsService.startOrder(id);

        return [
            menuEntry(
                "UnitsManagement.actions.editReservationTime",
                "pi pi-clock",
                () => openReservationModal(id, true)
            ),
            menuEntry(
                "UnitsManagement.actions.cancelReservation",
                "pi pi-times-circle",
                () =>
                    runApi(t("UnitsManagement.actions.cancelReservation"), () =>
                        unitsService.cancelUnitReservation(id)
                    )
            ),
            normalizedType.value === UnitType.Table
                ? menuEntry(
                      "UnitsManagement.actions.openTable",
                      "pi pi-unlock",
                      () =>
                          runApi(t("UnitsManagement.actions.openTable"), startService)
                  )
                : menuEntry(
                      "UnitsManagement.actions.start",
                      "pi pi-play",
                      () =>
                          runApi(t("UnitsManagement.actions.start"), startService)
                  ),
        ];
    }

    if (key === "occupied") {
        return [
            menuEntry(
                "UnitsManagement.actions.viewOrder",
                "pi pi-eye",
                () => {
                    if (orderId != null) {
                        orderDrawerOrderId.value = orderId;
                        orderDrawerVisible.value = true;
                    } else {
                        toast.add({
                            severity: "warn",
                            summary: t("UnitsManagement.noCurrentOrder"),
                            life: 3000,
                        });
                    }
                }
            ),
            menuEntry(
                "UnitsManagement.actions.completeCheckout",
                "pi pi-check-circle",
                () => openCheckoutModal(id)
            ),
            menuEntry(
                "UnitsManagement.actions.transferGuests",
                "pi pi-users",
                () => openTransferModal(id)
            ),
        ];
    }

    return [];
});

onMounted(() => {
    durationTimer = window.setInterval(() => {
        durationNowMs.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (durationTimer != null) {
        window.clearInterval(durationTimer);
        durationTimer = null;
    }
});
</script>
