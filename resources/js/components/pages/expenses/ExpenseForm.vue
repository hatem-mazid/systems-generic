<template>
    <Skeleton v-if="isLoading" height="28rem" />
    <Card v-else>
        <template #title>
            {{
                expenseId ? $t("ExpenseForm.Edit") : $t("ExpenseForm.Create")
            }}
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 space-y-5 xl:col-span-8">
                    <div>
                        <label
                            for="expense-description"
                            class="mb-2 block font-medium"
                            >{{ $t("ExpenseForm.Description") }}</label
                        >
                        <Textarea
                            id="expense-description"
                            v-model="form.description"
                            class="w-full min-h-[6rem]"
                            fluid
                            auto-resize
                            :invalid="!!validation.description"
                            :placeholder="$t('ExpenseForm.Description')"
                        />
                        <Message
                            v-if="validation.description"
                            severity="error"
                            size="small"
                            variant="simple"
                            class="mt-1"
                        >
                            {{ validation.description[0] }}
                        </Message>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label
                                for="expense-amount"
                                class="mb-2 block font-medium"
                                >{{ $t("ExpenseForm.Amount") }}</label
                            >
                            <InputNumber
                                id="expense-amount"
                                v-model="form.amount"
                                class="w-full"
                                input-class="w-full"
                                :min="0"
                                :max-fraction-digits="2"
                                fluid
                                :invalid="!!validation.amount"
                            />
                            <Message
                                v-if="validation.amount"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.amount[0] }}
                            </Message>
                        </div>
                        <div>
                            <label
                                for="expense-type"
                                class="mb-2 block font-medium"
                                >{{ $t("ExpenseForm.Type") }}</label
                            >
                            <Select
                                id="expense-type"
                                v-model="form.type"
                                :options="typeOptions"
                                option-label="label"
                                option-value="value"
                                class="w-full"
                                fluid
                                size="large"
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
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label
                                for="expense-date"
                                class="mb-2 block font-medium"
                                >{{ $t("ExpenseForm.ExpenseDate") }}</label
                            >
                            <DatePicker
                                id="expense-date"
                                v-model="form.expenseDate"
                                date-format="yy-mm-dd"
                                show-icon
                                fluid
                                size="large"
                                :invalid="!!validation.expense_date"
                                class="w-full"
                            />
                            <Message
                                v-if="validation.expense_date"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.expense_date[0] }}
                            </Message>
                        </div>
                        <div>
                            <label
                                for="expense-by"
                                class="mb-2 block font-medium"
                                >{{ $t("ExpenseForm.ExpenseBy") }}</label
                            >
                            <Select
                                id="expense-by"
                                v-model="form.expenseById"
                                :options="userOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="$t('ExpenseForm.ExpenseByPlaceholder')"
                                class="w-full"
                                fluid
                                size="large"
                                :loading="usersLoading"
                                show-clear
                                :invalid="!!validation.expense_by_id"
                            />
                            <Message
                                v-if="validation.expense_by_id"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.expense_by_id[0] }}
                            </Message>
                        </div>
                    </div>

                    <div
                        v-if="expenseId && createdByLabel"
                        class="rounded-lg border border-surface-200 bg-surface-50/80 p-4 text-sm dark:border-surface-700 dark:bg-surface-800/40"
                    >
                        <span class="font-medium text-surface-700 dark:text-surface-200"
                            >{{ $t("ExpenseForm.CreatedBy") }}:</span
                        >
                        <span class="ms-2 text-surface-600 dark:text-surface-300">{{
                            createdByLabel
                        }}</span>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="mt-2 flex justify-end gap-2">
                <Button
                    type="button"
                    :label="$t('Cancel')"
                    severity="secondary"
                    outlined
                    @click="router.push('/expenses')"
                />
                <Button
                    v-if="expenseId"
                    type="button"
                    :label="$t('ExpenseForm.SubmitUpdate')"
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
                    :label="$t('ExpenseForm.SubmitCreate')"
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
import Button from "primevue/button";
import Card from "primevue/card";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import Message from "primevue/message";
import Select from "primevue/select";
import Skeleton from "primevue/skeleton";
import Textarea from "primevue/textarea";
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import {
    expensesService,
    type Expense,
    type ExpenseUserOption,
} from "../../../apis/services/expenses/expenses.apis";
import {
    EXPENSE_TYPE_VALUES,
    expenseTypeLabelKey,
} from "../../../constants/expenseTypes";

