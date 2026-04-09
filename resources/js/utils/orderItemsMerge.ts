import type { OrderItem } from "../apis/services/orders/orders.type";

export type MergedOrderLine = {
    key: string;
    name?: string;
    product_id?: number | null;
    price?: string | number;
    quantity: number;
    total: number;
    image?: string | null;
    type?: string;
    section_name: string | null;
    section_code: string | null;
    /** True when merged lines came from more than one section */
    multiple_sections: boolean;
    batch_label: string;
    is_printed: boolean | "mixed";
    meta?: Record<string, unknown> | null;
    /** Number of raw order lines merged into this row */
    sourceLineCount: number;
};

type Agg = {
    key: string;
    name?: string;
    product_id?: number | null;
    price?: string | number;
    quantity: number;
    total: number;
    image?: string | null;
    type?: string;
    sectionNames: Set<string>;
    sectionCodes: Set<string>;
    batchNos: Set<number>;
    printed: Set<boolean>;
    meta: Record<string, unknown> | null;
    sourceLineCount: number;
};

/**
 * Merge order lines that share product_id + unit price + name (qty and line totals summed).
 */
export function mergeOrderItems(items: OrderItem[] | undefined | null): MergedOrderLine[] {
    if (!items?.length) {
        return [];
    }

    const map = new Map<string, Agg>();

    for (const line of items) {
        const key = `${line.product_id ?? "n"}|${String(line.price ?? "")}|${String(line.name ?? "")}`;
        const qty = Number(line.quantity ?? 0) || 0;
        const lineTotal = Number(line.total);
        const t = Number.isFinite(lineTotal) ? lineTotal : 0;

        let g = map.get(key);
        if (!g) {
            g = {
                key,
                name: line.name,
                product_id: line.product_id ?? null,
                price: line.price,
                quantity: 0,
                total: 0,
                image: line.image ?? null,
                type: line.type,
                sectionNames: new Set(),
                sectionCodes: new Set(),
                batchNos: new Set(),
                printed: new Set(),
                meta: null,
                sourceLineCount: 0,
            };
            map.set(key, g);
        }

        g.quantity += qty;
        g.total += t;
        g.sourceLineCount += 1;

        if (g.sourceLineCount > 1) {
            g.meta = null;
        } else if (line.meta && Object.keys(line.meta).length) {
            g.meta = { ...line.meta };
        }

        if (!g.image && line.image) {
            g.image = line.image;
        }
        if (line.section_name) {
            g.sectionNames.add(line.section_name);
        }
        if (line.section_code) {
            g.sectionCodes.add(line.section_code);
        }
        if (line.batch_no != null) {
            g.batchNos.add(line.batch_no);
        }
        if (typeof line.is_printed === "boolean") {
            g.printed.add(line.is_printed);
        }
    }

    return [...map.values()].map((g) => {
        const multiple_sections = g.sectionNames.size > 1 || g.sectionCodes.size > 1;
        const section_name = g.sectionNames.size === 1 ? [...g.sectionNames][0] : null;
        const section_code = g.sectionCodes.size === 1 ? [...g.sectionCodes][0] : null;
        const batch_label =
            g.batchNos.size === 0
                ? "—"
                : g.batchNos.size === 1
                  ? String([...g.batchNos][0])
                  : [...g.batchNos].sort((a, b) => a - b).join(", ");

        let is_printed: boolean | "mixed" = false;
        if (g.printed.size === 1) {
            is_printed = [...g.printed][0];
        } else if (g.printed.size > 1) {
            is_printed = "mixed";
        }

        return {
            key: g.key,
            name: g.name,
            product_id: g.product_id,
            price: g.price,
            quantity: g.quantity,
            total: g.total,
            image: g.image,
            type: g.type,
            section_name,
            section_code,
            multiple_sections,
            batch_label,
            is_printed,
            meta: g.meta,
            sourceLineCount: g.sourceLineCount,
        };
    });
}
