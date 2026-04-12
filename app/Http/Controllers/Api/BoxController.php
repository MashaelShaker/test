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

            $imageUrl = null;
            if (!empty($validated['image'])) {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['image']);
                $imageData = base64_decode($imageData);
                $filename = 'boxes/' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $imageData);
                $imageUrl = Storage::url($filename);
            }

            // Create product on Salla
            $sallaResponse = Http::withToken($token)
                ->post('https://api.salla.dev/admin/v2/products', [
                    'name'         => $validated['name'],
                    'price'        => $validated['price'],
                    'description'  => $validated['description'] ?? '',
                    'status'       => 'sale',
                    'product_type' => 'product',
                    'quantity'     => 10,
                ]);

            if (!$sallaResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء المنتج في سلة',
                    'error'   => $sallaResponse->json()
                ], 500);
            }

            $box = Box::create([
                'name'        => $validated['name'],
                'price'       => $validated['price'],
                'description' => $validated['description'] ?? null,
                'image_url'   => $imageUrl,
                'store_id'    => $user->store_id,
            ]);

            foreach ($validated['elements'] as $elementData) {
                $element = BoxElement::create([
                    'box_id'       => $box->id,
                    'element_name' => $elementData['name'],
                ]);
                $productIds = array_column($elementData['products'], 'id');
                $element->products()->attach($productIds);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الباقة بنجاح',
                'data'    => ['box_id' => $box->id, 'box_name' => $box->name]
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
