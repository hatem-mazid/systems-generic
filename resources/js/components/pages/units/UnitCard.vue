<template>
    <div
        class="rounded-lg border bg-white border-surface-200 p-3 dark:border-surface-700 dark:bg-surface-800"
    >
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex items-center gap-2">
                <i
                    :class="typeIconClass"
                    class="text-lg text-surface-700 dark:text-surface-200"
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

        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs text-surface-500 dark:text-surface-400"
                >{{ $t("Units.Type") }}:</span
            >
            <Tag
                :value="$t(`Units.Types.${typeLabel}`)"
                :severity="typeSeverity"
                rounded
            />
        </div>

        <div
            class="mt-2 space-y-1 text-sm text-surface-600 dark:text-surface-300"
        >
            <p>{{ $t("Units.Position") }}: {{ unit.position ?? "-" }}</p>
            <p>{{ $t("Units.Capacity") }}: {{ unit.capacity ?? "-" }}</p>
            <p>
                {{ $t("Units.Status") }}:
                {{ $t(`Units.Statuses.${statusLabel}`) }}
            </p>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2">
            <Button
                as="router-link"
                :to="`/units/${unit.id}`"
                size="large"
                rounded
                outlined
                severity="info"
                icon="pi pi-pencil"
                :aria-label="$t('Edit')"
            />
            <DeleteUnitButton :unit="unit" @deleted="$emit('deleted')" />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { UnitStatus, UnitType } from "../../../apis/services/units/units.type";
import DeleteUnitButton from "./DeleteUnitButton.vue";

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
