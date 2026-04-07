<template>
    <Skeleton v-if="isLoading" height="22rem" />
    <Card v-else>
        <template #title>
            {{ unitId ? $t("UnitsForm.Edit") : $t("UnitsForm.Create") }}
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-x-4 gap-y-5">
                <div class="col-span-12 md:col-span-6">
                    <label for="unit-name" class="mb-2 block font-medium">{{
                        $t("UnitsForm.Name")
                    }}</label>
                    <InputText
                        id="unit-name"
                        v-model="form.name"
                        type="text"
                        class="w-full"
                        fluid
                        :invalid="!!validation.name"
                        :placeholder="$t('UnitsForm.Name')"
                    />
                    <Message
                        v-if="validation.name"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.name[0] }}
                    </Message>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label for="unit-group" class="mb-2 block font-medium">{{
                        $t("UnitsForm.UnitGroup")
                    }}</label>
                    <Select
                        id="unit-group"
                        v-model="form.unit_group_id"
                        class="w-full"
                        fluid
                        filter
                        :options="unitGroupOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="$t('UnitsForm.UnitGroup')"
                        :invalid="!!validation.unit_group_id"
                    />
                    <Message
                        v-if="validation.unit_group_id"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.unit_group_id[0] }}
                    </Message>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label for="unit-type" class="mb-2 block font-medium">{{
                        $t("UnitsForm.Type")
                    }}</label>
                    <Select
                        id="unit-type"
                        v-model="form.type"
                        class="w-full"
                        fluid
                        :options="typeOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="$t('UnitsForm.Type')"
                        :invalid="!!validation.type"
                    />
                    <Message
                        v-if="validation.type"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.type[0] }}
                    </Message>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label for="unit-capacity" class="mb-2 block font-medium">{{
                        $t("UnitsForm.Capacity")
                    }}</label>
                    <InputNumber
                        id="unit-capacity"
                        v-model="form.capacity"
                        class="w-full"
                        fluid
                        :min="0"
                        show-buttons
                        :invalid="!!validation.capacity"
                        :placeholder="$t('UnitsForm.Capacity')"
                    />
                    <Message
                        v-if="validation.capacity"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.capacity[0] }}
                    </Message>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label for="unit-position" class="mb-2 block font-medium">{{
                        $t("UnitsForm.Position")
                    }}</label>
                    <InputNumber
                        id="unit-position"
                        v-model="form.position"
                        class="w-full"
                        fluid
                        :min="0"
                        show-buttons
                        :invalid="!!validation.position"
                        :placeholder="$t('UnitsForm.Position')"
                    />
                    <Message
                        v-if="validation.position"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.position[0] }}
                    </Message>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label for="unit-fee" class="mb-2 block font-medium">{{
                        $t("UnitsForm.FeePerHour")
                    }}</label>
                    <InputNumber
                        id="unit-fee"
                        v-model="form.price_per_hour"
                        class="w-full"
                        fluid
                        :min="0"
                        mode="decimal"
                        :min-fraction-digits="0"
                        :max-fraction-digits="2"
                        :invalid="!!validation.price_per_hour"
                        :placeholder="$t('UnitsForm.FeePerHour')"
                    />
                    <Message
                        v-if="validation.price_per_hour"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.price_per_hour[0] }}
                    </Message>
                </div>

                <div class="col-span-12">
                    <span class="mb-2 block font-medium">{{
                        $t("UnitsForm.Status")
                    }}</span>
                    <div class="flex min-h-[2.75rem] items-center gap-3">
                        <ToggleSwitch v-model="form.active" input-id="unit-active" />
                        <label
                            for="unit-active"
                            class="m-0 cursor-pointer text-surface-700 dark:text-surface-200"
                        >
                            {{
                                form.active
                                    ? $t("UnitsForm.Active")
                                    : $t("UnitsForm.Inactive")
                            }}
                        </label>
                    </div>
                    <Message
                        v-if="validation.active"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.active[0] }}
                    </Message>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="mt-2 flex justify-end gap-2">
                <Button
                    v-if="unitId"
                    type="button"
                    :label="$t('UnitsForm.SubmitUpdate')"
                    :loading="submitLoading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <AppIcon name="pi pi-check" />
                    </template>
                </Button>
                <Button
                    v-else
                    type="button"
                    :label="$t('UnitsForm.SubmitCreate')"
                    :loading="submitLoading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <AppIcon name="pi pi-plus" />
                    </template>
                </Button>
            </div>
        </template>
    </Card>
