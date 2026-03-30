<template>
    <Card
        class="user-card group h-full overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-0 shadow-sm transition-all duration-200 hover:border-primary-200/60 hover:shadow-md dark:border-surface-700 dark:bg-surface-900 dark:hover:border-primary-700/40"
    >
        <template #content>
            <div class="flex flex-col gap-4 p-1">
                <div class="flex items-start gap-4">
                    <Avatar
                        :label="initial"
                        size="xlarge"
                        shape="circle"
                        class="!bg-gradient-to-br !from-primary-400 !to-primary-600 !text-white ring-4 ring-primary-500/10 dark:ring-primary-400/10"
                    />
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h3
                            class="truncate text-lg font-semibold leading-tight text-surface-900 dark:text-surface-50"
                        >
                            {{ displayName }}
                        </h3>
                        <Tag
                            v-if="activeKnown"
                            :value="activeLabel"
                            :severity="isActive ? 'success' : 'secondary'"
                            class="mt-2 !text-xs"
                            rounded
                        />
                        <span
                            v-else
                            class="mt-2 inline-block text-xs text-surface-500 dark:text-surface-400"
                        >
                            —
                        </span>
                    </div>
                </div>

                <div
                    class="space-y-3 rounded-xl bg-surface-50/80 px-3 py-3 dark:bg-surface-800/50"
                >
                    <div class="flex gap-2.5">
                        <span
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-200/70 text-surface-600 dark:bg-surface-700 dark:text-surface-300"
                        >
                            <i class="pi pi-envelope text-sm" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-surface-500 dark:text-surface-400"
                            >
                                {{ $t("UserForm.Email") }}
                            </p>
                            <p
                                class="truncate text-sm text-surface-800 dark:text-surface-100"
                            >
                                {{ displayEmail }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2.5">
                        <span
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-200/70 text-surface-600 dark:bg-surface-700 dark:text-surface-300"
                        >
                            <i class="pi pi-users text-sm" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-surface-500 dark:text-surface-400"
                            >
                                {{ $t("UserForm.Role") }}
                            </p>
                            <p
                                class="text-sm text-surface-800 dark:text-surface-100"
                            >
                                {{ roleLabel }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-surface-200/80 pt-3 dark:border-surface-700"
                >
                    <Button
                        v-tooltip.top="$t('UserForm.Edit')"
                        variant="outlined"
                        size="small"
                        rounded
                        icon="pi pi-pencil"
                        :to="`/users/${user.id}`"
                        as="router-link"
                        severity="info"
                        :aria-label="$t('UserForm.Edit')"
                    />
                    <Button
                        v-tooltip.top="$t('Delete')"
                        variant="outlined"
                        size="small"
                        rounded
                        icon="pi pi-trash"
                        severity="danger"
                        :loading="deleting"
                        :disabled="disableDelete"
                        :aria-label="$t('Delete')"
                        @click="$emit('delete', user)"
                    />
                </div>
            </div>
        </template>
    </Card>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    deleting: {
        type: Boolean,
        default: false,
    },
    disableDelete: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["delete"]);

const { t, te } = useI18n();

const displayName = computed(() => props.user?.name?.trim() || "—");

const initial = computed(() => {
    const n = props.user?.name?.trim();
    return n?.[0]?.toUpperCase() ?? "?";
});

const displayEmail = computed(() => props.user?.email?.trim() || "—");

const activeKnown = computed(
    () => props.user?.active !== undefined && props.user?.active !== null
);

const isActive = computed(() => Boolean(props.user?.active));

const activeLabel = computed(() =>
    isActive.value ? t("UserForm.Active") : t("UserForm.Inactive")
);

const roleLabel = computed(() => {
    const role = props.user?.role;
    if (!role) {
        return "—";
    }
    const key = `UserForm.Roles.${role}`;
    return te(key) ? t(key) : String(role);
});
</script>
