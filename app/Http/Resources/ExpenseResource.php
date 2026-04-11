<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->type;
        $typeValue = $type instanceof \BackedEnum ? $type->value : $type;

        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'type' => $typeValue,
            'expense_date' => $this->expense_date?->toDateString(),
            'expense_by' => $this->whenLoaded('expenseBy', function () {
                $u = $this->expenseBy;

                return $u ? [
                    'id' => $u->id,
                    'name' => $u->name,
                ] : null;
            }),
            'created_by' => $this->whenLoaded('createdBy', function () {
                $u = $this->createdBy;

                return $u ? [
                    'id' => $u->id,
                    'name' => $u->name,
                ] : null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
