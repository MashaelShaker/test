<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoxController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                       => 'required|string',
            'price'                      => 'required|numeric',
            'description'                => 'nullable|string',
            'store_id'                   => 'nullable|string',
            'image'                      => 'nullable|string',
            'elements'                   => 'required|array|min:1',
            'elements.*.name'            => 'required|string',
            'elements.*.products'        => 'required|array|min:1',
            'elements.*.products.*.id'   => 'required|exists:products,id',
        ]);

        $imageUrl = null;
        if (!empty($validated['image'])) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['image']);
            $imageData = base64_decode($imageData);
            $filename = 'boxes/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $imageUrl = Storage::url($filename);
        }

        $box = Box::create([
            'name'        => $validated['name'],
            'price'       => $validated['price'],
            'description' => $validated['description'] ?? null,
            'store_id'    => $validated['store_id'] ?? null,
            'image_url'   => $imageUrl,
        ]);

        foreach ($validated['elements'] as $elementData) {
            $element = $box->elements()->create(['element_name' => $elementData['name']]);
            $productIds = array_column($elementData['products'], 'id');
            $element->products()->attach($productIds);
        }

        return response()->json(['message' => 'تم حفظ الباقة بنجاح', 'box' => $box], 201);
    }

    public function index()
    {
        $boxes = Box::with('elements.products')->get();
        return view('boxes.index', compact('boxes'));
    }

    // In BoxController.php
    public function show($id)
    {
        $box = Box::with('elements.products')->findOrFail($id);
        // Change 'show' to 'boxes.show' if the file is in resources/views/boxes/show.blade.php
        return view('boxes.show', compact('box'));
    }

    public function edit($id)
    {
        $box = Box::with('elements.products')->findOrFail($id);

        // تحويل البيانات لشكل يفهمه JavaScript بسهولة
        $elements = $box->elements->map(function ($el) {
            return [
                'id' => 'element-' . $el->id,
                'name' => $el->element_name,
                'products' => $el->products->map(function ($p) {
                    return [
                        'id'    => $p->id,
                        'name'  => $p->name,
                        'price' => (float) $p->price,
                        'image' => $p->image_url,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return view('dashboard', compact('box', 'elements')); // نمرر $elements هنا
    }

    public function update(Request $request, $id)
    {
        $box = \App\Models\Box::findOrFail($id);

        if (!empty($request->image) && strpos($request->image, 'data:image') !== false) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageData = base64_decode($imageData);
            $filename = 'boxes/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $box->image_url = Storage::url($filename);
        }

        $box->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image_url' => $box->image_url,
        ]);

        // Sync elements: Simplest way is to drop and recreate for nested relations
        $box->elements()->delete();
        foreach ($request->elements as $elementData) {
            $element = $box->elements()->create(['element_name' => $elementData['name']]);
            $productIds = array_column($elementData['products'], 'id');
            $element->products()->attach($productIds);
        }

        return response()->json(['message' => 'تم تحديث الباقة بنجاح']);
    // Inside public function update

}

    public function destroy($id)
    {
        $box = \App\Models\Box::findOrFail($id);
        $box->delete(); // Cascades to elements via migration
        return redirect()->back()->with('success', 'تم حذف الباقة');
    }
}
