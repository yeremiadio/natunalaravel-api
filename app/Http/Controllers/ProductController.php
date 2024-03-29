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
use Telegram\Bot\Api;

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
        }, 'product_images'])->filter(request(['search', 'sort', 'orderby', 'min_price', 'max_price', 'category']))->paginate(request('limit'));
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
            'description' => 'required|min:15|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|numeric',
            'unit' => 'required|string',
            'product_images' => 'nullable',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $input['thumbnail'] = null;
            if ($request->hasFile('thumbnail')) {
                $input['thumbnail'] = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
                // $input['thumbnail'] = rand() . '.' . request()->thumbnail->getClientOriginalExtension();

                // request()->thumbnail->move(public_path('assets/images/thumbnail/products/'), $input['thumbnail']);
            }

            $product = Product::create([
                'title' => $input['title'],
                'description' => $input['description'],
                'thumbnail' => $input['thumbnail'],
                'slug' =>  Str::slug($input['title']),
                'price' => $input['price'],
                'unit' => $input['unit'],
                'category_id' =>  $input['category_id'],
            ]);

            if ($request->hasFile('product_images')) {
                $images = $request->file('product_images');

                foreach ($images as $image) {
                    $imageName = cloudinary()->upload($image->getRealPath())->getSecurePath();
                    $request['product_id'] = $product->id;
                    $request['image_name'] = $imageName;
                    $image->move(public_path('assets/images/products/'), $imageName);
                    ProductImage::create($request->all());
                }
            }

            DB::commit();

            $telegramMessage = "Produk baru berhasil ditambahkan, nama produk: {$input['title']}";

            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

            $telegram->sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
                'text' => $telegramMessage
            ]);

            $data = [
                'product' => Product::where('slug', $product->slug)->with(['category' => function ($q) {
                    $q->select('id', 'category_name', 'category_slug');
                }, 'product_images'])->first(),
            ];

            return $this->responseSuccess('Product created successfully', $data, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Product failed to create');
        }
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
        }, 'product_images'])->first();
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
    // public function updateAlt(Request $request, $slug)
    // {
    //     $product = Product::where('slug', $slug)->with('category')->first();
    //     if (!$product) return $this->responseFailed('Data not found', '', 404);

    //     $input = $request->all();
    //     $validator = Validator::make($input, [
    //         'title' => 'required|string',
    //         'description' => 'required|min:15|string',
    //         'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'price' => 'required|numeric',
    //         'quantity' => 'required|min:1|numeric',
    //         'product_images' => 'required|array|between:1,5',
    //         'product_images.*.id' => 'required|numeric',
    //         'product_images.*.image_name' => 'nullable|image',
    //         'category_id' => 'required|exists:categories,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->responseFailed('Error Validation', $validator->errors(), 400);
    //     }

    //     try {
    //         DB::beginTransaction();
    //         $oldFile = $product->thumbnail;
    //         if ($request->hasFile('thumbnail')) {
    //             File::delete('assets/images/thumbnail/products/' . $oldFile);
    //             $input['thumbnail'] = rand() . '.' . request()->thumbnail->getClientOriginalExtension();
    //             request()->thumbnail->move(public_path('assets/images/thubmnail/products/'), $input['thumbnail']);
    //         } else {
    //             $input['thumbnail'] = $oldFile;
    //         }
    //         $product->update([
    //             'title' => $input['title'],
    //             'description' => $input['description'],
    //             'thumbnail' => $input['thumbnail'],
    //             'slug' =>  Str::slug($input['title']),
    //             'quantity' => $input['quantity'],
    //             'category_id' =>  $input['category_id'],
    //         ]);

    //         foreach ($input['product_images'] as $key => $imageValues) {
    //             if ($imageValues['id'] == -1) {
    //                 $imageValues['image_name'] = null;
    //                 if ($request->hasFile('product_images.' . $key . '.image_name')) {
    //                     $imageValues['image_name'] = rand() . '.' . $request->product_images[$key]['image_name']->getClientOriginalExtension();

    //                     $request->product_images[$key]['image_name']->move(public_path('assets/images/products/'), $imageValues['image_name']);
    //                 }
    //                 ProductImage::create([
    //                     'product_id' => $product->id,
    //                     'image_name' => $imageValues['image_name']
    //                 ]);
    //             } else {
    //                 $oldFile = $product->product_images[$key]->image_name;
    //                 if ($request->hasFile('product_images.' . $key . '.image_name')) {
    //                     $imageValues['image_name'] = rand() . '.' . $request->product_images[$key]['image_name']->getClientOriginalExtension();

    //                     $request->product_images[$key]['image_name']->move(public_path('assets/images/products/'), $imageValues['image_name']);
    //                 } else {
    //                     $imageValues['image_name'] = $oldFile;
    //                 }

    //                 ProductImage::where('id', $imageValues['id'])
    //                     ->update([
    //                         'image_name' => $imageValues['image_name']
    //                     ]);
    //             }
    //         }

    //         $telegramMessage = "Produk baru berhasil diubah, nama produk: {$input['title']}";

    //         $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

    //         $telegram->sendMessage([
    //             'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
    //             'text' => $telegramMessage
    //         ]);

    //         DB::commit();

    //         $data = Product::where('slug', $product->slug)->with(['category' => function ($q) {
    //             $q->select('id', 'category_name', 'category_slug');
    //         }, 'product_images'])->first();

    //         return $this->responseSuccess('Product updated successfully', $data, 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->responseFailed('Product update failed');
    //     }
    // }

    public function update(Request $request, $id)
    {
        $product = Product::where('id', $id)->first();
        if (!$product) return $this->responseFailed('Data not found', '', 404);
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'thumbnail' => 'nullable',
            'unit' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }
        try {
            DB::beginTransaction();
            $oldFile = $product->thumbnail;
            if ($request->hasFile('thumbnail')) {
                // File::delete('assets/images/thumbnail/products/' . $oldFile);
                $input['thumbnail'] = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
                // $thumbnailName = rand() . '.' . request()->thumbnail->getClientOriginalExtension();
                // $input['thumbnail'] = $thumbnailName;
                // $thumbnail->move(public_path('assets/images/thumbnail/products/'), $thumbnailName);
            } else {
                $input['thumbnail'] = $oldFile;
            }
            $product->update([
                'title' => $input['title'],
                'description' => $input['description'],
                'thumbnail' => $input['thumbnail'],
                'price' => $input['price'],
                'slug' =>  Str::slug($input['title']),
                'category_id' =>  $input['category_id'],
                'unit' => $input['unit'],
            ]);
            if ($request->hasFile('product_images')) {
                $images = $request->file('product_images');
                foreach ($images as $image) {
                    $imageName = cloudinary()->upload($image->getRealPath())->getSecurePath();
                    $request['product_id'] = $product->id;
                    $request['image_name'] = $imageName;
                    $image->move(public_path('assets/images/products/'), $imageName);
                    ProductImage::create($request->all());
                }
            }
            DB::commit();
            $data = Product::where('slug', $product->slug)->with(['category' => function ($q) {
                $q->select('id', 'category_name', 'category_slug');
            }, 'product_images'])->first();

            return $this->responseSuccess('Product updated successfully', $data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Product update failed');
        }
    }

    public function deleteProductImage($id)
    {
        $images = ProductImage::findOrFail($id);
        if (File::exists("assets/images/products/" . $images->image)) {
            File::delete("assets/images/products/" . $images->image);
        }

        ProductImage::find($id)->delete();
        return $this->responseSuccess('Product image deleted successfully', $images, 200);
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

        foreach ($product->product_images as $productValue) {
            if ($productValue->image_name) {
                File::delete('assets/images/products/' . $productValue->image_name);
            }
        }

        $telegramMessage = "Produk berhasil dihapus, nama produk: {$product->title}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $product->delete();

        return $this->responseSuccess('Product deleted successfully');
    }
}
