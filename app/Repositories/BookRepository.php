<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookRepository
{
    protected $model;

    public function __construct(Book $model)
    {
        $this->model = $model;
    }

    public function getAllPaginated(int $perPage = 6): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function getTopBooks(int $count): Collection
    {
        return $this->model->orderBy('views', 'DESC')
            ->limit($count)
            ->get();
    }

    public function searchByTitle(string $keyword, int $perPage = 6): LengthAwarePaginator
    {
        return $this->model->where('title', 'LIKE', "%{$keyword}%")
            ->orderBy('views', 'DESC')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Book
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function findById(int $id): ?Book
    {
        return $this->model->find($id);
    }

    public function create(array $data): Book
    {
        return $this->model->create($data);
    }

    public function update(Book $book, array $data): bool
    {
        return $book->update($data);
    }

    public function getBooksByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get()->keyBy('id');
    }
} 