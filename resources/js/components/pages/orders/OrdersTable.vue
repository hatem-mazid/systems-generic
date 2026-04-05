<template>
    <div class="orders-table-wrap relative min-w-0">
        <DataTable
            :value="orders"
            :loading="loading"
            data-key="id"
            striped-rows
            class="orders-table w-full min-w-0"
            :pt="{
                table: { class: 'w-full' },
            }"
        >
            <template #empty>
                <div
                    class="py-6 text-center text-surface-600 dark:text-surface-300"
                >
                    {{ emptyMessage }}
                </div>
            </template>

            <Column
                field="id"
                :header="$t('OrdersList.ColumnId')"
                class="w-[4.5rem]"
            >
                <template #body="{ data }">
                    <span class="tabular-nums">{{ data.id }}</span>
                </template>
            </Column>

            <Column field="unit_name" :header="$t('OrdersList.ColumnUnit')">
                <template #body="{ data }">
                    {{ data.unit_name ?? data.unit_id ?? "—" }}
                </template>
            </Column>

            <Column field="total" :header="$t('OrdersList.ColumnTotal')">
                <template #body="{ data }">
                    <span class="tabular-nums">{{
                        formatTotal(data.total)
                    }}</span>
                </template>
            </Column>

            <Column
                field="user_name"
                :header="$t('OrdersList.ColumnUserName')"
            >
                <template #body="{ data }">
                    {{ data.user_name ?? "—" }}
                </template>
            </Column>

            <Column field="status" :header="$t('OrdersList.ColumnStatus')">
                <template #body="{ data }">
                    <Tag
                        v-if="data.status"
                        :value="statusLabel(data.status)"
                        :severity="statusSeverity(data.status)"
                        class="text-xs capitalize"
                    />
                    <span v-else>—</span>
                </template>
            </Column>

            <Column :header="$t('OrdersList.ColumnOpenedCreated')">
                <template #body="{ data }">
                    <div class="flex flex-col gap-1.5">
                        <Chip
                            icon="pi pi-clock"
                            class="w-fit max-w-full text-xs"
                            :label="dateChipLabel('OrdersList.OpenedShort', data.opened_at)"
                            :title="dateChipTitle('OrdersList.OpenedShort', data.opened_at)"
                        />
                        <Chip
                            icon="pi pi-calendar-plus"
                            class="w-fit max-w-full text-xs"
                            :label="dateChipLabel('OrdersList.CreatedShort', data.created_at)"
                            :title="dateChipTitle('OrdersList.CreatedShort', data.created_at)"
                        />
                    </div>
                </template>
            </Column>

            <Column
                :header="$t('OrdersList.ColumnActions')"
                class="w-[4.5rem] text-end"
                :exportable="false"
            >
                <template #body="{ data }">
                    <div class="flex justify-end">
                        <Button
                            type="button"
                            size="large"
                            rounded
                            outlined
                            severity="secondary"
                            icon="pi pi-ellipsis-v"
                            class="shrink-0"
                            :aria-label="$t('OrdersList.MoreActions')"
                            v-tooltip.top="$t('OrdersList.MoreActions')"
                            @click="openRowMenu($event, data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <Menu ref="rowMenu" :model="rowMenuItems" popup />
    </div>
</template>

<script setup>
import Button from "primevue/button";
import Chip from "primevue/chip";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Menu from "primevue/menu";
import Tag from "primevue/tag";
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { OrderStatus } from "../../../apis/services/orders/orders.type";

defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["view", "print"]);

const { t } = useI18n();

const rowMenu = ref();
const activeRow = ref(null);

const rowMenuItems = computed(() => {
    if (!activeRow.value) {
        return [];
    }
    return [
        {
            label: t("OrdersList.ViewOrder"),
            icon: "pi pi-eye",
            command: () => {
                emit("view", activeRow.value.id);
            },
        },
        {
            label: t("OrdersList.PrintInvoice"),
            icon: "pi pi-print",
            command: () => {
                emit("print", activeRow.value);
            },
        },
    ];
});

function openRowMenu(event, data) {
    activeRow.value = data;
    rowMenu.value?.toggle(event);
}

function formatTotal(value) {
    if (value === undefined || value === null || value === "") {
        return "—";
    }
    const n = Number(value);
    if (Number.isNaN(n)) {
        return String(value);
    }
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "IQD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(n);
}

function formatDt(iso) {
    if (!iso) {
        return "—";
    }
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

/** Chip label: "Opened: …" / "Created: …" with formatted or em dash. */
function dateChipLabel(i18nKey, iso) {
    return `${t(i18nKey)}: ${formatDt(iso)}`;
}

/** Full tooltip for truncated or long values. */
function dateChipTitle(i18nKey, iso) {
    return dateChipLabel(i18nKey, iso);
}

function statusLabel(status) {
    const key = `OrdersList.Statuses.${status}`;
    const translated = t(key);
    return translated === key ? status : translated;
}

function statusSeverity(status) {
    switch (status) {
        case OrderStatus.Open:
            return "success";
        case OrderStatus.Pending:
            return "warn";
        case OrderStatus.Closed:
            return "secondary";
        case OrderStatus.Cancelled:
            return "danger";
        default:
            return "secondary";
    }
}
</script>

<style scoped>
.orders-table :deep(.p-datatable-tbody > tr) {
    min-height: 48px;
    height: auto;
}

.orders-table :deep(.p-datatable-tbody > tr > td) {
    vertical-align: middle;
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
}
</style>
