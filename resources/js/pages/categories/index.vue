<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Sidebar.Categories") }}
            </h1>

            <Button
                to="/categories/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add Category')"
            >
                <template #icon>
                    <AppIcon name="pi pi-plus" />
                </template>
            </Button>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2">
            <span class="text-sm text-surface-600 dark:text-surface-300">
                {{ $t("UnitsManagement.viewMode.label") }}:
            </span>
            <Button
                type="button"
                size="small"
                rounded
                :outlined="viewMode !== 'comfortable'"
                :severity="viewMode === 'comfortable' ? 'primary' : 'secondary'"
                :label="$t('UnitsManagement.viewMode.comfortable')"
                @click="configStore.setCategoriesViewMode('comfortable')"
            />
            <Button
                type="button"
                size="small"
                rounded
                :outlined="viewMode !== 'compact'"
                :severity="viewMode === 'compact' ? 'primary' : 'secondary'"
                :label="$t('UnitsManagement.viewMode.compact')"
                @click="configStore.setCategoriesViewMode('compact')"
            />
        </div>

        <div class="mt-8 min-w-0">
            <div :class="categoriesGridClass">
                <Skeleton
                    v-if="isLoading"
                    v-for="n in paginator.per_page"
                    :key="'sk-' + n"
                    width="100%"
                    height="260px"
                    class="rounded-2xl"
                />

                <template v-else>
                    <CategoryCard
                        v-for="category in categories"
                        :key="category.id"
                        :category="category"
                        @deleted="handleCategoryDeleted"
                    />

                    <div
                        v-if="!categories.length"
                        class="col-span-full rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-300"
                    >
                        {{ $t("CategoriesList.Empty") }}
                    </div>
                </template>
            </div>

            <Paginator
                class="pagination-touch mt-6 bg-transparent"
                :rows="paginator.per_page"
                :total-records="paginator.total"
                :first="(paginator.current_page - 1) * paginator.per_page"
                @page="onPageChange"
            />
        </div>
    </div>
</template>

<script setup>
import { Button } from "primevue";
import { computed, onMounted, ref } from "vue";
import { categoriesService } from "../../apis/services/categories/categories.apis";
import CategoryCard from "../../components/pages/categories/CategoryCard.vue";
import { useConfigStore } from "../../stores/config";

const isLoading = ref(true);
const categories = ref([]);
const configStore = useConfigStore();
const viewMode = computed(() => configStore.categoriesViewMode);
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
});

const categoriesGridClass = computed(() => {
    if (viewMode.value === "compact") {
        return "grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5";
    }
    return "grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4";
});

const fetchCategories = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }

    categoriesService
        .getCategories({ page, per_page: paginator.value.per_page })
        .then((response) => {
            const data = response.data;
            categories.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((error) => {
            console.error("Error fetching categories:", error);
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const handleCategoryDeleted = () => {
    fetchCategories(paginator.value.current_page, false);
};

const onPageChange = (event) => {
    paginator.value.per_page = event.rows;
    fetchCategories(event.page + 1);
};

onMounted(() => {
    configStore.loadCategoriesViewMode();
    fetchCategories();
});
</script>
