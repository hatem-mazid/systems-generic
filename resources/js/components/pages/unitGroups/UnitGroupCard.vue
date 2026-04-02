<template>
    <section
        class="rounded-xl border flex flex-col gap-5 border-surface-200 p-4 dark:border-surface-700"
    >
        <div class="flex items-center justify-between gap-1">
            <div class="flex min-w-0 items-center gap-3">
                <h2
                    class="truncate line-clamp-1 text-lg font-semibold text-surface-900 dark:text-surface-0"
                >
                    {{ unitGroup.name || "-" }}
                </h2>
                <Tag
                    :value="
                        unitGroup.is_active
                            ? $t('UserForm.Active')
                            : $t('UserForm.Inactive')
                    "
                    :severity="unitGroup.is_active ? 'success' : 'warn'"
                    rounded
                />
            </div>

            <div class="flex items-center gap-3">
                <Button
                    as="router-link"
                    :to="`/units/create?unit_group_id=${unitGroup.id}`"
                    size="large"
                    outlined
                    rounded
                    severity="secondary"
                    icon="pi pi-plus"
                    :label="$t('Add Unit')"
                />
                <Button
                    as="router-link"
                    :to="`/unit-groups-setup/${unitGroup.id}`"
                    size="large"
                    outlined
                    rounded
                    severity="info"
                    icon="pi pi-pencil"
                    :aria-label="$t('Edit')"
                />
                <DeleteUnitGroupButton
                    :unit-group="unitGroup"
                    @deleted="$emit('deleted')"
                />
            </div>
        </div>

        <div>
            <div
                v-if="unitGroup.units?.length"
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3"
            >
                <UnitCard
                    v-for="unit in unitGroup.units"
                    :key="unit.id"
                    :unit="unit"
                    @deleted="$emit('deleted')"
                />
            </div>

            <div
                v-else
                class="rounded-lg border border-dashed border-surface-300 p-4 text-sm text-surface-600 dark:border-surface-600 dark:text-surface-300"
            >
                {{ $t("Units.EmptyInGroup") }}
            </div>
        </div>
    </section>
</template>

<script setup>
import UnitCard from "../units/UnitCard.vue";
import DeleteUnitGroupButton from "./DeleteUnitGroupButton.vue";

defineEmits(["deleted"]);

const props = defineProps({
    unitGroup: {
        type: Object,
        required: true,
    },
});
</script>
