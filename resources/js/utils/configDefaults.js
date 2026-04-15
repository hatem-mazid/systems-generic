const TIME_PATTERN = /^([01]\d|2[0-3]):([0-5]\d)$/;

export function parseDateTime(value) {
    if (!value) {
        return null;
    }
    const dt = new Date(value.replace(" ", "T"));
    return Number.isNaN(dt.getTime()) ? null : dt;
}

export function buildFilterRangeFromConfig(config) {
    const from = parseDateTime(config?.default_config?.from_datetime);
    const to = parseDateTime(config?.default_config?.to_datetime);

    if (from && to) {
        return { from, to };
    }

    const openingTime = config?.opening_time || "08:00";
    const closingTime = config?.closing_time || "23:59";
    return buildFilterRangeFromTimes(openingTime, closingTime);
}

export function buildFilterRangeFromTimes(openingTime, closingTime) {
    const from = todayWithTime(openingTime);
    const to = todayWithTime(closingTime);

    if (to <= from) {
        to.setDate(to.getDate() + 1);
    }

    return { from, to };
}

function todayWithTime(time) {
    const date = new Date();
    const match = TIME_PATTERN.exec(time ?? "");
    if (!match) {
        date.setHours(0, 0, 0, 0);
        return date;
    }

    date.setHours(Number(match[1]), Number(match[2]), 0, 0);
    return date;
}

export function toDateTimeParam(value, boundary = "start") {
    if (!value) {
        return null;
    }
    const dt = value instanceof Date ? new Date(value.getTime()) : new Date(value);
    if (Number.isNaN(dt.getTime())) {
        return null;
    }

    const isMidnight =
        dt.getHours() === 0 &&
        dt.getMinutes() === 0 &&
        dt.getSeconds() === 0 &&
        dt.getMilliseconds() === 0;

    if (boundary === "end" && isMidnight) {
        dt.setHours(23, 59, 59, 0);
    }

    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, "0");
    const d = String(dt.getDate()).padStart(2, "0");
    const hh = String(dt.getHours()).padStart(2, "0");
    const mm = String(dt.getMinutes()).padStart(2, "0");
    const ss = String(dt.getSeconds()).padStart(2, "0");
    return `${y}-${m}-${d} ${hh}:${mm}:${ss}`;
}
