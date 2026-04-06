<template>
    <div
        class="rounded-lg border bg-white border-surface-200 p-3 dark:border-surface-700 dark:bg-surface-800 flex flex-col gap-2"
    >
        <div class="flex items-center justify-between gap-3">
            <p
                class="min-w-0 flex-1 truncate font-medium text-surface-900 dark:text-surface-0"
            >
                {{ unit.name || "-" }}
            </p>
            <Button
                type="button"
                size="large"
                rounded
                outlined
                severity="secondary"
                icon="pi pi-ellipsis-h"
                :aria-label="$t('UnitsManagement.more')"
                :disabled="!menuItems.length"
                @click="toggleMenu"
            />
            <Menu
                ref="menuRef"
                :model="menuItems"
                popup
                :pt="touchMenuPt"
            />
        </div>

        <div class="flex items-center gap-2">
            <i
                :class="typeIconClass"
                class="!text-xl text-surface-700 dark:text-surface-200"
            />
            <h3
                class="shrink-0 !text-md font-medium text-surface-900 dark:text-surface-0"
            >
                {{ $t(`Units.Types.${typeLabel}`) }}
            </h3>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-sm text-surface-600 dark:text-surface-300"
                >{{ $t("Units.Status") }}:</span
            >
            <Tag
                class="shrink-0 !text-xs"
                :value="statusTagDisplay"
                :severity="statusTagSeverity"
                rounded
            />
        </div>

        <div class="space-y-1 text-sm text-surface-600 dark:text-surface-300">
            <p v-if="unit.position != null">
                {{ $t("Units.Position") }}: {{ unit.position }}
            </p>
            <p v-if="unit.capacity != null">
                {{ $t("Units.Capacity") }}: {{ unit.capacity }}
            </p>
        </div>

        <OrderDetailDrawer
            v-model:visible="orderDrawerVisible"
            :order-id="orderDrawerOrderId"
            @updated="notifyAction"
        />

        <Dialog
            v-model:visible="reservationModalVisible"
            modal
            :header="$t('UnitsManagement.actions.addReservation')"
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
                    :label="$t('UnitsManagement.submitReservation')"
                    icon="pi pi-check"
                    :loading="reservationSubmitting"
                    :disabled="!reservationDateTime || reservationUnitId == null"
                    @click="submitReservation"
                />
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
                    icon="pi pi-users"
                    :loading="transferSubmitting"
                    :disabled="
                        transferSourceUnitId == null ||
                        transferTargetUnitId == null ||
                        transferSubmitting ||
                        transferLoading
                    "
                    @click="submitTransferGuests"
                />
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { UnitStatus, UnitType } from "../../../apis/services/units/units.type";
import { unitsService } from "../../../apis/services/units/units.apis";
import OrderDetailDrawer from "../orders/OrderDetailDrawer.vue";

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
const transferModalVisible = ref(false);
const transferLoading = ref(false);
const transferSubmitting = ref(false);
const transferSourceUnitId = ref(null);
const transferTargetUnitId = ref(null);
const transferUnitOptions = ref([]);

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
        return "pi pi-stop";
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

function openReservationModal(id) {
    reservationUnitId.value = id;
    reservationDateTime.value = formatDateTimeLocal(roundToNearestMinutes(new Date(), 5));
    reservationCustomerName.value = "";
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
            summary: t("UnitsManagement.actions.addReservation"),
            life: 2500,
        });
        reservationModalVisible.value = false;
        reservationUnitId.value = null;
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
                () => {
                    router.push({
                        path: "/reservations/create",
                        query: { unit_id: String(id), mode: "edit" },
                    });
                }
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
                () =>
                    runApi(t("UnitsManagement.actions.completeCheckout"), () =>
                        unitsService.closeUnit(id)
                    )
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
</script>
