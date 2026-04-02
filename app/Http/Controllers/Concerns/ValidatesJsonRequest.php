<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidatesJsonRequest
{
    /**
     * Validate request data; on failure return JSON 422 with `message` and `errors`.
     *
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @param  array<string, string>  $attributes
     * @return array<string, mixed>|JsonResponse
     */
    protected function validateJsonOrFail(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array|JsonResponse {
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        return $validator->validated();
    }
}
