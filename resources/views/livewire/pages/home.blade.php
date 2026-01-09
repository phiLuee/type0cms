<?php

use function Livewire\Volt\{layout, title};

layout('components.layouts.app');
title('Home - ' . config('app.name'));

?>

<div>
    <main class="w-full max-w-7xl mx-auto p-6 lg:p-8 min-h-screen flex items-center justify-center">
        <livewire:blog-post-list />
    </main>
</div>
