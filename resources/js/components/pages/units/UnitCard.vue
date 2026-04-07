<template>
    <div
        class="rounded-lg border bg-white border-surface-200 p-3 dark:border-surface-700 dark:bg-surface-800 flex flex-col gap-2"
        :class="{ 'opacity-75': !canEditOrDelete }"
    >
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <AppIcon
                    :name="typeIconClass"
                    class="!text-xl text-surface-700 dark:text-surface-200 size-6"
                />
                
                <p
                    class="truncate font-medium text-surface-900 dark:text-surface-0"
                >
                    {{ unit.name || "-" }}
                </p>
            </div>
            <Tag
                :value="$t(`Units.ActiveStates.${activeLabel}`)"
                :severity="activeSeverity"
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

        <div class="flex items-center justify-end gap-2">
            <Button
                v-if="canEditOrDelete"
                as="router-link"
                :to="`/units/${unit.id}`"
                size="large"
                rounded
                outlined
                severity="info"
                :aria-label="$t('Edit')"
            >
                <template #icon>
                    <AppIcon name="pi pi-pencil" />
                </template>
            </Button>
            <Button
                v-else
                size="large"
                rounded
                outlined
                severity="info"
                disabled
                :aria-label="$t('Edit')"
            >
                <template #icon>
                    <AppIcon name="pi pi-pencil" />
                </template>
            </Button>
            <DeleteUnitButton
                :unit="unit"
                :disabled="!canEditOrDelete"
                @deleted="$emit('deleted')"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { UnitStatus, UnitType } from "../../../apis/services/units/units.type";
import DeleteUnitButton from "./DeleteUnitButton.vue";

const { t } = useI18n();

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
});

defineEmits(["deleted"]);

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

/** Edit/delete only when operational status is available. */
const canEditOrDelete = computed(() => {
    const raw = props.unit?.status;
    if (raw === undefined || raw === null || String(raw).trim() === "") {
        return true;
    }
    return String(raw).trim().toLowerCase() === UnitStatus.Available;
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

const activeLabel = computed(() =>
    props.unit?.active ? "active" : "inactive"
);

const activeSeverity = computed(() =>
    props.unit?.active ? "success" : "danger"
);

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
</script>
