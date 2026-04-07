<template>
    <div class="touch-manipulation">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1
                class="text-2xl text-surface-800 font-semibold dark:text-surface-100 sm:text-3xl"
            >
                {{ $t("Sidebar.UnitGroups") }}
            </h1>

            <Button
                to="/unit-groups-setup/create"
                as="router-link"
                size="large"
                class="min-h-[48px] w-full shrink-0 sm:w-auto"
                :label="$t('Add Unit Group')"
            >
                <template #icon>
                    <AppIcon name="pi pi-plus" />
                </template>
            </Button>
        </div>

        <div class="mt-8 min-w-0 space-y-6">
            <Skeleton
                v-if="isLoading"
                v-for="n in 4"
                :key="'sk-' + n"
                width="100%"
                height="220px"
                class="rounded-2xl"
            />

            <template v-else>
                <UnitGroupCard
                    v-for="unitGroup in unitGroups"
                    :key="unitGroup.id"
                    :unit-group="unitGroup"
                    @deleted="handleUnitGroupDeleted"
                />

                <div
                    v-if="!unitGroups.length"
                    class="rounded-xl border border-dashed border-surface-300 p-8 text-center text-surface-600 dark:border-surface-600 dark:text-surface-300"
                >
                    {{ $t("UnitGroupsList.Empty") }}
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
</template>

<script setup>
import { Button } from "primevue";
import { onMounted, ref } from "vue";
import { unitGroupsService } from "../../apis/services/unitGroups/unitGroups.apis";
import UnitGroupCard from "../../components/pages/unitGroups/UnitGroupCard.vue";

const isLoading = ref(true);
const unitGroups = ref([]);
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
});

const fetchUnitGroups = (page = 1, showFullLoading = true) => {
    if (showFullLoading) {
        isLoading.value = true;
    }

    unitGroupsService
        .getUnitGroups({ page, per_page: paginator.value.per_page })
        .then((response) => {
            const data = response.data;
            unitGroups.value = data.items ?? [];
            paginator.value = {
                ...paginator.value,
                ...data.meta,
            };
        })
        .catch((error) => {
            console.error("Error fetching unit groups:", error);
        })
        .finally(() => {
            if (showFullLoading) {
                isLoading.value = false;
            }
        });
};

const handleUnitGroupDeleted = () => {
    fetchUnitGroups(paginator.value.current_page, false);
};

const onPageChange = (event) => {
    paginator.value.per_page = event.rows;
    fetchUnitGroups(event.page + 1);
};

onMounted(() => {
    fetchUnitGroups();
});
</script>
