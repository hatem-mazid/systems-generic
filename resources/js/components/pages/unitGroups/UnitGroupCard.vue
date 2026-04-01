<template>
    <Card
        class="unit-group-card group h-full overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-0 shadow-sm transition-all duration-200 hover:border-primary-200/60 hover:shadow-md dark:border-surface-700 dark:bg-surface-900 dark:hover:border-primary-700/40"
    >
        <template #content>
            <div class="flex h-full flex-col gap-4 p-1">
                <div class="space-y-3">
                    <div class="flex min-w-0 items-center justify-between gap-2">
                        <h3
                            class="min-w-0 flex-1 truncate text-xl font-semibold leading-tight text-surface-900 dark:text-surface-0"
                        >
                            {{ unitGroup.name || "-" }}
                        </h3>
                        <Tag
                            v-if="activeKnown"
                            :value="activeLabel"
                            :severity="isActive ? 'success' : 'warn'"
                            class="shrink-0 !text-xs"
                            rounded
                        />
                        <span
                            v-else
                            class="shrink-0 text-xs text-surface-500 dark:text-surface-400"
                        >
                            —
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-2 text-sm text-surface-600 dark:text-surface-300"
                    >
                        <i class="pi pi-sort-numeric-up" />
                        <span>
                            {{ $t("UnitGroupsList.Position") }}:
                            {{ unitGroup.position ?? "-" }}
                        </span>
                    </div>
                </div>

                <div
                    class="mt-auto flex items-center justify-end gap-2 border-t border-surface-200/80 pt-3 dark:border-surface-700"
                >
                    <Button
                        as="router-link"
                        :to="`/unit-groups/${unitGroup.id}`"
                        size="large"
                        rounded
                        outlined
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
        </template>
    </Card>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DeleteUnitGroupButton from "./DeleteUnitGroupButton.vue";

defineEmits(["deleted"]);

const props = defineProps({
    unitGroup: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();

const activeKnown = computed(
    () =>
        props.unitGroup?.is_active !== undefined &&
        props.unitGroup?.is_active !== null
);

const isActive = computed(() => Boolean(props.unitGroup?.is_active));

const activeLabel = computed(() =>
    isActive.value ? t("UserForm.Active") : t("UserForm.Inactive")
);
</script>
