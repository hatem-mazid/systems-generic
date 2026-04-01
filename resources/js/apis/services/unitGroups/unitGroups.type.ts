export interface UnitGroup {
    id?: string | number;
    name?: string;
    is_active?: boolean;
    position?: number;
    created_at?: string;
    updated_at?: string;
}

export type UnitGroupWritePayload = Required<
    Omit<UnitGroup, "id" | "created_at" | "updated_at">
>;
