export enum OrderItemType {
    Product = "product",
    Service = "service",
    Timer = "timer",
    UnitFees = "unitFees",
}

export enum OrderStatus {
    Active = "active",
    Reserved = "reserved",
    Open = "open",
    Pending = "pending",
    Ordering = "ordering",
    Takeaway = "takeaway",
    Closed = "closed",
    Cancelled = "cancelled",
}

export interface OrderItem {
    id?: number;
    product_id?: number;
    name?: string;
    notes?: string;
    price?: string | number;
    quantity?: number;
    total?: string | number;
    type?: OrderItemType | string;
    meta?: Record<string, unknown>;
    batch_no?: number | null;
    is_printed?: boolean;
    section_code?: string | null;
    section_name?: string | null;
    created_at?: string;
    updated_at?: string;
    /** Single product image URL (default media), when product is loaded on order show. */
    image?: string | null;
}

export interface Order {
    id?: number;
    unit_id?: number;
    user_id?: number;
    status?: OrderStatus | string;
    total?: string | number;
    reserved_at?: string;
    opened_at?: string;
    closed_at?: string;
    created_at?: string;
    updated_at?: string;
    user_name?: string;
    unit_name?: string;
    items?: OrderItem[];
}

export interface OrderSectionPrintItem {
    id: number;
    batch_no?: number;
    name?: string;
    notes?: string | null;
    quantity?: number;
    type?: string;
}

export interface OrderSectionPrintGroup {
    section_code: string;
    section_name?: string;
    printer_name?: string | null;
    items: OrderSectionPrintItem[];
}

export interface OrderPrintPatch {
    batch_no: number;
    sections: OrderSectionPrintGroup[];
}

export interface OrderPrintResponse {
    order_id: number;
    sections: OrderSectionPrintGroup[];
    patches?: OrderPrintPatch[];
    printed_items_count: number;
}
