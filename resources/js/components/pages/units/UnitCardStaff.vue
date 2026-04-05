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
            <Tag
                class="shrink-0 !text-xs"
                :value="$t(`Units.Types.${typeLabel}`)"
                :severity="typeSeverity"
                rounded
            />
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
        return [
            menuEntry(
                "UnitsManagement.actions.addReservation",
                "pi pi-calendar-plus",
                () => {
                    router.push({
                        path: "/reservations/create",
                        query: { unit_id: String(id) },
                    });
                }
            ),
            menuEntry(
                "UnitsManagement.actions.startOrder",
                "pi pi-shopping-cart",
                () =>
                    runApi(t("UnitsManagement.actions.startOrder"), () =>
                        unitsService.startOrder(id)
                    )
            ),
        ];
    }

    if (key === "reserved") {
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
            menuEntry(
                "UnitsManagement.actions.start",
                "pi pi-play",
                () =>
                    runApi(t("UnitsManagement.actions.start"), () =>
                        unitsService.startOrder(id)
                    )
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
                        router.push(`/orders/${orderId}`);
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
                "UnitsManagement.actions.changeStatus",
                "pi pi-sync",
                () => {
                    router.push(`/units/${id}`);
                }
            ),
            menuEntry(
                "UnitsManagement.actions.editDetails",
                "pi pi-pencil",
                () => {
                    router.push(`/units/${id}`);
                }
            ),
            menuEntry(
                "UnitsManagement.actions.removeUnit",
                "pi pi-trash",
                () => {
                    confirm.require({
                        message: t("Units.DeleteMessage", {
                            name: u?.name || t("Units.ThisUnit"),
                        }),
                        header: t("Units.DeleteTitle"),
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
                        accept: async () => {
                            try {
                                await unitsService.deleteUnit(id);
                                toast.add({
                                    severity: "success",
                                    summary: t("Units.DeleteSuccess"),
                                    life: 3000,
                                });
                                notifyAction();
                            } catch {
                                toast.add({
                                    severity: "error",
                                    summary: t("Units.DeleteError"),
                                    life: 4000,
                                });
                            }
                        },
                    });
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
                () => {
                    router.push({
                        path: "/units/transfer",
                        query: { unit_id: String(id) },
                    });
                }
            ),
        ];
    }

    return [];
});
</script>
