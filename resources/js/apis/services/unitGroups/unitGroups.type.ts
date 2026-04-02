import { Unit } from "../units/units.type";

export interface UnitGroup {
    id?: number;
    name?: string;
    is_active?: boolean;
    position?: number;
    created_at?: string;
    updated_at?: string;
    units?: Unit[];
    // color: null;
}

export type UnitGroupWritePayload = Required<
    Omit<UnitGroup, "id" | "created_at" | "updated_at">
>;
