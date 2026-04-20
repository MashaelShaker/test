<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxElement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;

class BoxController extends Controller
{
    /**
     * Decode a data-URL image, persist to storage, return full public URL (APP_URL + /storage/...), or null if invalid/empty.
     * Covers: create with image, update with new image — never returns a value for "no change" or garbage input.
     */
    private function storeImageFromDataUrl(?string $dataUrl): ?string
    {
        if (!is_string($dataUrl)) {
            return null;
        }
        $dataUrl = trim($dataUrl);
        if ($dataUrl === '') {
            return null;
        }
        if (!str_contains($dataUrl, 'data:image') || !str_contains($dataUrl, 'base64,')) {
            return null;
        }

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $dataUrl);
        if ($imageData === '') {
            return null;
        }

        $binary = base64_decode($imageData, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        // Reject effectively empty payloads (avoids corrupt / zero-byte "uploads")
        if (strlen($binary) < 12) {
            return null;
        }

        $filename = 'boxes/' . uniqid() . '.jpg';

        Storage::disk('public')->put($filename, $binary);

        return $this->absolutePublicStorageUrl($filename);
    }

    /**
     * Full URL for public storage files — يعتمد على APP_URL (مثلاً ngrok) وليس host افتراضي خاطئ.
     */
    private function absolutePublicStorageUrl(string $relativePath): string
    {
        return rtrim((string) config('app.url'), '/') . '/storage/' . ltrim($relativePath, '/');
    }

    /**
     * Resolve disk path under storage/app/public from a stored image URL (relative /storage/... or full APP_URL).
     */
    private function resolveLocalPublicStoragePath(string $imageUrl): ?string
    {
        $relative = $this->publicStorageRelativePath($imageUrl);
        if ($relative === null) {
            return null;
        }

        $full = storage_path('app/public/' . $relative);

        return is_readable($full) ? $full : null;
    }

    private function publicStorageRelativePath(string $imageUrl): ?string
    {
        $path = parse_url($imageUrl, PHP_URL_PATH);
        if ($path === null || $path === false || $path === '') {
            $path = $imageUrl;
        }

        if (preg_match('#/storage/(.+)$#', $path, $m)) {
            return $m[1];
        }

        $stripped = str_replace('/storage/', '', $path);
        if ($stripped !== $path) {
            $relative = ltrim($stripped, '/');
            return $relative === '' ? null : $relative;
        }

        return null;
    }

    private function deleteLocalImage(?string $imageUrl): void
    {
        if (!is_string($imageUrl) || $imageUrl === '') {
            return;
        }

        $relative = $this->publicStorageRelativePath($imageUrl);
        if ($relative === null) {
            return;
        }

        Storage::disk('public')->delete($relative);
    }

    /**
     * رفع صورة المنتج إلى Salla. أي فشل هنا لا يجب أن يكسر إنشاء/تحديث البوكس — يُسجَّل فقط ويُرجَع null.
     */
    private function uploadBoxImageToSalla($token, $sallaProductId, $imageUrl): ?int
    {
        if ($imageUrl === null || $imageUrl === '') {
            return null;
        }

        $imagePath = $this->resolveLocalPublicStoragePath($imageUrl);
        if ($imagePath === null) {
            Log::error('Salla upload skipped: could not resolve local file from image_url', [
                'image_url' => $imageUrl,
            ]);

            return null;
        }

        Log::info('Salla upload local file before attach', [
            'absolute_path' => $imagePath,
            'is_file'         => is_file($imagePath),
            'is_readable'     => is_readable($imagePath),
            'size_bytes'      => @filesize($imagePath),
        ]);

        $fileSize = (int) (@filesize($imagePath) ?: 0);
        if ($fileSize === 0) {
            Log::error('Salla upload skipped: empty image contents');
            return null;
        }

        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $filename = basename($imagePath);

        // Ensure filename has proper extension for Salla API
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        }

        $photoContent = file_get_contents($imagePath);

        if ($photoContent === false) {
            Log::error('Salla upload skipped: failed to read image contents', ['image_path' => $imagePath]);
            return null;
        }

        $url = "https://api.salla.dev/admin/v2/products/{$sallaProductId}/images";
        $payloadSize = $fileSize;

