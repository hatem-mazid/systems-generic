export interface ProductTranslation {
    id?: string | number;
    locale?: string;
    key?: string;
    value?: string | null;
}

export interface ProductMedia {
    id?: string | number;
    collection_name?: string;
    name?: string;
    file_name?: string;
    mime_type?: string | null;
    size?: number;
    url?: string;
    is_default?: boolean;
}

export interface ProductCategory {
    id?: string | number;
    name?: string;
}

export type ProductTypeValue =
    | "physical"
    | "service_fixed"
    | "service_timer";

export interface Product {
    id?: string | number;
    name?: string;
    description?: string | null;
    order?: number | null;
    type?: ProductTypeValue | string;
    price?: string | number | null;
    is_limited?: boolean;
    stock_quantity?: number | null;
    active?: boolean;
    created_at?: string;
    updated_at?: string;
    translations?: ProductTranslation[];
    media?: ProductMedia[];
    categories?: ProductCategory[];
}

/** Payload for create/update (includes optional locale-specific strings). */
export interface ProductWritePayload {
    name: string;
    description?: string | null;
    category_id?: string | number | null;
    type?: string;
    price?: number | null;
    is_limited?: boolean;
    stock_quantity?: number | null;
    active: boolean;
    translations?: {
        ar?: {
            name?: string;
            description?: string | null;
        };
    };
}

export interface ProductMediaPayload {
    file: File;
    collection?: "default";
}

export interface DeleteProductMediaPayload {
    media_id: string | number;
}

export interface SetDefaultProductMediaPayload {
    media_id: string | number;
}
