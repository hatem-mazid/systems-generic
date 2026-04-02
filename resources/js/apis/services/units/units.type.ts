export interface Unit {
    id?: number;
    unit_group_id?: number;
    name?: string;
    capacity?: number;
    type?: UnitType;
    // color?: string | null;
    active?: boolean;
    // properties?: Record<string, unknown> | null;
    status?: UnitStatus;
    reserved_at?: string;
    reserved_by?: string;
    current_order_id?: number;
    position?: number;
    price_per_hour?: string;
    created_at?: string;
    updated_at?: string;
    group?: {
        id?: number;
        name?: string;
    };
    current_order?: {
        id?: number;
        status?: string;
        total?: string | number;
        opened_at?: string | null;
        closed_at?: string | null;
    } | null;
}
export enum UnitType {
    Table = "table",
    Room = "room",
    Billiard = "billiard",
}

export enum UnitStatus {
    Available = "available",
    Reserved = "reserved",
    Occupied = "occupied",
    Inactive = "inactive",
}

/** Payload aligned with Laravel UnitController store/update validation. */
export interface UnitWritePayload {
    unit_group_id: number;
    name: string;
    type: UnitType | string;
    active: boolean;
    capacity?: number | null;
    position?: number | null;
    price_per_hour?: number | string | null;
}