        try {
            Log::info('Salla upload payload ready', [
                'has_content' => !empty($photoContent),
                'mime_type'   => $mimeType,
                'filename'    => $filename,
                'payload_size'=> $payloadSize,
                'sending'     => 'photo only',
            ]);

            $sentRequest = null;
            $response = Http::withToken($token)
                ->attach('photo', $photoContent, $filename)
                ->attach('main', '1')
                ->withOptions([
                    'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$sentRequest) {
                        $sentRequest = $stats->getRequest();
                    },
                ])
                ->post($url);

            Log::info('Salla upload response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if (!$response->successful()) {
                $fullBody = $response->body();

                $failContext = [
                    'status' => $response->status(),
                    'body'   => $fullBody,
                ];

                if ($response->status() === 422) {
                    $failContext['request_payload_size'] = $payloadSize;
                    $failContext['request_headers'] = self::redactHeadersForLog($sentRequest);
                }

                Log::error('Salla upload failed', $failContext);
                // نفس الجسم كاملاً في سطر منفصل لتفادي أي اقتطاع في عارض السجلات
                Log::error('[Salla upload] complete response body (raw): ' . $fullBody);

                return null;
            }

            $data = $response->json();
            if (!is_array($data)) {
                Log::error('Salla upload: unexpected JSON shape', [
                    'body' => $response->body(),
                ]);

                return null;
            }

            $nested = $data['data'] ?? null;
            $imageId = null;
            if (is_array($nested)) {
                $imageId = $nested['id'] ?? null;
                if ($imageId === null && isset($nested['image']) && is_array($nested['image'])) {
                    $imageId = $nested['image']['id'] ?? null;
                }
                if ($imageId === null && isset($nested['photo']) && is_array($nested['photo'])) {
                    $imageId = $nested['photo']['id'] ?? null;
                }
            }
            if ($imageId === null) {
                $imageId = $data['id'] ?? null;
            }

            if ($imageId === null || $imageId === '') {
                return null;
            }

