<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Repositories\BookRepository;
use App\Http\Resources\Book as BookResource;
use App\Http\Resources\Books as BookResourceCollection;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    protected $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Get all books with pagination.
     */
    public function index(): BookResourceCollection
    {
        $books = $this->bookRepository->getAllPaginated();
        return new BookResourceCollection($books);
    }

    /**
     * Get top books by views.
     */
    public function top(int $count): BookResourceCollection
    {
        $books = $this->bookRepository->getTopBooks($count);
        return new BookResourceCollection($books);
    }

    /**
     * Search books by title.
     */
    public function search(Request $request): BookResourceCollection
    {
        $keyword = $request->input('keyword', '');
        $books = $this->bookRepository->searchByTitle($keyword);
        return new BookResourceCollection($books);
    }

    /**
     * Store a new book.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'cover' => 'nullable|string',
        ]);

        $book = $this->bookRepository->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Book added successfully',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * Get a book by ID.
     */
    public function show(int $id): JsonResponse
    {
        $book = $this->bookRepository->findById($id);
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => new BookResource($book)
        ]);
    }

    /**
     * Get a book by slug.
     */
    public function slug(string $slug): JsonResponse
    {
        $book = $this->bookRepository->findBySlug($slug);
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => new BookResource($book)
        ]);
    }

    /**
     * Update a book.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $book = $this->bookRepository->findById($id);
        
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

        $this->bookRepository->update($book, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Book updated successfully',
            'data' => new BookResource($book),
        ]);
    }

    /**
     * Handle book cart logic.
     */
    public function cart(Request $request): JsonResponse
    {
        $cartItems = json_decode($request->carts, true);
        $bookIds = array_column($cartItems, 'id');
        $books = $this->bookRepository->getBooksByIds($bookIds);

        $cartResponse = array_map(function ($cartItem) use ($books) {
            $bookId = $cartItem['id'];
            $quantityRequested = (int) $cartItem['quantity'];

            if (isset($books[$bookId])) {
                $book = $books[$bookId];
                $quantityAvailable = min($quantityRequested, $book->stock);
                $note = $this->determineCartNote($quantityAvailable, $quantityRequested, $book->stock);

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'cover' => $book->cover,
                    'price' => $book->price,
                    'quantity' => $quantityAvailable,
                    'note' => $note,
                ];
            }

            return [
                'id' => $bookId,
                'note' => 'book not found',
            ];
        }, $cartItems);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart data retrieved',
            'data' => $cartResponse,
        ]);
    }

    private function determineCartNote(int $quantityAvailable, int $quantityRequested, int $stock): string
    {
        if ($quantityAvailable === $quantityRequested) {
            return 'safe';
        }
        
        return $quantityAvailable === $stock ? 'out of stock' : 'unsafe';
    }
}
