<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\Book as BookResource;
use App\Http\Resources\Books as BookResourceCollection;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $criteria = Book::paginate(6);
        return new BookResourceCollection($criteria);
    }

    /**
     * Display top books based on views.
     *
     * @param  int  $count
     * @return \Illuminate\Http\Response
     */
    public function top(int $count)
    {
        $criteria = Book::orderBy('views', 'DESC')->limit($count)->get();
        return new BookResourceCollection($criteria);
    }

    /**
     * Search for books by title.
     *
     * @param  string  $keyword
     * @return \Illuminate\Http\Response
     */
    public function search(string $keyword)
    {
        $criteria = Book::where('title', 'LIKE', "%$keyword%")
            ->orderBy('views', 'DESC')
            ->get();
        return new BookResourceCollection($criteria);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate and store a new book (logic goes here)
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        return new BookResource($book);
    }

    /**
     * Display a book by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function slug(string $slug)
    {
        $book = Book::where('slug', $slug)->first();
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // Validate and update a book (logic goes here)
    }

    /**
     * Get cart information for books.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cart(Request $request)
    {
        // Decode the incoming carts JSON.
        $carts = json_decode($request->carts, true);
        $book_carts = [];

        foreach ($carts as $cart) {
            $book = Book::find($cart['id']);

            // If the book is found
            if ($book) {
                $quantity = min((int)$cart['quantity'], (int)$book->stock);
                $note = $quantity === $cart['quantity'] ? 'safe' : ($quantity === $book->stock ? 'out of stock' : 'unsafe');

                $book_carts[] = [
                    'id' => $book->id,
                    'title' => $book->title,
                    'cover' => $book->cover,
                    'price' => $book->price,
                    'quantity' => $quantity,
                    'note' => $note,
                ];
            } else {
                // If book not found, add an error note
                $book_carts[] = [
                    'id' => $cart['id'],
                    'note' => 'book not found',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart data retrieved',
            'data' => $book_carts,
        ], 200);
    }
}
