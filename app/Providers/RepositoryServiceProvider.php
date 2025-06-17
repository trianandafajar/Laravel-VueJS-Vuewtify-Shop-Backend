<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BookRepository;
use App\Models\Book;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(BookRepository::class, function ($app) {
            return new BookRepository(new Book());
        });
    }

    public function boot()
    {
        //
    }
} 