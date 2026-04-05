<template>
    <div class="flex flex-col gap-5">
        <div class="flex items-center justify-between gap-1">
            <div class="flex min-w-0 items-center gap-3">
                <h2
                    v-if="!hideGroupHeading"
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

            <div v-if="!managementMode || isAdmin" class="flex items-center gap-3">
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
                class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"
            >
                <template v-for="unit in unitGroup.units" :key="unit.id">
                    <UnitCard
                        v-if="!useStaffUnitCard"
                        :unit="unit"
                        @deleted="$emit('deleted')"
                    />
                    <UnitCardStaff
                        v-else
                        :unit="unit"
                        @action="$emit('deleted')"
                    />
                </template>
            </div>

            <div
                v-else
                class="rounded-lg border border-dashed border-surface-300 p-4 text-sm text-surface-600 dark:border-surface-600 dark:text-surface-300"
            >
                {{ $t("Units.EmptyInGroup") }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { UserRole } from "../../../apis/services/users/users.type";
import UnitCard from "../units/UnitCard.vue";
import UnitCardStaff from "../units/UnitCardStaff.vue";
import DeleteUnitGroupButton from "./DeleteUnitGroupButton.vue";
import { useUserStore } from "../../../stores/user";

defineEmits(["deleted"]);

const props = defineProps({
    unitGroup: {
        type: Object,
        required: true,
    },
    /** When true (Units Management page): non-admin users see staff unit card with actions menu. */
    managementMode: {
        type: Boolean,
        default: false,
    },
    /** Hide group name heading (e.g. when the name is shown in a tab). */
    hideGroupHeading: {
        type: Boolean,
        default: false,
    },
});

const userStore = useUserStore();

const isAdmin = computed(() => {
    const r = userStore.info?.role;
    return r === UserRole.ADMIN || r === "admin";
});

const useStaffUnitCard = computed(
    () => props.managementMode && !isAdmin.value
);
</script>
