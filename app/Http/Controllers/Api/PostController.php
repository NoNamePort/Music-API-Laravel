<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;
use App\Services\AudioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    private $audioService;
    
    public function __construct(AudioService $audioService)
    {
        $this->audioService = $audioService;
    }

    public function index(Request $request)
    {
        $query = auth()->user()->posts()->with(['user', 'tags']);

        // Фильтрация по тегам
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        // Сортировка
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'is_active'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $posts = $query->paginate(10);
        
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'is_active' => 'boolean',
            'tags' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3|max:102400', // 100MB максимум
        ]);

        $audioPath = null;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $audioPath = $file->storeAs('audio', $filename, 'public');
            
            try {
                $audioPath = $this->audioService->compressAudio($audioPath, $filename);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Ошибка при обработке аудио файла',
                    'errors' => ['audio_file' => [$e->getMessage()]]
                ], 422);
            }
        }

        $post = auth()->user()->posts()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_active' => $request->boolean('is_active'),
            'audio_path' => $audioPath
        ]);

        if (!empty($validated['tags'])) {
            $this->syncTags($post, $validated['tags']);
        }

        return response()->json([
            'message' => 'Пост успешно создан',
            'post' => $post->load('tags')
        ], 201);
    }

    public function show(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($post->load('tags'));
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'is_active' => 'boolean',
            'tags' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3|max:102400',
        ]);

        $audioPath = $post->audio_path;

        if ($request->hasFile('audio_file')) {
            if ($audioPath) {
                Storage::disk('public')->delete($audioPath);
            }
            $file = $request->file('audio_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $audioPath = $file->storeAs('audio', $filename, 'public');
            
            try {
                $audioPath = $this->audioService->compressAudio($audioPath, $filename);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Ошибка при обработке аудио файла',
                    'errors' => ['audio_file' => [$e->getMessage()]]
                ], 422);
            }
        } elseif ($request->boolean('remove_audio') && $audioPath) {
            Storage::disk('public')->delete($audioPath);
            $audioPath = null;
        }

        $post->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_active' => $request->boolean('is_active'),
            'audio_path' => $audioPath
        ]);

        if ($request->has('tags')) {
            $this->syncTags($post, $validated['tags']);
        }

        return response()->json([
            'message' => 'Пост успешно обновлен',
            'post' => $post->load('tags')
        ]);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($post->audio_path && Storage::disk('public')->exists($post->audio_path)) {
            Storage::disk('public')->delete($post->audio_path);
        }

        $post->delete();

        return response()->json(['message' => 'Пост успешно удален']);
    }

    public function removeTag(Post $post, Tag $tag)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->tags()->detach($tag->id);
        
        if ($tag->posts()->count() === 0) {
            $tag->delete();
        }

        return response()->json([
            'message' => 'Тег успешно удален',
            'post' => $post->load('tags')
        ]);
    }

    private function syncTags($post, $tagsString)
    {
        if (empty(trim($tagsString))) {
            $post->tags()->detach();
            return;
        }

        $tagNames = array_map('trim', explode(',', $tagsString));
        $tags = collect($tagNames)->filter(function ($tagName) {
            return !empty(trim($tagName));
        })->map(function ($tagName) {
            return Tag::firstOrCreate(['name' => $tagName]);
        });
        
        $post->tags()->sync($tags->pluck('id'));
    }
} 