<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxElement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class BoxController extends Controller
{
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
            'display_value' => 1 //you need to set the image id as value You can upload a new image to product using attach image endpoint then use 'image' id from response,,https://docs.salla.dev/5394187e0
        ], $productIds);

        Http::withToken($token)
            ->acceptJson()
            ->post("https://api.salla.dev/admin/v2/products/{$sallaProductId}/options", [
                'name'         => $elementData['name'],
                'required'     => true,
                'display_type' => 'image',  // changed from 'text'
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

        $imageUrl = null;
        if (!empty($validated['image'])) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['image']);
            $imageData = base64_decode($imageData);
            $filename  = 'boxes/' . uniqid() . '.jpg';
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

        foreach ($validated['elements'] as $elementData) {
            $element = BoxElement::create(['box_id' => $box->id, 'element_name' => $elementData['name']]);
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

        $box->name        = $request->name;
        $box->price       = $request->price;
        $box->description = $request->description;

        if (!empty($request->image) && str_contains($request->image, 'data:image')) {
            $imageData      = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageData      = base64_decode($imageData);
            $filename       = 'boxes/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $box->image_url = Storage::url($filename);
        }

        $box->save();

        if ($request->has('elements')) {
            $box->elements()->each(fn($el) => $el->products()->detach());
            $box->elements()->delete();

            foreach ($request->elements as $elementData) {
                $element = BoxElement::create(['box_id' => $box->id, 'element_name' => $elementData['name']]);
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

\Log::info('GET product response', ['status' => $getRes->status()]);

if ($getRes->successful()) {
    foreach ($getRes->json('data.options', []) as $option) {
        $delRes = Http::withToken($token)
            ->acceptJson()
            ->delete("https://api.salla.dev/admin/v2/products/options/{$option['id']}");

        \Log::info('DELETE option', ['option_id' => $option['id'], 'status' => $delRes->status()]);
    }
}

            try {
                $this->pushOptionsToSalla($token, $box->salla_product_id, $request->elements);
                \Log::info('Options pushed successfully');
            } catch (\Exception $e) {
                \Log::error('Salla options sync failed: ' . $e->getMessage());
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

        $box->delete();
        return redirect()->back()->with('success', 'تم حذف الباقة');
    }
      public function details($id)
    {  $box = Box::with('elements.products')->where("salla_product_id",$id)->first();

    return response()->json([
            'success' => true,
            'message' => 'تم تحديث الباقة بنجاح',
            'data'    => $box
        ]);
    } 
}