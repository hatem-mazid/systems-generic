export enum OrderItemType {
    Product = "product",
    Service = "service",
    Timer = "timer",
}

export enum OrderStatus {
    Active = "active",
    Reserved = "reserved",
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