            return (int) $imageId;
        } catch (\Throwable $e) {
            Log::error('Salla upload exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    private static function redactHeadersForLog(?RequestInterface $request): array
    {
        if ($request === null) {
            return ['_note' => 'request not captured (on_stats missing)'];
        }

        $out = [];
        foreach ($request->getHeaders() as $name => $values) {
            $value = implode(', ', $values);
            if (strtolower($name) === 'authorization') {
                $value = preg_replace('/Bearer\s+\S+/i', 'Bearer [REDACTED]', $value) ?? $value;
            }
            $out[$name] = $value;
        }

        return $out;
    }

    private function deleteSallaImage(string $token, int $sallaProductId, int $imageId): void
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->delete("https://api.salla.dev/admin/v2/products/images/{$imageId}");

            if (!$response->successful()) {
                Log::warning('Salla image delete failed', [
                    'product_id' => $sallaProductId,
                    'image_id'   => $imageId,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Salla image delete exception', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return int[]
     */
    private function fetchSallaProductImageIds(string $token, int $sallaProductId): array
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("https://api.salla.dev/admin/v2/products/{$sallaProductId}");

            if (!$response->successful()) {
                Log::warning('Salla product fetch failed for image listing', [
                    'product_id' => $sallaProductId,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);

                return [];
            }

            $data = $response->json('data');
            $candidateLists = [
                'data.images',
                'data.images.data',
                'data.gallery',
                'data.photos',
            ];

            $ids = [];
            foreach ($candidateLists as $path) {
                $list = $response->json($path);
                if (is_array($list) && !empty($list)) {
                    foreach ($list as $image) {
                        $id = is_array($image) ? ($image['id'] ?? null) : null;
                        if ($id !== null) {
                            $ids[] = (int) $id;
                        }
                    }
                    if (!empty($ids)) {
                        break;
                    }
                }
            }

            Log::info('Salla product image list', [
                'product_id' => $sallaProductId,
                'found_ids'  => $ids,
                'data_keys'  => is_array($data) ? array_keys($data) : null,
            ]);

            return $ids;
        } catch (\Throwable $e) {
            Log::error('Salla product images fetch exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function pushOptionsToSalla(string $token, int $sallaProductId, array $elements): void
    {
        foreach ($elements as $elementData) {

            $productIds = array_column($elementData['products'], 'id');
            $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $values = array_map(fn($pid) => [
                'name'       => $products[$pid]->name ?? "Product $pid",
                'price'      => 0,
                'is_default' => false,
                'image_url'  => $products[$pid]->image_url ?? null,

                // IMPORTANT FIX ONLY (was 1)
                'display_value' => $products[$pid]->image_url ?? null
            ], $productIds);

            Http::withToken($token)
                ->acceptJson()
                ->post("https://api.salla.dev/admin/v2/products/{$sallaProductId}/options", [
                    'name'         => $elementData['name'],
                    'required'     => true,
                    'display_type' => 'image',
                    'visibility'   => 'always',
                    'values'       => $values,
                ]);
        }
    }

    private function deleteAllSallaOptions(string $token, int $sallaProductId): void
    {
        $res = Http::withToken($token)
            ->acceptJson()
            ->get("https://api.salla.dev/admin/v2/products/{$sallaProductId}");

        if (!$res->successful()) return;

        foreach ($res->json('data.options', []) as $option) {
            Http::withToken($token)
                ->acceptJson()
                ->delete("https://api.salla.dev/admin/v2/products/options/{$option['id']}");
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                       => 'required|string',
            'price'                      => 'required|numeric',
            'description'                => 'nullable|string',
            'image'                      => 'nullable|string',
            'elements'                   => 'required|array|min:1',
            'elements.*.name'            => 'required|string',
            'elements.*.products'        => 'required|array|min:1',
            'elements.*.products.*.id'   => 'required|exists:products,id',
        ]);

        $user  = auth()->user();
        $token = $user->token->access_token;

        $imageUrl = $this->storeImageFromDataUrl($validated['image'] ?? null);

        $sallaResponse = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.salla.dev/admin/v2/products', [
                'name'         => $validated['name'],
                'price'        => $validated['price'],
                'description'  => $validated['description'] ?? '',
                'status'       => 'sale',
                'product_type' => 'product',
                'quantity'     => 10,
            ]);

        if (!$sallaResponse->successful()) {
            Log::error('Salla product creation failed', [
                'status' => $sallaResponse->status(),
                'body'   => $sallaResponse->body(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء المنتج في سلة',
                'error'   => $sallaResponse->json()
            ], 500);
        }

        $data             = $sallaResponse->json();
        $salla_product_id = $data['data']['id'] ?? $data['data']['product']['id'] ?? $data['id'] ?? null;

        if (!$salla_product_id) {
            return response()->json(['success' => false, 'message' => 'لم يتم جلب Salla Product ID', 'debug' => $data], 422);
        }

        $this->pushOptionsToSalla($token, $salla_product_id, $validated['elements']);

        $box = Box::create([
            'name'             => $validated['name'],
            'price'            => $validated['price'],
            'description'      => $validated['description'] ?? null,
            'image_url'        => $imageUrl,
            'store_id'         => $user->store_id,
            'salla_product_id' => $salla_product_id,
        ]);

        // رفع الصورة لسلة فقط عند وجود ملف محفوظ وصالح (لا رفع فارغ).
        // فشل الرفع أو عدم إرجاع image_id لا يُلغي إنشاء البوكس — يُسجَّل فقط داخل uploadBoxImageToSalla.
        if ($imageUrl !== null) {
            $sallaImageId = $this->uploadBoxImageToSalla(
                $token,
                $salla_product_id,
                $imageUrl
            );
            if ($sallaImageId) {
                $box->image_id = $sallaImageId;
                $box->save();
            }
        }

        foreach ($validated['elements'] as $elementData) {
            $element = BoxElement::create([
                'box_id' => $box->id,
                'element_name' => $elementData['name']
            ]);

            $element->products()->attach(array_column($elementData['products'], 'id'));
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الباقة بنجاح',
            'data'    => ['box_id' => $box->id, 'salla_product_id' => $salla_product_id]
        ], 201);
    }

    public function index()
    {
        $boxes = Box::with('elements.products')->get();
        return view('boxes.index', compact('boxes'));
    }

    public function show($id)
    {
        $box = Box::with('elements.products')->findOrFail($id);
        return view('boxes.show', compact('box'));
    }

    public function edit($id)
    {
        $box = Box::with('elements.products')->findOrFail($id);

        $elements = $box->elements->map(fn($el) => [
            'id'       => 'element-' . $el->id,
            'name'     => $el->element_name,
            'products' => $el->products->map(fn($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => (float) $p->price,
                'image' => $p->image_url,
            ])->toArray(),
        ])->toArray();

        return view('dashboard', compact('box', 'elements'));
    }

    public function update(Request $request, $id)
    {
        $box   = Box::findOrFail($id);
        $user  = auth()->user();
        $token = $user->token->access_token;

        // null = لم يُرسل تغيير صورة أو القيمة غير صالحة — نُبقي image_url كما هو
        $newImageUrl = $this->storeImageFromDataUrl($request->input('image'));
        $oldImageUrl = $box->image_url;

        $box->name        = $request->name;
        $box->price       = $request->price;
        $box->description = $request->description;

        if ($newImageUrl !== null) {
            $box->image_url = $newImageUrl;
        }

        $box->save();

        // عند استبدال الصورة فقط: رفع الملف الجديد إلى Salla. فشل الرفع لا يوقف تحديث البوكس المحلي.
        if ($newImageUrl !== null && $box->salla_product_id) {
            $sallaImageId = $this->uploadBoxImageToSalla(
                $token,
                (int) $box->salla_product_id,
                $newImageUrl
            );
            if ($sallaImageId) {
                $box->image_id = $sallaImageId;
                $box->save();
            }
        }

        if ($newImageUrl !== null && $oldImageUrl !== null && $oldImageUrl !== $newImageUrl) {
            $this->deleteLocalImage($oldImageUrl);
        }

        // عند استبدال الصورة فقط: رفع الملف الجديد إلى Salla. فشل الرفع لا يوقف تحديث البوكس المحلي.
        if ($newImageUrl !== null && $box->salla_product_id) {
            $existingImageIds = $this->fetchSallaProductImageIds($token, (int) $box->salla_product_id);

            $sallaImageId = $this->uploadBoxImageToSalla(
                $token,
                (int) $box->salla_product_id,
                $newImageUrl
            );
            if ($sallaImageId) {
                $box->image_id = $sallaImageId;
                $box->save();

                foreach ($existingImageIds as $oldId) {
                    if ($oldId !== $sallaImageId) {
                        $this->deleteSallaImage($token, (int) $box->salla_product_id, $oldId);
                    }
                }
            }
        }

        if ($request->has('elements')) {
            $box->elements()->each(fn($el) => $el->products()->detach());
            $box->elements()->delete();

            foreach ($request->elements as $elementData) {
                $element = BoxElement::create([
                    'box_id' => $box->id,
                    'element_name' => $elementData['name']
                ]);

                $element->products()->attach(array_column($elementData['products'], 'id'));
            }
        }

        if ($box->salla_product_id) {
            Http::withToken($token)
                ->acceptJson()
                ->put("https://api.salla.dev/admin/v2/products/{$box->salla_product_id}", [
                    'name'        => $request->name,
                    'price'       => $request->price,
                    'description' => $request->description ?? '',
                ]);
        }

        if ($box->salla_product_id && $request->has('elements')) {

            $getRes = Http::withToken($token)
                ->acceptJson()
                ->get("https://api.salla.dev/admin/v2/products/{$box->salla_product_id}");

            Log::info('GET product response', ['status' => $getRes->status()]);

            if ($getRes->successful()) {
                foreach ($getRes->json('data.options', []) as $option) {
                    $delRes = Http::withToken($token)
                        ->acceptJson()
                        ->delete("https://api.salla.dev/admin/v2/products/options/{$option['id']}");

                    Log::info('DELETE option', ['option_id' => $option['id'], 'status' => $delRes->status()]);
                }
            }

            try {
                $this->pushOptionsToSalla($token, $box->salla_product_id, $request->elements);
                Log::info('Options pushed successfully');
            } catch (\Exception $e) {
                Log::error('Salla options sync failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الباقة بنجاح',
            'data'    => $box->fresh()->load('elements.products')
        ]);
    }

    public function destroy($id)
    {
        $box   = Box::findOrFail($id);
        $user  = auth()->user();
        $token = $user->token->access_token;

        if ($box->salla_product_id) {
            Http::withToken($token)
                ->acceptJson()
                ->delete("https://api.salla.dev/admin/v2/products/{$box->salla_product_id}");
        }

        $localImage = $box->getRawOriginal('image_url');

        $box->delete();

        $this->deleteLocalImage($localImage);

        return redirect()->back()->with('success', 'تم حذف الباقة');
    }
    public function details($salla_product_id)
    {
        $box = Box::with('elements.products')
            ->where('salla_product_id', $salla_product_id)
            ->first();

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة مرتبطة بهذا المنتج',
                'data' => null,
            ], 404)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, ngrok-skip-browser-warning');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحميل بيانات الباقة بنجاح',
            'data' => $box,
        ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, ngrok-skip-browser-warning');
    }
}
