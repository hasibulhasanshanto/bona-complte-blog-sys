<?php

namespace App\Http\Controllers\Author;

use App\Tag;
use App\Post;
use App\User;
use App\Category;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\NewAuthorPost;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Auth::User()->posts()->with('user')->latest()->get();
        return view('backend.author.post.index', compact('posts') );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.author.post.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([

            'title' => 'required|unique:posts', 
            'image' => 'required|file|mimes:jpeg,png,jpg|max:3050', 
            'categories' => 'required', 
            'tags' => 'required', 
            'body' => 'required', 
        ]);
        
//          Get Image file
        $image = $request->file('image');
        $slug = Str::slug($request->title);

        if(isset($image)){
//          make Unique nam efor image
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
 //         Check is category Dir is Exists
            if(!Storage::disk('public')->exists('posts')){

                Storage::disk('public')->makeDirectory('posts');

            }
//          Resize image for category and Upload
                $postimage = Image::make($image)->resize('1600','479')->stream();
                Storage::disk('public')->put('posts/'. $imagename, $postimage);
 //         Check is category Slider Dir is Exists

        } 
        else{
            $imagename = 'default.png';
        }

//      Save all to category
        $posts = new Post();
        $posts->title = Str::title($request->title);
        $posts->user_id = Auth::id();
        $posts->image = $imagename; 
        $posts->slug = $slug;
        $posts->body = $request->body;
        
        if(isset($request->status)){
            $posts->status = true;
        }
        else{
            $posts->status = false;
        }
        $posts->is_approved = false;

        $posts->save();

        $posts->categories()->attach($request->categories);
        $posts->tags()->attach($request->tags);
        
        $users = User::where('role_id', '1')->get();
        Notification::send($users, new NewAuthorPost($posts));

        if( $posts){
            Toastr::success('Post Created Sucessfully!!', 'Success');
            return redirect()->route('author.post.index');

        }else{
            Toastr::error('Something Went Wrong :(', 'Error');
            return redirect()->route('author.post.index');
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        if ( $post->user_id != Auth::id() ) {

            Toastr::error('You are not authorized to access this post', 'Error');
            return redirect()->back();
        }
        
        return view('backend.author.post.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        if ( $post->user_id != Auth::id() ) {

            Toastr::error('You are not authorized to access this post', 'Error');
            return redirect()->back();
        }

        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.author.post.edit', compact('post','categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        if ( $post->user_id != Auth::id() ) {

            Toastr::error('You are not authorized to access this post', 'Error');
            return redirect()->back();
        }

        $request->validate([

            'title' => 'required', 
            'image' => 'image|file|mimes:jpeg,png,jpg|max:3050', 
            'categories' => 'required', 
            'tags' => 'required', 
            'body' => 'required', 
        ]);
        
//          Get Image file
        $image = $request->file('image');
        $slug = Str::slug($request->title);

        if(isset($image)){
//          make Unique nam efor image
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
 //         Check is category Dir is Exists
            if(!Storage::disk('public')->exists('posts')){

                Storage::disk('public')->makeDirectory('posts');

            }
            if(Storage::disk('public')->exists('posts/'.$post->image)){

                Storage::disk('public')->delete('posts/'.$post->image);

            }
//          Resize image for category and Upload
                $postimage = Image::make($image)->resize('1600','479')->stream();
                Storage::disk('public')->put('posts/'. $imagename, $postimage);
 //         Check is category Slider Dir is Exists

        } 
        else{
            $imagename = $post->image;
        }

//      Save all to category 
        $post->title = Str::title($request->title);
        $post->user_id = Auth::id();
        $post->image = $imagename; 
        $post->slug = $slug;
        $post->body = $request->body;
        
        if(isset($request->status)){
            $post->status = true;
        }
        else{
            $post->status = false;
        }
        $post->is_approved = false;

        $post->save();

        $post->categories()->sync($request->categories);
        $post->tags()->sync($request->tags);
        
        

        if( $post){
            Toastr::success('Post Updated Sucessfully!!', 'Success');
            return redirect()->route('author.post.index');

        }else{
            Toastr::error('Something Went Wrong :(', 'Error');
            return redirect()->route('author.post.index');
        } 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if ( $post->user_id != Auth::id() ) {

            Toastr::error('You are not authorized to access this post', 'Error');
            return redirect()->back();
        }
        
        if(Storage::disk('public')->exists('posts/'.$post->image)){

            Storage::disk('public')->delete('posts/'.$post->image);

        }
        $post->categories()->detach();
        $post->tags()->detach();
        $post->delete();

        if( $post){
            Toastr::success('Post Deleted Sucessfully!', 'Success');
            return redirect()->route('author.post.index');

        }else{
            Toastr::error('Something Went Wrong :(', 'Error');
            return redirect()->route('author.post.index');
        } 
    }
}
