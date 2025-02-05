<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;

// Главная страница
Route::get('/', function () {
    // Если пользователь авторизован, перенаправляем в личный кабинет
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Если не авторизован, перенаправляем на страницу входа
    return redirect()->route('login');
});

Route::get('/login', function () {
    // Если пользователь уже авторизован, перенаправляем в личный кабинет
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    // Если пользователь уже авторизован, перенаправляем в личный кабинет
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.register');
})->name('register');

Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $query = auth()->user()->posts()->with(['user', 'tags']);

        // Фильтрация по тегам
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        // Сортировка
        $sortField = $request->filled('sort_by') ? $request->sort_by : 'created_at';
        $sortDirection = $request->filled('sort_direction') ? $request->sort_direction : 'desc';
        $allowedSortFields = ['title', 'created_at', 'is_active'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $posts = $query->paginate(10);
        return view('dashboard', compact('posts'));
    })->name('dashboard');

    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/posts/{post}/remove-tag/{tag}', [PostController::class, 'removeTag'])
        ->name('posts.remove-tag');
});
