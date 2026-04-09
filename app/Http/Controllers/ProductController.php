<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function import(Request $request)
    {
        $this->ensureCan('products create');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $spreadsheet = IOFactory::load($validated['file']->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            return response()->json([
                'message' => 'The import file is empty.',
            ], 422);
        }

        $headers = [];
        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $header = (string) $sheet->getCell([$column, 1])->getCalculatedValue();
            $headers[$column] = strtolower(trim($header));
        }

        $requiredHeaders = ['name', 'category_id', 'type', 'is_limited', 'stock_quantity', 'active'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                return response()->json([
                    'message' => "Missing required column: {$requiredHeader}",
                ], 422);
            }
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [];
                foreach ($headers as $column => $header) {
                    if ($header === '') {
                        continue;
                    }
                    $value = $sheet->getCell([$column, $row])->getCalculatedValue();
                    $rowData[$header] = is_string($value) ? trim($value) : $value;
                }

                if ($this->isImportRowEmpty($rowData)) {
                    continue;
                }

                $rowData = $this->normalizeImportRow($rowData);

                $rowValidator = validator($rowData, [
                    'id' => 'nullable|integer|exists:products,id',
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'ar_name' => 'nullable|string|max:255',
                    'ar_description' => 'nullable|string',
                    'category_id' => 'nullable|integer|exists:categories,id',
                    'type' => ['required', Rule::enum(ProductType::class)],
                    'price' => 'nullable|numeric|min:0|max:99999999.99',
                    'is_limited' => 'required|boolean',
                    'stock_quantity' => [
                        'nullable',
                        'integer',
                        'min:0',
                        Rule::requiredIf(fn () => (bool) $rowData['is_limited']),
                    ],
                    'active' => 'required|boolean',
                ]);

                if ($rowValidator->fails()) {
                    $errors[] = [
                        'row' => $row,
                        'errors' => $rowValidator->errors(),
                    ];

                    continue;
                }

                $payload = $rowValidator->validated();
                $productId = $payload['id'] ?? null;
                unset($payload['id']);

                $translations = [
                    'ar' => [
                        'name' => $payload['ar_name'] ?? null,
                        'description' => $payload['ar_description'] ?? null,
                    ],
                ];
                unset($payload['ar_name'], $payload['ar_description']);

                $categoryId = $payload['category_id'] ?? null;
                unset($payload['category_id']);

                if ($productId) {
                    $product = Product::find($productId);
                    if (! $product) {
                        $errors[] = [
                            'row' => $row,
                            'errors' => ['id' => ['Product not found.']],
                        ];

                        continue;
                    }
                    $product->update($payload);
                    $updated++;
                } else {
                    $product = Product::create($payload);
                    $created++;
                }

                $this->syncProductCategory($product, $categoryId);
                $this->syncProductTranslations($product, $translations);
            }

            if (! empty($errors)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Import failed. Please fix the reported rows and try again.',
                    'errors' => $errors,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'message' => 'Products imported successfully.',
                'created' => $created,
                'updated' => $updated,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => 'Import failed due to an unexpected error.',
            ], 500);
        }
    }

    public function exportTemplate(): StreamedResponse
    {
        $this->ensureCan('products index');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'id',
            'name',
            'description',
            'ar_name',
            'ar_description',
            'category_id',
            'type',
            'price',
            'is_limited',
            'stock_quantity',
            'active',
        ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column.'1', $header);
        }

        $products = Product::with(['translations', 'categories'])->orderBy('id')->get();
        $rowNumber = 2;

        foreach ($products as $product) {
            $sheet->setCellValue('A'.$rowNumber, $product->id);
            $sheet->setCellValue('B'.$rowNumber, $product->name);
            $sheet->setCellValue('C'.$rowNumber, $product->description);
            $sheet->setCellValue('D'.$rowNumber, $this->extractTranslationValue($product, 'ar', 'name'));
            $sheet->setCellValue('E'.$rowNumber, $this->extractTranslationValue($product, 'ar', 'description'));
            $sheet->setCellValue('F'.$rowNumber, $product->categories->first()?->id);
            $sheet->setCellValue('G'.$rowNumber, $product->type?->value ?? (string) $product->type);
            $sheet->setCellValue('H'.$rowNumber, $product->price);
            $sheet->setCellValue('I'.$rowNumber, $product->is_limited ? 1 : 0);
            $sheet->setCellValue('J'.$rowNumber, $product->stock_quantity);
            $sheet->setCellValue('K'.$rowNumber, $product->active ? 1 : 0);

            $rowNumber++;
        }

        if ($products->isEmpty()) {
            $sheet->setCellValue('A2', '');
            $sheet->setCellValue('B2', 'Sample Product');
            $sheet->setCellValue('C2', 'Sample English description');
            $sheet->setCellValue('D2', 'منتج تجريبي');
            $sheet->setCellValue('E2', 'وصف عربي تجريبي');
            $sheet->setCellValue('F2', 1);
            $sheet->setCellValue('G2', 'physical');
            $sheet->setCellValue('H2', 25.5);
            $sheet->setCellValue('I2', 1);
            $sheet->setCellValue('J2', 10);
            $sheet->setCellValue('K2', 1);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'products-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function index(Request $request)
    {
        $this->ensureCan('products index');

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'type' => ['sometimes', 'nullable', Rule::enum(ProductType::class)],
            'active' => 'sometimes|boolean',
            'search' => 'sometimes|nullable|string|max:255',
        ]);

        $query = Product::with(['translations', 'media', 'categories.translations', 'section']);

        if (! empty($validated['category_id'] ?? null)) {
            $categoryId = $validated['category_id'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if (! empty($validated['type'] ?? null)) {
            $query->where('type', $validated['type']);
        }

        if (array_key_exists('active', $validated)) {
            $query->where('active', $validated['active']);
        }

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $perPage = (int) ($validated['per_page'] ?? $request->integer('per_page', 12));

        $products = $query->orderBy('id')->paginate($perPage);

        return new ProductCollection($products);
    }

    public function show(string $id)
    {
        $this->ensureCan('products edit');

        $product = Product::with(['translations', 'media', 'categories.translations', 'section'])->find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(new ProductResource($product));
    }

    public function store(Request $request)
    {
        $this->ensureCan('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'type' => ['required', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'required|boolean',
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:0',
                Rule::requiredIf(fn () => $request->boolean('is_limited')),
            ],
            'active' => 'required|boolean',
            'section_id' => 'nullable|integer|exists:sections,id',
            'translations' => 'nullable|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'] ?? null;
        $categoryId = $validated['category_id'] ?? null;
        unset($validated['translations']);
        unset($validated['category_id']);

        $product = Product::create($validated);
        $this->syncProductCategory($product, $categoryId);
        $this->syncProductTranslations($product, $translations);
        $product->load(['translations', 'media', 'categories.translations', 'section']);

        return response()->json(new ProductResource($product));
    }

    public function update(Request $request, string $id)
    {
        $this->ensureCan('products edit');

        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'type' => ['sometimes', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'sometimes|boolean',
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:0',
                Rule::requiredIf(function () use ($request, $product) {
                    $limited = $request->has('is_limited')
                        ? $request->boolean('is_limited')
                        : $product->is_limited;

                    return (bool) $limited;
                }),
            ],
            'active' => 'sometimes|boolean',
            'section_id' => 'sometimes|nullable|integer|exists:sections,id',
            'translations' => 'sometimes|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'] ?? null;
        $hasCategory = array_key_exists('category_id', $validated);
        $categoryId = $validated['category_id'] ?? null;
        unset($validated['translations']);
        unset($validated['category_id']);

        if (! empty($validated)) {
            $product->update($validated);
        }
        if ($hasCategory) {
            $this->syncProductCategory($product, $categoryId);
        }
        $this->syncProductTranslations($product, $translations);
        $product->load(['translations', 'media', 'categories.translations', 'section']);

        return response()->json(new ProductResource($product));
    }

    public function destroy(string $id)
    {
        $this->ensureCan('products delete');

        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->noContent();
    }

    /**
     * Append an image to the product media library (default collection).
     */
    public function storeMedia(Request $request, Product $product)
    {
        $this->ensureCan('products edit');

        $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'collection' => ['sometimes', 'string', 'max:255', 'in:default'],
        ]);

        $media = $product
            ->addMediaFromRequest('file')
            ->toMediaCollection('default');

        $hasDefault = $product->media()->get()->contains(
            fn ($m) => (bool) $m->getCustomProperty('is_default')
        );

        if (! $hasDefault) {
            $media->setCustomProperty('is_default', true);
            $media->save();
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * Remove a single media file from the product.
     */
    public function destroyMedia(Request $request, Product $product)
    {
        $this->ensureCan('products edit');

        $validated = $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ]);

        $media = $product->media()->where('id', $validated['media_id'])->firstOrFail();
        $wasDefault = (bool) $media->getCustomProperty('is_default');
        $media->delete();

        if ($wasDefault) {
            $next = $product->media()->orderBy('id')->first();
            if ($next) {
                $next->setCustomProperty('is_default', true);
                $next->save();
            }
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * Mark one media item as the default (preview) image for listings and cards.
     */
    public function setDefaultMedia(Request $request, Product $product)
    {
        $this->ensureCan('products edit');

        $validated = $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        $target = $product->media()->where('id', $validated['media_id'])->firstOrFail();

        foreach ($product->media as $m) {
            $m->setCustomProperty('is_default', $m->id === $target->id);
            $m->save();
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * @param  array<string, array<string, string|null>>|null  $translations
     */
    private function syncProductTranslations(Product $product, ?array $translations): void
    {
        if ($translations === null) {
            return;
        }

        foreach ($translations as $locale => $fields) {
            if (! is_array($fields) || $locale === 'en') {
                continue;
            }

            foreach (['name', 'description'] as $key) {
                if (! array_key_exists($key, $fields)) {
                    continue;
                }

                $value = $fields[$key];

                if ($value === null || $value === '') {
                    $product->translations()
                        ->where('locale', $locale)
                        ->where('key', $key)
                        ->delete();
                } else {
                    $product->translations()->updateOrCreate(
                        ['locale' => $locale, 'key' => $key],
                        ['value' => $value]
                    );
                }
            }
        }
    }

    private function syncProductCategory(Product $product, ?int $categoryId): void
    {
        if ($categoryId === null) {
            $product->categories()->sync([]);

            return;
        }

        $product->categories()->sync([$categoryId]);
    }

    /**
     * @param  array<string, mixed>  $rowData
     */
    private function isImportRowEmpty(array $rowData): bool
    {
        foreach ($rowData as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $rowData
     * @return array<string, mixed>
     */
    private function normalizeImportRow(array $rowData): array
    {
        foreach (['id', 'category_id', 'stock_quantity'] as $key) {
            if (($rowData[$key] ?? null) === '') {
                $rowData[$key] = null;
            }
        }

        foreach (['description', 'ar_name', 'ar_description'] as $key) {
            if (($rowData[$key] ?? null) === '') {
                $rowData[$key] = null;
            }
        }

        if (array_key_exists('price', $rowData) && $rowData['price'] === '') {
            $rowData['price'] = null;
        }

        if (array_key_exists('is_limited', $rowData)) {
            $rowData['is_limited'] = $this->toBoolean($rowData['is_limited']);
        }

        if (array_key_exists('active', $rowData)) {
            $rowData['active'] = $this->toBoolean($rowData['active']);
        }

        return $rowData;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function extractTranslationValue(Product $product, string $locale, string $key): ?string
    {
        $translation = $product->translations
            ->first(fn ($item) => $item->locale === $locale && $item->key === $key);

        return $translation?->value;
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
