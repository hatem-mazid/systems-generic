<template>
  <div class="touch-manipulation">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <h1
        class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
      >
        {{ $t("Sidebar.Products") }}
      </h1>

      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
        <Button
          type="button"
          outlined
          severity="secondary"
          size="large"
          class="min-h-[48px] w-full sm:w-auto"
          :label="$t('ProductsList.DownloadTemplate')"
          :loading="templateLoading"
          @click="downloadTemplate"
        >
          <template #icon>
            <AppIcon name="pi pi-download" />
          </template>
        </Button>

        <Button
          v-if="canCreateProduct"
          type="button"
          outlined
          severity="secondary"
          size="large"
          class="min-h-[48px] w-full sm:w-auto"
          :label="$t('ProductsList.BulkImport')"
          :loading="importLoading"
          @click="openImportDialog"
        >
          <template #icon>
            <AppIcon name="pi pi-upload" />
          </template>
        </Button>

        <Button
          v-if="canCreateProduct"
          to="/products/create"
          as="router-link"
          size="large"
          class="min-h-[48px] w-full shrink-0 sm:w-auto"
          :label="$t('Add Product')"
        >
          <template #icon>
            <AppIcon name="pi pi-plus" />
          </template>
        </Button>
      </div>
    </div>
    <input
      ref="importInputRef"
      type="file"
      class="hidden"
      accept=".xlsx,.xls,.csv"
      @change="onImportFileChange"
    />

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
        @click="configStore.setProductsViewMode('comfortable')"
      />
      <Button
        type="button"
        size="small"
        rounded
        :outlined="viewMode !== 'compact'"
        :severity="viewMode === 'compact' ? 'primary' : 'secondary'"
        :label="$t('UnitsManagement.viewMode.compact')"
        @click="configStore.setProductsViewMode('compact')"
      />
    </div>

    <div
      class="mt-6 rounded-2xl border border-surface-200/80 bg-surface-0 p-5 shadow-sm sm:p-6 dark:border-surface-700 dark:bg-surface-900"
    >
      <div
        class="flex flex-col gap-5 lg:flex-row lg:flex-wrap lg:items-end lg:gap-6"
      >
        <div class="min-w-0 w-full shrink-0">
          <label
            class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
            for="product-search"
          >
            {{ $t("ProductsList.SearchLabel") }}
          </label>
          <span class="relative block w-full">
            <AppIcon
              name="pi pi-search"
              class="pointer-events-none absolute left-4 top-1/2 z-[1] -translate-y-1/2 text-lg text-surface-400"
              aria-hidden="true"
            />
            <InputText
              id="product-search"
              v-model="search"
              type="search"
              fluid
              size="large"
              class="w-full !ps-12 !text-base"
              :placeholder="$t('ProductsList.SearchPlaceholder')"
              autocomplete="off"
              autocapitalize="off"
              autocorrect="off"
              @keydown.enter="applySearchNow"
            />
          </span>
        </div>

        <div
          class="grid w-full grid-cols-1 gap-5 sm:grid-cols-3 sm:gap-4 lg:flex lg:min-w-0 lg:max-w-4xl lg:flex-1 lg:gap-4"
        >
          <div class="min-w-0 sm:min-w-[10rem] lg:w-48">
            <label
              class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
              for="filter-category"
            >
              {{ $t("ProductsList.CategoryLabel") }}
            </label>
            <Select
              id="filter-category"
              v-model="filters.categoryId"
              size="large"
              :options="categoryOptions"
              option-label="label"
              option-value="value"
              class="w-full"
              scroll-height="min(50vh, 18rem)"
              :placeholder="$t('ProductsList.CategoryPlaceholder')"
              :show-clear="filters.categoryId != null"
              @update:model-value="onFilterChange"
            />
          </div>

          <div class="min-w-0 sm:min-w-[10rem] lg:w-48">
            <label
              class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
              for="filter-type"
            >
              {{ $t("ProductsList.TypeLabel") }}
            </label>
            <Select
              id="filter-type"
              v-model="filters.type"
              size="large"
              :options="typeOptions"
              option-label="label"
              option-value="value"
              class="w-full"
              scroll-height="min(50vh, 18rem)"
              :placeholder="$t('ProductsList.TypePlaceholder')"
              :show-clear="filters.type != null"
              @update:model-value="onFilterChange"
            />
          </div>

          <div class="min-w-0 sm:min-w-[10rem] lg:w-44">
            <label
              class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
              for="filter-active"
            >
              {{ $t("ProductsList.StatusLabel") }}
            </label>
            <Select
              id="filter-active"
              v-model="filters.active"
              size="large"
              :options="activeOptions"
              option-label="label"
              option-value="value"
              class="w-full"
              scroll-height="min(50vh, 18rem)"
              :placeholder="$t('ProductsList.StatusPlaceholder')"
              :show-clear="filters.active != null"
              @update:model-value="onFilterChange"
            />
          </div>
        </div>

        <div class="flex w-full shrink-0 justify-stretch sm:justify-end lg:w-auto lg:pb-1">
          <Button
            type="button"
            outlined
            severity="secondary"
            size="large"
            class="min-h-[48px] w-full sm:min-w-[12rem]"
            :label="$t('ProductsList.ClearFilters')"
            @click="clearFilters"
          >
            <template #icon>
              <AppIcon name="pi pi-filter-slash" />
            </template>
          </Button>
        </div>
      </div>
    </div>

    <div class="mt-8 min-w-0">
      <div :class="productsGridClass">
        <Skeleton
          v-if="isLoading"
          v-for="n in paginator.per_page"
          :key="'sk-' + n"
          width="100%"
          height="260px"
          class="rounded-2xl"
        />

        <template v-else>
          <template v-for="product in products" :key="product.id">
            <ProductCard
              v-if="product"
              :product="product"
              @deleted="handleProductDeleted"
            />
          </template>

          <div
            v-if="!products.length"
            class="col-span-full rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-300"
          >
            {{ $t("ProductsList.Empty") }}
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
import { isAxiosError } from "axios";
import { useDebounceFn } from "@vueuse/core";
import { Button } from "primevue";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { categoriesService } from "../../apis/services/categories/categories.apis";
import { productsService } from "../../apis/services/products/products.apis";
import ProductCard from "../../components/pages/products/Card.vue";
import { useConfigStore } from "../../stores/config";
import { useUserStore } from "../../stores/user";

