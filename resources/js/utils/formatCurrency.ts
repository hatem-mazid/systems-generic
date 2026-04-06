export function formatCurrency(value: unknown): string {
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