</template>

<script setup lang="ts">
import { isAxiosError } from "axios";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { unitGroupsService } from "../../../apis/services/unitGroups/unitGroups.apis";
import { unitsService } from "../../../apis/services/units/units.apis";
import { UnitType, type UnitWritePayload } from "../../../apis/services/units/units.type";

interface UnitFormProps {
    unitId?: string;
}

const props = defineProps<UnitFormProps>();
const toast = useToast();
const router = useRouter();
const route = useRoute();
const { t } = useI18n();

const isLoading = ref(!!props.unitId);
const submitLoading = ref(false);
const validation = ref<Record<string, string[]>>({});
const unitGroupOptions = ref<{ label: string; value: number }[]>([]);

type UnitFormState = {
    unit_group_id: number;
    name: string;
    type: UnitType | string;
    active: boolean;
    capacity: number | null;
    position: number | null;
    price_per_hour: number | null;
};

const form = ref<UnitFormState>({
    unit_group_id: 0,
    name: "",
    type: UnitType.Table,
    active: true,
    capacity: null,
    position: 0,
    price_per_hour: null,
});

const typeOptions = computed(() => [
    { label: t("Units.Types.table"), value: UnitType.Table },
    { label: t("Units.Types.room"), value: UnitType.Room },
    { label: t("Units.Types.billiard"), value: UnitType.Billiard },
]);

const rawPrefillGroupId = route.query.unit_group_id;
const prefillGroupId =
    rawPrefillGroupId === undefined
        ? undefined
        : Number(Array.isArray(rawPrefillGroupId) ? rawPrefillGroupId[0] : rawPrefillGroupId);

const normalizeOptionalNumber = (value: number | null | undefined) =>
    value === null || value === undefined || Number.isNaN(value) ? null : value;

async function loadUnitGroups() {
    const { data } = await unitGroupsService.getUnitGroups({ page: 1, per_page: 100 });
    unitGroupOptions.value = (data.items ?? [])
        .filter((group) => group.id != null)
        .map((group) => ({
            label: group.name || `#${group.id}`,
            value: Number(group.id),
        }));
}

function buildPayload(): UnitWritePayload {
    return {
        unit_group_id: Number(form.value.unit_group_id),
        name: form.value.name.trim(),
        type: form.value.type,
        active: Boolean(form.value.active),
        capacity: normalizeOptionalNumber(form.value.capacity),
        position: normalizeOptionalNumber(form.value.position),
        price_per_hour: normalizeOptionalNumber(
            form.value.price_per_hour === null
                ? null
                : Number(form.value.price_per_hour)
        ),
    };
}

function applyUnit(data: any) {
    form.value = {
        unit_group_id: Number(data.unit_group_id ?? 0),
        name: data.name ?? "",
        type: data.type ?? UnitType.Table,
        active: Boolean(data.active),
        capacity: data.capacity ?? null,
        position: data.position ?? 0,
        price_per_hour:
            data.price_per_hour === null || data.price_per_hour === undefined
                ? null
                : Number(data.price_per_hour),
    };
}

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};
    const payload = buildPayload();

    try {
        if (props.unitId) {
            await unitsService.updateUnit(props.unitId, payload);
            toast.add({
                severity: "success",
                summary: t("UnitsForm.UpdatedSuccess"),
                life: 3000,
            });
        } else {
            await unitsService.createUnit(payload);
            toast.add({
                severity: "success",
                summary: t("UnitsForm.CreatedSuccess"),
                life: 3000,
            });
        }
        router.push("/unit-groups-setup");
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("UnitsForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("UnitsForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    try {
        await loadUnitGroups();
        if (!props.unitId && prefillGroupId && !Number.isNaN(prefillGroupId)) {
            form.value.unit_group_id = prefillGroupId;
        }

        if (!props.unitId) {
            isLoading.value = false;
            return;
        }

        const { data } = await unitsService.getUnit(props.unitId);
        applyUnit(data);
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitsForm.LoadError"),
            life: 4000,
        });
        router.push("/unit-groups-setup");
    } finally {
        isLoading.value = false;
    }
});
</script>
