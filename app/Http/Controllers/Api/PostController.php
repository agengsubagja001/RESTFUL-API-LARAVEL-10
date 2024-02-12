<?php

namespace App\Http\Controllers\Api;
//import Model "Post"
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
//import Resource "PostResource"
use App\Http\Resources\PostResource;

use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all posts
        $posts = Post::latest()->paginate(5);
        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    /**
     * store
     *
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required|min:10',
            'content' => 'required|min:10',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());
        //create post

        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(
            true,
            'Data Post Berhasil Ditambahkan!',
            $post
        );
    }


    /**
     * show
     *
     * @param  mixed $post
     * @return void
     */
    public function show($id)
    {
        //find post by ID
        $post = Post::find($id);

        //return single post as a resource
        return new PostResource(true, 'Detail Data Post!', $post);
    }

    public function update(Request $request, $id)
    {

        // define validator rule
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = Post::find($id);

        if ($request->hasFile('image')) {
            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/' . basename($post->image));

            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,

            ]);
        } else {

            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    // method delete
    public function destroy($id)
    {
        $post = Post::find($id);

        Storage::delete('public/posts/' . basename($post->image));

        $post->delete();

        return new PostResource(true, 'Data berhasil di hapus!', $post);
    }
}
