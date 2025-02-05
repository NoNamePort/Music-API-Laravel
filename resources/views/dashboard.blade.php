<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div class="text-xl font-semibold">
                        Личный кабинет
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Выйти
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-6xl mx-auto mt-8 px-4">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Фильтры и сортировка -->
                <div class="mb-6">
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4">
                        <!-- Сохраняем текущую страницу -->
                        <input type="hidden" name="page" value="{{ request('page') }}">
                        
                        <select name="sort_by" class="border rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>По дате создания</option>
                            <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>По названию</option>
                            <option value="is_active" {{ request('sort_by') == 'is_active' ? 'selected' : '' }}>По статусу</option>
                        </select>
                        <select name="sort_direction" class="border rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                            <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                        </select>
                        <div class="flex gap-2">
                            <input type="text" 
                                   name="tag" 
                                   value="{{ request('tag') }}" 
                                   placeholder="Фильтр по тегу" 
                                   class="border rounded px-2 py-1">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded">
                                Фильтровать
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Мои посты</h2>
                    <a href="{{ route('posts.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Создать пост
                    </a>
                </div>

                <div class="space-y-4">
                    @foreach($posts as $post)
                    <div class="border p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-semibold">{{ $post->title }}</h3>
                                <p class="text-gray-600">{{ $post->description }}</p>
                                <div class="mt-2 text-sm text-gray-500">
                                    <span>Создан: {{ $post->created_at->format('d.m.Y H:i') }}</span>
                                    <span class="ml-4">Обновлен: {{ $post->updated_at->format('d.m.Y H:i') }}</span>
                                </div>
                                <div class="mt-2">
                                    <span class="px-2 py-1 rounded-full text-sm {{ $post->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $post->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    @foreach($post->tags as $tag)
                                    <span class="inline-flex items-center bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">
                                        {{ $tag->name }}
                                        <a href="{{ route('posts.remove-tag', ['post' => $post->id, 'tag' => $tag->id]) }}" 
                                           onclick="return confirm('Удалить тег?')"
                                           class="ml-1 text-gray-500 hover:text-gray-700">×</a>
                                    </span>
                                    @endforeach
                                </div>
                                @if($post->audio_path)
                                    <div class="mt-2">
                                        <audio controls class="w-full">
                                            <source src="{{ Storage::disk('public')->url($post->audio_path) }}" type="audio/mpeg">
                                            Ваш браузер не поддерживает аудио элемент.
                                        </audio>
                                    </div>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('posts.edit', $post) }}" class="text-blue-500 hover:text-blue-700">
                                    Редактировать
                                </a>
                                <form method="POST" action="{{ route('posts.destroy', $post) }}" class="inline" onsubmit="return confirm('Вы уверены?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Пагинация -->
                <div class="mt-4">
                    {{ $posts->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    </div>
</body>
</html> 