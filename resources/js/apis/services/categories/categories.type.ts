export interface CategoryTranslation {
    id?: string | number;
    locale?: string;
    key?: string;
    value?: string | null;
}

export interface CategoryMedia {
    id?: string | number;
    collection_name?: string;
    name?: string;
    file_name?: string;
    mime_type?: string | null;
    size?: number;
    url?: string;
}

export interface Category {
    id?: string | number;
    name?: string;
    description?: string | null;
    order?: number | null;
    active?: boolean;
    created_at?: string;
    updated_at?: string;
    translations?: CategoryTranslation[];
    media?: CategoryMedia[];
}

/** Payload for create/update (includes optional locale-specific strings). */
export interface CategoryWritePayload {
    name: string;
    description?: string | null;
    order?: number | null;
    active: boolean;
    translations?: {
        ar?: {
            name?: string;
            description?: string | null;
        };
    };
}

export interface CategoryMediaPayload {
    file: File;
    collection?: "image";
    replace?: boolean;
}

export interface DeleteCategoryMediaPayload {
    collection?: "image";
}
