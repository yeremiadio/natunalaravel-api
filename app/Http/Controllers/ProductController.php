<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $product = Product::latest()->with(['category' => function ($q) {
            $q->select('id', 'category_name', 'category_slug');
        }, 'product_images'])->filter(request(['search', 'sort', 'min_price', 'max_price', 'category']))->paginate(request('limit'));
        if (!$product) return $this->responseFailed('Data not found', '', 404);

        return $this->responseSuccess('List Products', $product, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string|unique:products,title',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|numeric',
            'product_images' => 'required|array|between:1,5',
            'product_images.*.image_name' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',

        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        if ($request->hasFile('thumbnail')) {
            $input['thumbnail'] = rand() . '.' . request()->thumbnail->getClientOriginalExtension();

            request()->thumbnail->move(public_path('assets/images/thumbnail/products/'), $input['thumbnail']);
        }

        $product = Product::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'thumbnail' => $input['thumbnail'] ?? null,
            'slug' =>  Str::slug($input['title']),
            'price' => $input['price'],
            'category_id' =>  $input['category_id'],
        ]);

        foreach ($input['product_images'] as $key => $imageValues) {
            if ($request->hasFile('product_images.' . $key . '.image_name')) {
                $imageValues['image_name'] = rand() . '.' . $request->product_images[$key]['image_name']->getClientOriginalExtension();

                $request->product_images[$key]['image_name']->move(public_path('assets/images/products/'), $imageValues['image_name']);
            }

            $product_image = ProductImage::create([
                'product_id' => $product->id,
                'image_name' => $imageValues['image_name'] ?? null
            ]);
        }

        $data = [
            'product' => Product::where('slug', $product->slug)->with(['category' => function ($q) {
                $q->select('id', 'category_name', 'category_slug');
            }, 'product_images'])->first(),
        ];

        return $this->responseSuccess('Product created successfully', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) return $this->responseFailed('Data not found', '', 404);

        $data = Product::where('slug', $product->slug)->with(['category' => function ($q) {
            $q->select('id', 'category_name', 'category_slug');
        }])->first();
        return $this->responseSuccess('Product detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->with('category')->first();
        if (!$product) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string|unique:products,title',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $oldFile = $product->thumbnail;
        if ($request->hasFile('thumbnail')) {
            File::delete('assets/images/thumbnail/products/' . $oldFile);
            $input['thumbnail'] = rand() . '.' . request()->thumbnail->getClientOriginalExtension();
            request()->thumbnail->move(public_path('assets/images/thubmnail/products/'), $input['thumbnail']);
        } else {
            $input['thumbnail'] = $oldFile;
        }
        $product->update([
            'title' => $input['title'],
            'description' => $input['description'],
            'thumbnail' => $input['thumbnail'],
            'slug' =>  Str::slug($input['title']),
            'category_id' =>  $input['category_id'],
        ]);

        $data = Product::where('slug', $product->slug)->with(['category' => function ($q) {
            $q->select('id', 'category_name', 'category_slug');
        }, 'images' => function ($q) {
            $q->select('id', 'image_name');
        }])->first();

        return $this->responseSuccess('Product updated successfully', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::where('id', $id)->first();
        if (!$product) return $this->responseFailed('Data not found', '', 404);

        if ($product->thumbnail) {
            File::delete('assets/images/thumbnail/products/' . $product->thumbnail);
        }

        $product->delete();

        return $this->responseSuccess('Product deleted successfully');
    }
}