const { t } = useI18n();
const toast = useToast();
const configStore = useConfigStore();
const { hasPermission } = useUserStore();
const canCreateProduct = hasPermission("products create");
const viewMode = computed(() => configStore.productsViewMode);

const isLoading = ref(true);
const importLoading = ref(false);
const templateLoading = ref(false);
const importInputRef = ref(null);
const products = ref([]);
const categories = ref([]);
const search = ref("");

const filters = ref({
  categoryId: null,
  type: null,
  active: null,
});

const paginator = ref({
  current_page: 1,
  per_page: 12,
  total: 0,
});

const categoryOptions = computed(() => {
  const all = { label: t("ProductsList.AllCategories"), value: null };
  const rest = categories.value.map((c) => ({
    label: c.name || "—",
    value: c.id,
  }));
  return [all, ...rest];
});

const typeOptions = computed(() => [
  { label: t("ProductsList.AllTypes"), value: null },
  { label: t("ProductsList.Types.physical"), value: "physical" },
  { label: t("ProductsList.Types.service_fixed"), value: "service_fixed" },
  { label: t("ProductsList.Types.service_timer"), value: "service_timer" },
]);

const activeOptions = computed(() => [
  { label: t("ProductsList.AllStatuses"), value: null },
  { label: t("UserForm.Active"), value: "active" },
  { label: t("UserForm.Inactive"), value: "inactive" },
]);

const productsGridClass = computed(() => {
  if (viewMode.value === "compact") {
    return "grid grid-cols-1 gap-5 sm:gap-5 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5";
  }
  return "grid grid-cols-1 gap-5 sm:gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4";
});

function buildQueryParams(page = 1) {
  const params = {
    page,
    per_page: paginator.value.per_page,
  };

  if (filters.value.categoryId != null) {
    params.category_id = filters.value.categoryId;
  }

  if (filters.value.type) {
    params.type = filters.value.type;
  }

  if (filters.value.active === "active") {
    params.active = 1;
  } else if (filters.value.active === "inactive") {
    params.active = 0;
  }

  const q = search.value.trim();
  if (q) {
    params.search = q;
  }

  return params;
}

const fetchProducts = (page = 1, showFullLoading = true) => {
  if (showFullLoading) {
    isLoading.value = true;
  }

  productsService
    .getProducts(buildQueryParams(page))
    .then((response) => {
      const data = response.data;
      products.value = data.items ?? [];
      paginator.value = {
        ...paginator.value,
        ...data.meta,
      };
    })
    .catch((error) => {
      console.error("Error fetching products:", error);
    })
    .finally(() => {
      if (showFullLoading) {
        isLoading.value = false;
      }
    });
};

const debouncedSearch = useDebounceFn(() => {
  fetchProducts(1, false);
}, 350);

watch(search, () => {
  debouncedSearch();
});

const onFilterChange = () => {
  fetchProducts(1);
};

const applySearchNow = () => {
  fetchProducts(1);
};

const clearFilters = () => {
  search.value = "";
  filters.value = {
    categoryId: null,
    type: null,
    active: null,
  };
  fetchProducts(1);
};

const handleProductDeleted = () => {
  fetchProducts(paginator.value.current_page, false);
};

const onPageChange = (event) => {
  paginator.value.per_page = event.rows;
  fetchProducts(event.page + 1);
};

const downloadTemplate = async () => {
  templateLoading.value = true;
  try {
    const blob = await productsService.exportProductsTemplate();
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "products-import-template.xlsx";
    a.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error downloading product template:", error);
    toast.add({
      severity: "error",
      summary: t("ProductsList.TemplateDownloadError"),
      life: 4000,
    });
  } finally {
    templateLoading.value = false;
  }
};

const openImportDialog = () => {
  if (importLoading.value) {
    return;
  }
  importInputRef.value?.click();
};

const onImportFileChange = async (event) => {
  const input = event.target;
  const file = input?.files?.[0];
  input.value = "";
  if (!file) {
    return;
  }

  importLoading.value = true;
  try {
    const { data } = await productsService.importProducts(file);
    toast.add({
      severity: "success",
      summary:
        data?.message ??
        t("ProductsList.ImportSuccess", {
          created: data?.created ?? 0,
          updated: data?.updated ?? 0,
        }),
      life: 4500,
    });
    fetchProducts(1);
  } catch (error) {
    const fallbackMessage = t("ProductsList.ImportError");
    if (isAxiosError(error)) {
      const serverMessage = error.response?.data?.message;
      toast.add({
        severity: "error",
        summary: serverMessage || fallbackMessage,
        life: 5500,
      });
    } else {
      toast.add({
        severity: "error",
        summary: fallbackMessage,
        life: 5500,
      });
    }
  } finally {
    importLoading.value = false;
  }
};

const loadCategories = () => {
  categoriesService
    .getCategories({ page: 1, per_page: 100 })
    .then((response) => {
      categories.value = response.data.items ?? [];
    })
    .catch((err) => console.error("Error fetching categories:", err));
};

onMounted(() => {
  configStore.loadProductsViewMode();
  loadCategories();
  fetchProducts();
});
</script>
