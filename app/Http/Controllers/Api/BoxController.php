<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoxRequest;
use App\Models\Box;
use App\Models\BoxElement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class BoxController extends Controller
{
    public function store(StoreBoxRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = auth()->user();
            $token = $user->token->access_token;

            // 📸 حفظ الصورة
            $imageUrl = null;
            if (!empty($validated['image'])) {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['image']);
                $imageData = base64_decode($imageData);

                $filename = 'boxes/' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $imageData);

                $imageUrl = rtrim((string) config('app.url'), '/') . '/storage/' . ltrim($filename, '/');
            }

            // 🛒 إنشاء منتج في سلة
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

            // ❌ لو فشل إنشاء المنتج في سلة
            if (!$sallaResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء المنتج في سلة',
                    'status'  => $sallaResponse->status(),
                    'error'   => $sallaResponse->json()
                ], 500);
            }

            // 📦 استخراج الرد
            $data = $sallaResponse->json();

            // 🔥 استخراج ID بطريقة آمنة (حسب اختلاف ردود API)
            $salla_product_id =
                $data['data']['id']
                ?? $data['data']['product']['id']
                ?? $data['id']
                ?? null;

            // ❌ إذا ما تم جلب ID
            if (!$salla_product_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم جلب Salla Product ID',
                    'debug'   => $data
                ], 500);
            }

            // 💾 حفظ البوكس
            $box = Box::create([
                'name'              => $validated['name'],
                'price'             => $validated['price'],
                'description'       => $validated['description'] ?? null,
                'image_url'         => $imageUrl,
                'store_id'          => $user->store_id,
                'salla_product_id'  => $salla_product_id,
            ]);

            // 📦 حفظ العناصر داخل البوكس
            foreach ($validated['elements'] as $elementData) {
                $element = BoxElement::create([
                    'box_id'       => $box->id,
                    'element_name' => $elementData['name'],
                ]);

                $productIds = array_column($elementData['products'], 'id');
                $element->products()->attach($productIds);
            }

            // ✅ نجاح العملية
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الباقة بنجاح',
                'data'    => [
                    'box_id' => $box->id,
                    'box_name' => $box->name,
                    'salla_product_id' => $salla_product_id
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المرسلة',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ عند حفظ الباقة',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $boxes = Box::select('id', 'name', 'price', 'image_url', 'description')->get();
        return response()->json(['data' => $boxes]);
    }

    public function show($id)
    {
        $box = Box::with(['elements.products'])->findOrFail($id);
        return response()->json(['data' => $box]);
    }
}
