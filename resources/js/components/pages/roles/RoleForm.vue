<template>
    <Skeleton v-if="isLoading" height="20rem" />
    <Card v-else>
        <template #title>
            {{ roleId ? $t("RoleForm.Edit") : $t("RoleForm.Create") }}
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-x-4 gap-y-5">
                <div class="col-span-12">
                    <label for="role-name" class="block mb-2">{{
                        $t("RoleForm.Name")
                    }}</label>
                    <InputGroup>
                        <InputGroupAddon>
                            <AppIcon name="pi pi-users" />
                        </InputGroupAddon>
                        <InputText
                            id="role-name"
                            v-model="item.name"
                            type="text"
                            class="w-full"
                            fluid
                            :invalid="!!validation.name"
                            :placeholder="$t('RoleForm.Name')"
                        />
                    </InputGroup>
                    <Message
                        v-if="validation.name"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.name[0] }}
                    </Message>
                </div>

                <div class="col-span-12">
                    <label for="role-permissions" class="block mb-2">{{
                        $t("RoleForm.Permissions")
                    }}</label>
                    <MultiSelect
                        id="role-permissions"
                        v-model="item.permissions"
                        class="w-full"
                        fluid
                        display="chip"
                        filter
                        :options="permissionOptions"
                        :placeholder="$t('RoleForm.PermissionsPlaceholder')"
                        :invalid="!!validation.permissions"
                    />
                    <Message
                        v-if="validation.permissions"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ validation.permissions[0] }}
                    </Message>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-end mt-4 gap-2">
                <Button
                    type="button"
                    :label="
                        roleId ? $t('RoleForm.SubmitUpdate') : $t('RoleForm.SubmitCreate')
                    "
                    :loading="submitLoading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <AppIcon :name="roleId ? 'pi pi-check' : 'pi pi-plus'" />
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
import { rolesService } from "../../../apis/services/roles/roles.apis";
import type { Role } from "../../../apis/services/roles/roles.type";

interface RoleFormProps {
    roleId?: string;
}

const props = defineProps<RoleFormProps>();
const router = useRouter();
const toast = useToast();
const { t } = useI18n();

const isLoading = ref(true);
const submitLoading = ref(false);
const permissionOptions = ref<string[]>([]);

const item = ref<Role>({
    name: "",
    permissions: [],
});

const validation = ref<Record<string, string[]>>({});

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};

    try {
        if (props.roleId) {
            await rolesService.updateRole(props.roleId, item.value);
            toast.add({
                severity: "success",
                summary: t("RoleForm.UpdatedSuccess"),
                life: 3000,
            });
        } else {
            await rolesService.createRole(item.value);
            toast.add({
                severity: "success",
                summary: t("RoleForm.CreatedSuccess"),
                life: 3000,
            });
        }

        router.push("/roles");
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("RoleForm.ValidationError"),
                life: 4000,
            });
        } else {
            toast.add({
                severity: "error",
                summary: t("RoleForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    try {
        const permissionsRes = await rolesService.getPermissions();
        permissionOptions.value = permissionsRes.data.items ?? [];

        if (props.roleId) {
            const { data } = await rolesService.getRole(props.roleId);
            item.value = {
                ...item.value,
                ...data,
                permissions: data.permissions ?? [],
            };
        }
    } finally {
        isLoading.value = false;
    }
});
</script>
