<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("OrdersList.Title") }}
            </h1>
        </div>

        <div
            class="mt-6 rounded-2xl border border-surface-200/80 bg-surface-0 p-5 shadow-sm sm:p-6 dark:border-surface-700 dark:bg-surface-900"
        >
            <div
                class="flex flex-col gap-5 lg:flex-row lg:flex-wrap lg:items-end lg:gap-6"
            >
                <div
                    class="grid w-full grid-cols-1 gap-5 sm:grid-cols-2 lg:flex lg:min-w-0 lg:flex-1 lg:gap-4"
                >
                    <div class="min-w-0 sm:min-w-[11rem] lg:w-52">
                        <label
                            class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                            for="orders-date-from"
                        >
                            {{ $t("OrdersList.FilterDateFrom") }}
                        </label>
                        <DatePicker
                            id="orders-date-from"
                            v-model="filters.dateFrom"
                            date-format="yy-mm-dd"
                            show-icon
                            fluid
                            size="large"
                            :show-clear="!!filters.dateFrom"
                            @update:model-value="onFilterChange"
                        />
                    </div>
                    <div class="min-w-0 sm:min-w-[11rem] lg:w-52">
                        <label
                            class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                            for="orders-date-to"
                        >
                            {{ $t("OrdersList.FilterDateTo") }}
                        </label>
                        <DatePicker
                            id="orders-date-to"
                            v-model="filters.dateTo"
                            date-format="yy-mm-dd"
                            show-icon
                            fluid
                            size="large"
                            :show-clear="!!filters.dateTo"
                            @update:model-value="onFilterChange"
                        />
                    </div>
                    <div class="min-w-0 sm:min-w-[10rem] lg:w-44">
                        <label
                            class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                            for="orders-status"
                        >
                            {{ $t("OrdersList.FilterStatus") }}
                        </label>
                        <Select
                            id="orders-status"
                            v-model="filters.status"
                            size="large"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            scroll-height="min(50vh, 18rem)"
                            :placeholder="
                                $t('OrdersList.FilterStatusPlaceholder')
                            "
                            :show-clear="filters.status != null"
                            @update:model-value="onFilterChange"
                        />
                    </div>
                    <div
                        class="min-w-0 sm:min-w-[12rem] lg:min-w-[14rem] lg:max-w-xs lg:flex-1"
                    >
                        <label
                            class="mb-2 block text-base font-medium text-surface-700 dark:text-surface-200"
                            for="orders-waiter"
                        >
                            {{ $t("OrdersList.FilterWaiter") }}
                        </label>
                        <Select
                            id="orders-waiter"
                            v-model="filters.userId"
                            size="large"
                            :options="waiterOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            scroll-height="min(50vh, 18rem)"
                            :placeholder="
                                $t('OrdersList.FilterWaiterPlaceholder')
                            "
                            :show-clear="filters.userId != null"
                            :loading="usersLoading"
                            @update:model-value="onFilterChange"
                        />
                    </div>
                </div>

                <div
                    class="flex w-full shrink-0 justify-stretch sm:justify-end lg:w-auto lg:pb-1"
                >
                    <Button
                        type="button"
                        outlined
                        severity="secondary"
                        size="large"
                        class="min-h-[48px] w-full sm:min-w-[12rem]"
                        :label="$t('OrdersList.ClearFilters')"
                        @click="clearFilters"
                    >
                        <template #icon>
                            <AppIcon name="pi pi-filter-slash" />
                        </template>
                    </Button>
                </div>
            </div>
        </div>

        <div
            class="mt-8 min-w-0 overflow-x-auto rounded-2xl border border-surface-200/80 bg-surface-0 p-1 shadow-sm dark:border-surface-700 dark:bg-surface-900 sm:p-2"
        >
            <OrdersTable
                :orders="orders"
                :loading="isLoading"
                :empty-message="$t('OrdersList.Empty')"
                @view="onViewOrder"
                @invoice="onInvoiceOrder"
            />
        </div>

        <Paginator
            class="pagination-touch mt-6 bg-transparent"
            :rows="paginator.per_page"
            :total-records="paginator.total"
            :first="(paginator.current_page - 1) * paginator.per_page"
            @page="onPageChange"
        />
    </div>
</template>

<script setup>
import { Button } from "primevue";
import DatePicker from "primevue/datepicker";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { ordersService } from "../../apis/services/orders/orders.apis";
import { OrderStatus } from "../../apis/services/orders/orders.type";
import { usersService } from "../../apis/services/users/users.apis";
import { UserRole } from "../../apis/services/users/users.type";
import OrdersTable from "../../components/pages/orders/OrdersTable.vue";

const { t } = useI18n();
const router = useRouter();

const isLoading = ref(true);
const usersLoading = ref(true);
const orders = ref([]);
const users = ref([]);

const filters = ref({
    dateFrom: null,
    dateTo: null,
    status: null,
    userId: null,
});

const paginator = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
});

const statusOptions = computed(() => [
    { label: t("OrdersList.AllStatuses"), value: null },
    { label: t("OrdersList.Statuses.active"), value: OrderStatus.Active },
    { label: t("OrdersList.Statuses.reserved"), value: OrderStatus.Reserved },
    { label: t("OrdersList.Statuses.closed"), value: OrderStatus.Closed },
    { label: t("OrdersList.Statuses.cancelled"), value: OrderStatus.Cancelled },
]);

const waiterOptions = computed(() => {
    const all = { label: t("OrdersList.AllWaiters"), value: null };
    const rest = users.value.map((u) => ({
        label: u.name || u.email || String(u.id),
        value: u.id,
    }));
    return [all, ...rest];
});

function toYmd(d) {
    if (!d) {
        return null;
    }
    const dt = d instanceof Date ? d : new Date(d);
    if (Number.isNaN(dt.getTime())) {
        return null;
    }
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, "0");
    const day = String(dt.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
}

function buildQueryParams(page = 1) {
    const params = {
        page,
        per_page: paginator.value.per_page,
    };

    if (filters.value.status) {
        params.status = filters.value.status;
    }

    if (filters.value.userId != null && filters.value.userId !== "") {
        params.user_id = filters.value.userId;
    }

    const from = toYmd(filters.value.dateFrom);
    if (from) {
        params.date_from = from;
    }

    const to = toYmd(filters.value.dateTo);
    if (to) {
        params.date_to = to;
    }

    return params;
}

const fetchOrders = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }
    ordersService
        .getOrders(buildQueryParams(page))
        .then((response) => {
            const data = response.data;
            orders.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((error) => {
            console.error("Error fetching orders:", error);
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const loadUsers = () => {
    usersLoading.value = true;
    usersService
        .getUsers({ page: 1, per_page: 100, role: UserRole.Waiter })
        .then((response) => {
            users.value = response.data.items ?? [];
        })
        .catch((err) => console.error("Error fetching users:", err))
        .finally(() => {
            usersLoading.value = false;
        });
};

const onFilterChange = () => {
    fetchOrders(1);
};

const clearFilters = () => {
    filters.value = {
        dateFrom: null,
        dateTo: null,
        status: null,
        userId: null,
    };
    fetchOrders(1);
};

const onPageChange = (event) => {
    paginator.value.per_page = event.rows;
    fetchOrders(event.page + 1);
};

function onViewOrder(id) {
    if (id == null) {
        return;
    }
    router.push(`/orders/${id}`);
}

function onInvoiceOrder(id) {
    if (id == null) {
        return;
    }
    router.push(`/orders/${id}/invoice`);
}

onMounted(() => {
    loadUsers();
    fetchOrders();
});
</script>
