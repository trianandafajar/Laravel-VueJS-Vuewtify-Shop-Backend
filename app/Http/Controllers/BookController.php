<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\Book as BookResource;
use App\Http\Resources\Books as BookResourceCollection;

class BookController extends Controller
{
    /**
     * Get all books with pagination.
     */
    public function index()
    {
        $books = Book::paginate(6);
        return new BookResourceCollection($books);
    }

    /**
     * Get top books by views.
     */
    public function top(int $count)
    {
        $books = Book::orderBy('views', 'DESC')->limit($count)->get();
        return new BookResourceCollection($books);
    }

    /**
     * Search books by title.
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $books = Book::where('title', 'LIKE', "%$keyword%")
            ->orderBy('views', 'DESC')
            ->paginate(6);  // Gunakan pagination untuk hasil lebih optimal.

        return new BookResourceCollection($books);
    }

    /**
     * Store a new book.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'cover' => 'nullable|string',
        ]);

        $book = Book::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Book added successfully',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * Get a book by ID.
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
     * Get a book by slug.
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
     * Update a book.
     */
    public function update(Request $request, int $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'cover' => 'nullable|string',
        ]);

        $book->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Book updated successfully',
            'data' => new BookResource($book),
        ], 200);
    }

    /**
     * Handle book cart logic.
     */
    public function cart(Request $request)
    {
        $cartItems = json_decode($request->carts, true);
        $bookIds = array_column($cartItems, 'id');

        // Ambil semua buku yang ada dalam keranjang
        $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');

        $cartResponse = array_map(function ($cartItem) use ($books) {
            $bookId = $cartItem['id'];
            $quantityRequested = (int) $cartItem['quantity'];

            if (isset($books[$bookId])) {
                $book = $books[$bookId];
                $quantityAvailable = min($quantityRequested, $book->stock);
                $note = $quantityAvailable === $quantityRequested ? 'safe' : ($quantityAvailable === $book->stock ? 'out of stock' : 'unsafe');

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'cover' => $book->cover,
                    'price' => $book->price,
                    'quantity' => $quantityAvailable,
                    'note' => $note,
                ];
            } else {
                return [
                    'id' => $bookId,
                    'note' => 'book not found',
                ];
            }
        }, $cartItems);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart data retrieved',
            'data' => $cartResponse,
        ], 200);
    }
}
