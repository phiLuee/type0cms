<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Homepage - Volt Page
Volt::route('/', 'pages.home')->name('home');
