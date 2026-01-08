<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Blog\Models\Post;

Route::get('/', function () {
    return view('index');
});

// Blog Post Detailseite
Route::get('/blog/{post:slug}', function (Post $post) {
    // Nur veröffentlichte Posts anzeigen
    if (!$post->is_published) {
        abort(404);
    }

    return view('blog.show', compact('post'));
})->name('blog.show');
