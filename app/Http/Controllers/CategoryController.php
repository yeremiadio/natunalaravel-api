<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Telegram\Bot\Api;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = Category::with('products')->get();
        $data = Category::all();
        return $this->responseSuccess('List all categories', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'category_name' => 'required|string|unique:categories,category_name',

        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $category = Category::create([
            'category_name' => $input['category_name'],
            'category_slug' => Str::slug($input['category_name']),
        ]);

        $telegramMessage = "Kategori baru berhasil ditambahkan, nama kategori: {$input['category_name']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $data = [
            'category' => $category,
        ];

        return $this->responseSuccess('Category created successfully', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $category = Category::where('category_slug', $slug)->first();
        if (!$category) return $this->responseFailed('Data not found', '', 404);

        $data = Category::where('category_slug', $slug)->with('products')->first();
        return $this->responseSuccess('List Specific Product Category', $data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::where('id', $id)->first();
        if (!$category) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'category_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error validation', $validator->errors(), 400);
        }

        $category->update([
            'category_name' => $input['category_name'],
            'category_slug' => Str::slug($input['category_name'])
        ]);

        $telegramMessage = "Kategori baru berhasil diubah, nama kategori: {$input['category_name']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $data = Category::find($id);

        return $this->responseSuccess('Category updated successfully', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::where('id' ,$id)->first();
        if (!$category) return $this->responseFailed('Category not found', '', 404);

        $telegramMessage = "Kategori baru berhasil dihapus, nama kategori: {$category->category_name}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $category->delete();

        return $this->responseSuccess('Category deleted successfully');
    }
}
