<template>
    <div class="touch-manipulation p-4 text-surface-800 dark:text-surface-100 sm:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold sm:text-3xl">
                {{ $t("Dashboard.Title") }}
            </h1>
            <p class="mt-1 text-surface-600 dark:text-surface-400">
                {{ $t("Dashboard.Subtitle") }}
            </p>
        </div>

        <div
            v-if="quickActions.length"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <component
                :is="action.to ? 'RouterLink' : 'button'"
                v-for="action in quickActions"
                :key="action.key"
                :to="action.to"
                type="button"
                class="group flex min-h-[136px] items-center gap-4 rounded-2xl border border-surface-200/80 bg-surface-0 p-5 text-start shadow-sm transition hover:border-primary/40 hover:shadow dark:border-surface-700 dark:bg-surface-900"
                :disabled="action.loading"
                @click="action.onClick ? action.onClick() : null"
            >
                <span
                    class="grid h-14 w-14 shrink-0 place-content-center rounded-xl bg-primary/10 text-primary transition group-hover:bg-primary/15"
                >
                    <AppIcon :name="action.icon" class="text-3xl" />
                </span>
                <span class="min-w-0 flex-1">
                    <span
                        class="block truncate text-lg font-semibold text-surface-800 dark:text-surface-100"
                    >
                        {{ action.label }}
                    </span>
                </span>
                <ProgressSpinner
                    v-if="action.loading"
                    class="!h-6 !w-6 shrink-0"
                    stroke-width="6"
                />
                <AppIcon
                    v-else
                    name="pi pi-arrow-right"
                    class="shrink-0 text-surface-400 transition group-hover:text-primary"
                />
            </component>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-700 dark:text-surface-400"
        >
            {{ $t("Dashboard.NoQuickAccess") }}
        </div>
    </div>
</template>

<script setup>
import { ordersService } from "@/apis/services/orders/orders.apis";
import ProgressSpinner from "primevue/progressspinner";
import { useToast } from "primevue/usetoast";
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const { hasPermission } = useUserStore();

const takeawayCreating = ref(false);

const canViewProducts = computed(() => hasPermission("products index"));
const canCreateProduct = computed(() => hasPermission("products create"));
const canViewUnitsManagement = computed(() => hasPermission("units index"));
const canViewOrders = computed(() => hasPermission("order index"));
const canCreateTakeawayOrder = computed(() => hasPermission("order create"));

const quickActions = computed(() => {
    const actions = [];

    if (canViewProducts.value) {
        actions.push({
            key: "products",
            label: t("Dashboard.Products"),
            icon: "hi-shopping-bag",
            to: "/products",
        });
    }

    if (canViewUnitsManagement.value) {
        actions.push({
            key: "units-management",
            label: t("Dashboard.UnitsManagement"),
            icon: "hi-table",
            to: "/units-management",
        });
    }

    if (canViewOrders.value) {
        actions.push({
            key: "orders",
            label: t("Dashboard.Orders"),
            icon: "hi-receipt-tax",
            to: "/orders",
        });
    }

    if (canCreateTakeawayOrder.value) {
        actions.push({
            key: "new-takeaway-order",
            label: t("Dashboard.NewTakeawayOrder"),
            icon: "pi pi-plus-circle",
            loading: takeawayCreating.value,
            onClick: createTakeawayOrder,
        });
    }

    if (canCreateProduct.value) {
        actions.push({
            key: "create-product",
            label: t("Dashboard.CreateProduct"),
            icon: "pi pi-box",
            to: "/products/create",
        });
    }

    return actions;
});

async function createTakeawayOrder() {
    if (takeawayCreating.value) {
        return;
    }

    takeawayCreating.value = true;
    try {
        const { data } = await ordersService.createOrder();
        const payload = data?.data ?? data;
        if (payload?.id != null) {
            router.push(`/orders/${payload.id}`);
        }
    } catch (error) {
        const message =
            error?.response?.data?.message ?? t("OrdersList.CreateOrderError");
        toast.add({
            severity: "error",
            summary: message,
            life: 5000,
        });
    } finally {
        takeawayCreating.value = false;
    }
}
</script>
