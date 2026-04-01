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
}