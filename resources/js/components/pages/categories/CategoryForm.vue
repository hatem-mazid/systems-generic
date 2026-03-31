<template>
    <Skeleton v-if="isLoading" height="28rem" />
    <Card v-else>
        <template #title>
            {{
                categoryId ? $t("CategoryForm.Edit") : $t("CategoryForm.Create")
            }}
        </template>

        <template #content>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 space-y-6 xl:col-span-8">
                    <Tabs
                        v-model:value="activeLocale"
                        class="category-locale-tabs"
                    >
                        <TabList>
                            <Tab value="en">{{
                                $t("CategoryForm.LocaleEnglish")
                            }}</Tab>
                            <Tab value="ar" dir="rtl">
                                {{ $t("CategoryForm.LocaleArabic") }}
                            </Tab>
                        </TabList>
                        <TabPanels class="pt-4">
                            <TabPanel value="en">
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            for="category-name-en"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("CategoryForm.Title")
                                            }}</label
                                        >
                                        <InputText
                                            id="category-name-en"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full"
                                            fluid
                                            :invalid="!!validation.name"
                                            :placeholder="
                                                $t('CategoryForm.Title')
                                            "
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
                                        <label
                                            for="category-desc-en"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("CategoryForm.Description")
                                            }}</label
                                        >
                                        <Textarea
                                            id="category-desc-en"
                                            v-model="form.description"
                                            class="w-full min-h-[7rem]"
                                            fluid
                                            auto-resize
                                            :invalid="!!validation.description"
                                            :placeholder="
                                                $t('CategoryForm.Description')
                                            "
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
                                </div>
                            </TabPanel>
                            <TabPanel value="ar">
                                <div class="space-y-5" dir="rtl">
                                    <div>
                                        <label
                                            for="category-name-ar"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("CategoryForm.Title")
                                            }}</label
                                        >
                                        <InputText
                                            id="category-name-ar"
                                            v-model="form.arName"
                                            type="text"
                                            class="w-full"
                                            fluid
                                            :invalid="
                                                !!validation[
                                                    'translations.ar.name'
                                                ]
                                            "
                                            :placeholder="
                                                $t('CategoryForm.Title')
                                            "
                                        />
                                        <Message
                                            v-if="
                                                validation[
                                                    'translations.ar.name'
                                                ]
                                            "
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{
                                                validation[
                                                    "translations.ar.name"
                                                ][0]
                                            }}
                                        </Message>
                                    </div>
                                    <div>
                                        <label
                                            for="category-desc-ar"
                                            class="mb-2 block font-medium"
                                            >{{
                                                $t("CategoryForm.Description")
                                            }}</label
                                        >
                                        <Textarea
                                            id="category-desc-ar"
                                            v-model="form.arDescription"
                                            class="w-full min-h-[7rem]"
                                            fluid
                                            auto-resize
                                            :invalid="
                                                !!validation[
                                                    'translations.ar.description'
                                                ]
                                            "
                                            :placeholder="
                                                $t('CategoryForm.Description')
                                            "
                                        />
                                        <Message
                                            v-if="
                                                validation[
                                                    'translations.ar.description'
                                                ]
                                            "
                                            severity="error"
                                            size="small"
                                            variant="simple"
                                            class="mt-1"
                                        >
                                            {{
                                                validation[
                                                    "translations.ar.description"
                                                ][0]
                                            }}
                                        </Message>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>

                    <Divider />

                    <div
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:items-end"
                    >
                        <div>
                            <label
                                for="category-order"
                                class="mb-2 block font-medium"
                                >{{ $t("CategoryForm.Order") }}</label
                            >
                            <InputNumber
                                id="category-order"
                                v-model="form.order"
                                class="w-full"
                                input-id="category-order"
                                fluid
                                :min="0"
                                show-buttons
                                :invalid="!!validation.order"
                                :placeholder="$t('CategoryForm.Order')"
                            />
                            <Message
                                v-if="validation.order"
                                severity="error"
                                size="small"
                                variant="simple"
                                class="mt-1"
                            >
                                {{ validation.order[0] }}
                            </Message>
                        </div>
                        <div>
                            <span class="mb-2 block font-medium">{{
                                $t("CategoryForm.Status")
                            }}</span>
                            <div
                                class="flex min-h-[2.75rem] items-center gap-3"
                            >
                                <ToggleSwitch
                                    v-model="form.active"
                                    input-id="category-active"
                                />
                                <label
                                    for="category-active"
                                    class="m-0 cursor-pointer text-surface-700 dark:text-surface-200"
                                >
                                    {{
                                        form.active
                                            ? $t("CategoryForm.Active")
                                            : $t("CategoryForm.Inactive")
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
                </div>

                <div class="col-span-12 xl:col-span-4 xl:self-start">
                    <aside
                        class="flex w-full flex-col rounded-xl border border-surface-200 bg-surface-50/80 p-4 dark:border-surface-700 dark:bg-surface-800/40"
                    >
                        <p
                            class="mb-3 text-sm font-semibold text-surface-800 dark:text-surface-100"
                        >
                            {{ $t("CategoryForm.Image") }}
                        </p>

                        <div
                            v-if="hasImage"
                            class="relative aspect-[4/3] w-full overflow-hidden rounded-lg bg-surface-200 dark:bg-surface-700"
                        >
                            <img
                                :src="displayImageUrl ?? ''"
                                alt=""
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <Button
                                type="button"
                                size="small"
                                outlined
                                severity="secondary"
                                :icon="hasImage ? 'pi pi-image' : 'pi pi-plus'"
                                :label="
                                    hasImage
                                        ? $t('CategoryForm.ChangeImage')
                                        : $t('CategoryForm.AddImage')
                                "
                                :disabled="removingImage"
                                @click="openFileDialog"
                            />
                            <Button
                                v-if="hasImage"
                                type="button"
                                size="small"
                                outlined
                                severity="danger"
                                icon="pi pi-trash"
                                :label="$t('CategoryForm.RemoveImage')"
                                :loading="removingImage"
                                :disabled="removingImage"
                                @click="onRemoveImage"
                            />
                        </div>

                        <input
                            ref="fileInputRef"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="onFileChange"
                        />
                    </aside>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="mt-2 flex justify-end gap-2">
                <Button
                    v-if="categoryId"
                    type="button"
                    :label="$t('CategoryForm.SubmitUpdate')"
                    icon="pi pi-check"
                    :loading="submitLoading"
                    @click="onSubmit"
                />
                <Button
                    v-else
                    type="button"
                    :label="$t('CategoryForm.SubmitCreate')"
                    icon="pi pi-plus"
                    :loading="submitLoading"
                    @click="onSubmit"
                />
            </div>
        </template>
    </Card>
</template>

<script setup lang="ts">
import { isAxiosError } from "axios";
import Tab from "primevue/tab";
import TabList from "primevue/tablist";
import TabPanel from "primevue/tabpanel";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import { useToast } from "primevue/usetoast";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { categoriesService } from "../../../apis/services/categories/categories.apis";
import type {
    Category,
    CategoryTranslation,
    CategoryWritePayload,
} from "../../../apis/services/categories/categories.type";

interface CategoryFormProps {
    categoryId?: string;
}

const props = defineProps<CategoryFormProps>();
const toast = useToast();
const router = useRouter();
const { t } = useI18n();

const isLoading = ref(!!props.categoryId);
const submitLoading = ref(false);
const removingImage = ref(false);
const activeLocale = ref("en");
const fileInputRef = ref<HTMLInputElement | null>(null);
const pendingFile = ref<File | null>(null);
const previewObjectUrl = ref<string | null>(null);
const loadedCategory = ref<Category | null>(null);

const form = ref({
    name: "",
    description: "",
    arName: "",
    arDescription: "",
    order: null as number | null,
    active: true,
});

const validation = ref<Record<string, string[]>>({});

function pickTranslation(
    translations: CategoryTranslation[] | undefined,
    locale: string,
    key: string
): string {
    const row = translations?.find(
        (tr) => tr.locale === locale && tr.key === key
    );
    return row?.value ?? "";
}

function applyCategory(data: Category) {
    loadedCategory.value = data;
    form.value = {
        name: data.name ?? "",
        description: data.description ?? "",
        arName: pickTranslation(data.translations, "ar", "name"),
        arDescription: pickTranslation(data.translations, "ar", "description"),
        order: data.order ?? null,
        active: Boolean(data.active),
    };
}

const imageFromServer = computed(() => {
    const media = loadedCategory.value?.media ?? [];
    const image = media.find((m) => m.collection_name === "image") || media[0];
    return image?.url ?? null;
});

const displayImageUrl = computed(
    () => previewObjectUrl.value ?? imageFromServer.value
);

/** True when a preview (pending file) or saved media URL is available. */
const hasImage = computed(() => Boolean(displayImageUrl.value));

watch(pendingFile, (file) => {
    if (previewObjectUrl.value) {
        URL.revokeObjectURL(previewObjectUrl.value);
        previewObjectUrl.value = null;
    }
    if (file) {
        previewObjectUrl.value = URL.createObjectURL(file);
    }
});

onBeforeUnmount(() => {
    if (previewObjectUrl.value) {
        URL.revokeObjectURL(previewObjectUrl.value);
    }
});

function clearPendingFile() {
    pendingFile.value = null;
    if (previewObjectUrl.value) {
        URL.revokeObjectURL(previewObjectUrl.value);
        previewObjectUrl.value = null;
    }
}

function openFileDialog() {
    fileInputRef.value?.click();
}

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = "";
    if (!file) {
        return;
    }
    pendingFile.value = file;
}

function buildPayload(): CategoryWritePayload {
    return {
        name: form.value.name.trim(),
        description: form.value.description.trim() || null,
        order: form.value.order ?? null,
        active: form.value.active,
        translations: {
            ar: {
                name: form.value.arName.trim(),
                description: form.value.arDescription.trim() || null,
            },
        },
    };
}

async function onRemoveImage() {
    if (!hasImage.value) {
        return;
    }

    if (!props.categoryId) {
        clearPendingFile();
        return;
    }

    removingImage.value = true;
    try {
        const { data } = await categoriesService.deleteCategoryMedia(
            props.categoryId
        );
        applyCategory(data);
        clearPendingFile();
        toast.add({
            severity: "success",
            summary: t("CategoryForm.ImageRemoved"),
            life: 2500,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t("CategoryForm.ImageRemoveError"),
            life: 4000,
        });
    } finally {
        removingImage.value = false;
    }
}

const onSubmit = async () => {
    submitLoading.value = true;
    validation.value = {};

    const payload = buildPayload();

    try {
        if (props.categoryId) {
            const res = await categoriesService.updateCategory(
                props.categoryId,
                payload
            );
            applyCategory(res.data);

            if (pendingFile.value) {
                const up = await categoriesService.uploadCategoryMedia(
                    props.categoryId,
                    {
                        file: pendingFile.value,
                        collection: "image",
                        replace: true,
                    }
                );
                applyCategory(up.data);
                clearPendingFile();
            }

            toast.add({
                severity: "success",
                summary: t("CategoryForm.UpdatedSuccess"),
                life: 3000,
            });
            router.push("/categories");
        } else {
            const res = await categoriesService.createCategory(payload);
            const newId = res.data.id;

            if (pendingFile.value && newId != null) {
                await categoriesService.uploadCategoryMedia(newId, {
                    file: pendingFile.value,
                    collection: "image",
                    replace: true,
                });
            }

            clearPendingFile();
            toast.add({
                severity: "success",
                summary: t("CategoryForm.CreatedSuccess"),
                life: 3000,
            });
            router.push("/categories");
        }
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            const data = error.response?.data as
                | { errors?: Record<string, string[]>; message?: string }
                | undefined;
            validation.value = data?.errors ?? {};
            toast.add({
                severity: "error",
                summary: data?.message ?? t("CategoryForm.ValidationError"),
                life: 4000,
            });
        } else if (isAxiosError(error)) {
            toast.add({
                severity: "error",
                summary: t("CategoryForm.SaveError"),
                life: 4000,
            });
        }
    } finally {
        submitLoading.value = false;
    }
};

onMounted(async () => {
    if (!props.categoryId) {
        isLoading.value = false;
        return;
    }
    try {
        const { data } = await categoriesService.getCategory(props.categoryId);
        applyCategory(data);
    } catch {
        toast.add({
            severity: "error",
            summary: t("CategoryForm.LoadError"),
            life: 4000,
        });
        router.push("/categories");
    } finally {
        isLoading.value = false;
    }
});
</script>

<style scoped>
.category-locale-tabs :deep(.p-tablist-tab-list) {
    flex-wrap: wrap;
}
</style>
