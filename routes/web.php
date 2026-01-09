<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Plugins\Blog\Models\Post;

// Homepage - Volt Page
Volt::route('/', 'pages.home')->name('home');

// Blog Post Detailseite - Volt Page
Volt::route('/blog/{post:slug}', 'pages.blog.show')->name('blog.show');
