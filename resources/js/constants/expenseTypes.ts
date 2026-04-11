/** Must match App\Enums\ExpenseType */
export const EXPENSE_TYPE_VALUES = [
    "rent",
    "supplies",
    "payroll",
    "utilities",
    "marketing",
    "other",
] as const;

export type ExpenseTypeValue = (typeof EXPENSE_TYPE_VALUES)[number];

export function expenseTypeLabelKey(value: string): string {
    const map: Record<string, string> = {
        rent: "ExpenseForm.TypeRent",
        supplies: "ExpenseForm.TypeSupplies",
        payroll: "ExpenseForm.TypePayroll",
        utilities: "ExpenseForm.TypeUtilities",
        marketing: "ExpenseForm.TypeMarketing",
        other: "ExpenseForm.TypeOther",
    };

    return map[value] ?? value;
}