interface ExpenseFormProps {
    expenseId?: string;
}

const props = defineProps<ExpenseFormProps>();
const toast = useToast();
const router = useRouter();
const { t } = useI18n();

const isLoading = ref(!!props.expenseId);
const submitLoading = ref(false);
const usersLoading = ref(false);
const users = ref<ExpenseUserOption[]>([]);
const loadedExpense = ref<Expense | null>(null);

const form = ref({
    description: "",
    amount: null as number | null,
    type: "other" as string,
    expenseDate: null as Date | null,
    expenseById: null as number | null,
});

const validation = ref<Record<string, string[]>>({});

const typeOptions = computed(() =>
    EXPENSE_TYPE_VALUES.map((value) => ({
        value,
        label: t(expenseTypeLabelKey(value)),
    }))
);

const userOptions = computed(() =>
    (users.value ?? []).map((u) => ({
        value: Number(u.id),
        label: u.name ?? String(u.id),
    }))
);

const createdByLabel = computed(() => loadedExpense.value?.created_by?.name ?? "");

function parseYmd(s: string): Date {
    const [y, m, d] = s.split("-").map((x) => parseInt(x, 10));
    return new Date(y, m - 1, d);
}

function toYmd(d: Date | null): string {
    if (!d) {
        return "";
    }
    const dt = d instanceof Date ? d : new Date(d);
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, "0");
    const day = String(dt.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
}

function applyExpense(data: Expense) {
    loadedExpense.value = data;
    form.value = {
        description: data.description ?? "",
        amount: data.amount ?? null,
        type: data.type ?? "other",
        expenseDate: data.expense_date ? parseYmd(data.expense_date) : null,
        expenseById: data.expense_by?.id ?? null,
    };
}

const loadUsers = () => {
    usersLoading.value = true;
    expensesService
        .getUserOptions()
        .then((response) => {
            users.value = response.data.items ?? [];
        })
        .catch(() => {
            toast.add({
                severity: "error",
                summary: t("ExpenseForm.LoadUsersError"),
                life: 4000,
            });
        })
        .finally(() => {
            usersLoading.value = false;
        });
};

function buildPayload() {
    return {
        description: form.value.description.trim(),
        amount: Number(form.value.amount ?? 0),
        type: form.value.type,
        expense_date: toYmd(form.value.expenseDate),
        expense_by_id: form.value.expenseById,
    };
}

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};
    const payload = buildPayload();

    try {
        if (props.expenseId) {
            await expensesService.updateExpense(props.expenseId, payload);
            toast.add({
                severity: "success",
                summary: t("ExpenseForm.UpdatedSuccess"),
                life: 3000,
            });
        } else {
            await expensesService.createExpense(payload);
            toast.add({
                severity: "success",
                summary: t("ExpenseForm.CreatedSuccess"),
                life: 3000,
            });
        }
        router.push("/expenses");
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("ExpenseForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("ExpenseForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    loadUsers();
    if (!props.expenseId) {
        form.value.expenseDate = new Date();
        isLoading.value = false;
        return;
    }
    try {
        const { data } = await expensesService.getExpense(props.expenseId);
        applyExpense(data);
    } catch {
        toast.add({
            severity: "error",
            summary: t("ExpenseForm.LoadError"),
            life: 4000,
        });
        router.push("/expenses");
    } finally {
        isLoading.value = false;
    }
});
</script>
