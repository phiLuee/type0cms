<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Blog Post Detailseite - Volt Page (nur veröffentlichte Posts)
Volt::route('/blog/{post:slug}', 'pages.blog.show')->name('blog.show');
