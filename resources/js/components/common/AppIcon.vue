<template>
    <v-icon :name="normalizedName" />
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});

const normalizedName = computed(() => {
    const raw = String(props.name || "").trim();
    const nameMap = {
        "plus": "hi-plus",
        "plus-circle": "hi-plus-circle",
        "search": "hi-search",
        "filter-slash": "hi-filter",
        "trash": "hi-trash",
        "pencil": "hi-pencil-alt",
        "image": "hi-photograph",
        "images": "hi-collection",
        "check": "hi-check",
        "key": "hi-key",
        "arrow-left": "hi-arrow-left",
        "arrow-right": "hi-arrow-right",
        "print": "hi-printer",
        "external-link": "hi-external-link",
        "minus": "hi-minus",
        "clock": "hi-clock",
        "calendar-plus": "hi-calendar",
        "ellipsis-v": "hi-dots-vertical",
        "ellipsis-h": "hi-dots-horizontal",
        "receipt": "hi-receipt-tax",
        "users": "hi-users",
        "check-circle": "hi-check-circle",
        "home": "hi-home",
        "stop": "hi-stop",
        "box": "hi-cube",
        "star": "hi-star",
        "unlock": "hi-lock-open",
        "shopping-cart": "hi-shopping-cart",
        "times-circle": "hi-x-circle",
        "play": "hi-play",
        "eye": "hi-eye",
        "language": "hi-translate",
        "list": "hi-view-list",
        "sun": "hi-sun",
        "cog": "hi-cog",
        "inbox": "hi-inbox",
        "sign-out": "hi-logout",
        "tags": "hi-tag",
        "shopping-bag": "hi-shopping-bag",
        "th-large": "hi-view-grid",
        "table": "hi-table",
        "user": "hi-user",
        "envelope": "hi-mail",
        "exclamation-triangle": "hi-exclamation-circle",
    };

    if (!raw) {
        return "hi-solid-question-mark-circle";
    }

    if (raw.startsWith("hi-")) {
        return raw;
    }

    if (raw.includes(" ")) {
        const parts = raw.split(/\s+/).filter(Boolean);
        const iconPart = parts.find(
            (part) =>
                part.startsWith("pi-") ||
                part.startsWith("pr-") ||
                part.startsWith("hi-")
        );
        if (iconPart) {
            if (iconPart.startsWith("hi-")) {
                return iconPart;
            }
            const key = iconPart.replace(/^(pi|pr)-/, "");
            return nameMap[key] || "hi-solid-question-mark-circle";
        }
    }
    if (raw.startsWith("pi-") || raw.startsWith("pr-")) {
        const key = raw.replace(/^(pi|pr)-/, "");
        return nameMap[key] || "hi-solid-question-mark-circle";
    }
    return raw;
});
</script>
