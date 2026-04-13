<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class BoxController extends Controller
{
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
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء المنتج في سلة',
                'error'   => $sallaResponse->json()
            ], 500);
        }

        $data = $sallaResponse->json();
        $salla_product_id = $data['data']['id'] ?? $data['data']['product']['id'] ?? $data['id'] ?? null;

        if (!$salla_product_id) {
            return response()->json(['success' => false, 'message' => 'لم يتم جلب Salla Product ID', 'debug' => $data], 500);
        }

        $box = Box::create([
            'name'             => $validated['name'],
            'price'            => $validated['price'],
            'description'      => $validated['description'] ?? null,
            'image_url'        => $imageUrl,
            'store_id'         => $user->store_id,
            'salla_product_id' => $salla_product_id,
        ]);

        foreach ($validated['elements'] as $elementData) {
            $element = BoxElement::create(['box_id' => $box->id, 'element_name' => $elementData['name']]);
            $element->products()->attach(array_column($elementData['products'], 'id'));
        }

        return response()->json(['success' => true, 'message' => 'تم حفظ الباقة بنجاح', 'data' => ['box_id' => $box->id, 'salla_product_id' => $salla_product_id]], 201);
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
        $box = Box::findOrFail($id);

        if (!empty($request->image) && str_contains($request->image, 'data:image')) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageData = base64_decode($imageData);
            $filename = 'boxes/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $box->image_url = Storage::url($filename);
        }

        $box->update([
            'name'        => $request->name,
            'price'       => $request->price,
            'description' => $request->description,
            'image_url'   => $box->image_url,
        ]);

        if ($request->has('elements')) {
            $box->elements()->each(fn($el) => $el->products()->detach());
            $box->elements()->delete();

            foreach ($request->elements as $elementData) {
                $element = BoxElement::create(['box_id' => $box->id, 'element_name' => $elementData['name']]);
                $element->products()->attach(array_column($elementData['products'], 'id'));
            }
        }

        return response()->json(['success' => true, 'message' => 'تم تحديث الباقة بنجاح', 'data' => $box]);
    }

    public function destroy($id)
    {
        $box = \App\Models\Box::findOrFail($id);
        $box->delete(); // Cascades to elements via migration
        return redirect()->back()->with('success', 'تم حذف الباقة');
    }
}
