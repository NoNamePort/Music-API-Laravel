<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пост</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <div class="max-w-2xl mx-auto mt-8 px-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Редактировать пост</h2>

                <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Название</label>
                        <input type="text" 
                               name="title" 
                               value="{{ old('title', $post->title) }}"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Описание</label>
                        <textarea name="description" 
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description', $post->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $post->is_active) ? 'checked' : '' }}
                                   class="form-checkbox">
                            <span class="ml-2">Активен</span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Теги (через запятую)</label>
                        <input type="text" 
                               name="tags" 
                               value="{{ old('tags', $post->tags->pluck('name')->join(', ')) }}"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                               placeholder="тег1, тег2, тег3">
                        @error('tags')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Аудио файл (MP3)
                        </label>
                        @if($post->audio_path)
                            <div class="mb-2">
                                <audio controls class="w-full">
                                    <source src="{{ asset('storage/' . $post->audio_path) }}" type="audio/mpeg">
                                    Ваш браузер не поддерживает аудио элемент.
                                </audio>
                                <label class="inline-flex items-center mt-2">
                                    <input type="checkbox" name="remove_audio" class="form-checkbox">
                                    <span class="ml-2">Удалить текущий аудио файл</span>
                                </label>
                            </div>
                        @endif
                        <input type="file" 
                               name="audio_file" 
                               accept="audio/mpeg"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('audio_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-xs mt-1">Максимальный размер: 100MB</p>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('dashboard') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Отмена
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 