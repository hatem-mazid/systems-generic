<template>
    <Card
        class="category-card group h-full overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-0 shadow-sm transition-all duration-200 hover:border-primary-200/60 hover:shadow-md dark:border-surface-700 dark:bg-surface-900 dark:hover:border-primary-700/40"
    >
        <template #content>
            <div class="flex flex-col gap-3 p-1">
                <div
                    class="h-36 w-full overflow-hidden rounded-lg bg-surface-200 dark:bg-surface-700"
                >
                    <img
                        v-if="categoryImage"
                        :src="categoryImage"
                        :alt="category.name || 'category image'"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-surface-500 dark:text-surface-300"
                    >
                        <i class="pi pi-image !text-3xl"></i>
                    </div>
                </div>

                <div class="min-h-16 space-y-3">
                    <div
                        class="flex min-w-0 items-center justify-between gap-2"
                    >
                        <h3
                            class="min-w-0 flex-1 truncate text-xl font-semibold leading-tight text-surface-900 dark:text-surface-0"
                        >
                            {{ category.name || "-" }}
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
                    <p
                        class="line-clamp-2 text-sm text-surface-600 dark:text-surface-300"
                    >
                        {{
                            category.description ||
                            $t("CategoriesList.NoDescription")
                        }}
                    </p>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-surface-200/80 pt-3 dark:border-surface-700"
                >
                    <Button
                        as="router-link"
                        :to="`/categories/${category.id}`"
                        size="large"
                        rounded
                        outlined
                        severity="info"
                        icon="pi pi-pencil"
                        :aria-label="$t('Edit')"
                    />

                    <DeleteCategoryButton
                        :category="category"
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
import DeleteCategoryButton from "./DeleteCategoryButton.vue";

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

defineEmits(["deleted"]);

const { t } = useI18n();

const categoryImage = computed(() => {
    const media = props.category?.media ?? [];
    const image =
        media.find((item) => item.collection_name === "image") || media[0];
    return image?.url || null;
});

const activeKnown = computed(
    () =>
        props.category?.active !== undefined && props.category?.active !== null
);

const isActive = computed(() => Boolean(props.category?.active));

const activeLabel = computed(() =>
    isActive.value ? t("UserForm.Active") : t("UserForm.Inactive")
);
</script>
