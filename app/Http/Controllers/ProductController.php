<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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
        }])->filter(request(['search', 'sort', 'min_price', 'max_price', 'category']))->paginate(request('limit'));
        $data = [
            'products' => $product,

        ];
        return $this->responseSuccess('List Products', $data);
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',

        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        if ($request->hasFile('image')) {
            $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();

            request()->image->move(public_path('assets/images/products/'), $input['image']);
        }

        $product = Product::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'image' => $input['image'] ?? null,
            'slug' =>  Str::slug($input['title']),
            'price' => $input['price'],
            'category_id' =>  $input['category_id'],
        ]);

        $data = [
            'product' => $product,
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $oldFile = $product->image;
        if ($request->hasFile('image')) {
            File::delete('assets/images/products/' . $oldFile);
            $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();
            request()->image->move(public_path('assets/images/products/'), $input['image']);
        } else {
            $input['image'] = $oldFile;
        }
        $product->update([
            'title' => $input['title'],
            'description' => $input['description'],
            'image' => $input['image'],
            'slug' =>  Str::slug($input['title']),
            'category_id' =>  $input['category_id'],
        ]);

        $data = Product::where('slug', $product->slug)->with(['category' => function ($q) {
            $q->select('id', 'category_name', 'category_slug');
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

        if ($product->image) {
            File::delete('assets/images/products/' . $product->image);
        }

        $product->delete();

        return $this->responseSuccess('Product deleted successfully');
    }
}
