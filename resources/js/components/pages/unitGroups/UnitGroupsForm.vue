<template>
    <Skeleton v-if="isLoading" height="22rem" />
    <Card v-else>
        <template #title>
            {{
                unitGroupId
                    ? $t("UnitGroupsForm.Edit")
                    : $t("UnitGroupsForm.Create")
            }}
        </template>

        <template #content>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:items-end">
                <div class="sm:col-span-2">
                    <label for="unit-group-name" class="mb-2 block font-medium">{{
                        $t("UnitGroupsForm.Name")
                    }}</label>
                    <InputText
                        id="unit-group-name"
                        v-model="form.name"
                        type="text"
                        class="w-full"
                        fluid
                        :invalid="!!validation.name"
                        :placeholder="$t('UnitGroupsForm.Name')"
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

                <div>
                    <label for="unit-group-position" class="mb-2 block font-medium">{{
                        $t("UnitGroupsForm.Position")
                    }}</label>
                    <InputNumber
                        id="unit-group-position"
                        v-model="form.position"
                        class="w-full"
                        input-id="unit-group-position"
                        fluid
                        :min="0"
                        show-buttons
                        :invalid="!!validation.position"
                        :placeholder="$t('UnitGroupsForm.Position')"
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

                <div>
                    <span class="mb-2 block font-medium">{{
                        $t("UnitGroupsForm.Status")
                    }}</span>
                    <div class="flex min-h-[2.75rem] items-center gap-3">
                        <ToggleSwitch
                            v-model="form.is_active"
                            input-id="unit-group-active"
                        />
                        <label
                            for="unit-group-active"
                            class="m-0 cursor-pointer text-surface-700 dark:text-surface-200"
                        >
                            {{
                                form.is_active
                                    ? $t("UnitGroupsForm.Active")
                                    : $t("UnitGroupsForm.Inactive")
                            }}
                        </label>
                    </div>
                    <Message
                        v-if="validation.is_active"
                        severity="error"
                        size="small"
                        variant="simple"
                        class="mt-1"
                    >
                        {{ validation.is_active[0] }}
                    </Message>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="mt-2 flex justify-end gap-2">
                <Button
                    v-if="unitGroupId"
                    type="button"
                    :label="$t('UnitGroupsForm.SubmitUpdate')"
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
                    :label="$t('UnitGroupsForm.SubmitCreate')"
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
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { unitGroupsService } from "../../../apis/services/unitGroups/unitGroups.apis";
import type {
    UnitGroup,
    UnitGroupWritePayload,
} from "../../../apis/services/unitGroups/unitGroups.type";

interface UnitGroupsFormProps {
    unitGroupId?: string;
}

const props = defineProps<UnitGroupsFormProps>();
const toast = useToast();
const router = useRouter();
const { t } = useI18n();

const isLoading = ref(!!props.unitGroupId);
const submitLoading = ref(false);
const validation = ref<Record<string, string[]>>({});

const form = ref({
    name: "",
    position: 1,
    is_active: true,
});

function applyUnitGroup(data: UnitGroup) {
    form.value = {
        name: data.name ?? "",
        position: data.position ?? 1,
        is_active: Boolean(data.is_active),
    };
}

function buildPayload(): UnitGroupWritePayload {
    return {
        name: form.value.name.trim(),
        position: form.value.position ?? 1,
        is_active: form.value.is_active,
    };
}

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};

    const payload = buildPayload();

    try {
        if (props.unitGroupId) {
            await unitGroupsService.updateUnitGroup(props.unitGroupId, payload);
            toast.add({
                severity: "success",
                summary: t("UnitGroupsForm.UpdatedSuccess"),
                life: 3000,
            });
        } else {
            await unitGroupsService.createUnitGroup(payload);
            toast.add({
                severity: "success",
                summary: t("UnitGroupsForm.CreatedSuccess"),
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
                summary: data?.message ?? t("UnitGroupsForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("UnitGroupsForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    if (!props.unitGroupId) {
        isLoading.value = false;
        return;
    }

    try {
        const { data } = await unitGroupsService.getUnitGroup(props.unitGroupId);
        applyUnitGroup(data);
    } catch {
        toast.add({
            severity: "error",
            summary: t("UnitGroupsForm.LoadError"),
            life: 4000,
        });
        router.push("/unit-groups-setup");
    } finally {
        isLoading.value = false;
    }
});
</script>
