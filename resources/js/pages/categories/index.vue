<template>
    <div>
        <div class="flex justify-between">
            <h1 class="text-2xl text-surface-800 font-semibold dark:text-surface-100">
                {{ $t("Sidebar.Categories") }}
            </h1>

            <Button
                to="/categories/create"
                as="router-link"
                size="lg"
                :label="$t('Add Category')"
                icon="pi pi-plus"
            />
        </div>

        <div
            class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
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
                    class="col-span-full rounded-xl border border-dashed border-surface-300 p-6 text-center text-surface-600 dark:border-surface-600 dark:text-surface-300"
                >
                    {{ $t("CategoriesList.Empty") }}
                </div>
            </template>
        </div>

        <Paginator
            class="mt-5 bg-transparent"
            :rows="paginator.per_page"
            :totalRecords="paginator.total"
            :first="(paginator.current_page - 1) * paginator.per_page"
            @page="onPageChange"
        />
    </div>
</template>

<script setup>
import { Button } from "primevue";
import { onMounted, ref } from "vue";
import { categoriesService } from "../../apis/services/categories/categories.apis";
import CategoryCard from "../../components/pages/categories/CategoryCard.vue";

const isLoading = ref(true);
const categories = ref([]);
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
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
    fetchCategories();
});
</script>
