<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoxRequest;
use App\Models\Box;
use App\Models\BoxElement;

class BoxController extends Controller
{
    public function store(StoreBoxRequest $request)
    {
        try {
            $validated = $request->validated();

            $box = Box::create([
                'name'        => $validated['name'],
                'price'       => $validated['price'],
                'description' => $validated['description'] ?? null,
                'image_url'   => $validated['image_url'] ?? null,
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