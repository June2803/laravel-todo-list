<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>待辦清單</title>
    <link rel="stylesheet" href="{{ asset('css/todo.css') }}">
    <script src="{{ asset('js/appearance.js') }}"></script>
    <script defer src="{{ asset('js/todo.js') }}"></script>
</head>
<body>
<div class="page-scroll">
<main class="list">
    <header>
        <h1>待辦清單</h1>
        <div class="header-tools">
            <span>@if ($selectedTag !== null) 篩選結果 · @endif{{ $todos->where('is_completed', false)->count() }} 件待完成</span>
            <button id="appearance-open" type="button" aria-haspopup="dialog" aria-controls="appearance-dialog">設定</button>
        </div>
    </header>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif

    <details class="add-disclosure" @if ($errors->getBag('createTodo')->any()) open @endif>
        <summary @if (session('focus_new_todo')) autofocus @endif>
            <span class="add-sign" aria-hidden="true"></span>新增待辦
        </summary>
        <form class="add-form" @if (session('focus_new_todo')) data-created @endif method="POST" action="{{ route('todos.store', $filter) }}">
            @csrf
            @include('todos.fields', ['bag' => 'createTodo', 'item' => null])
            <button class="primary" type="submit">新增</button>
        </form>
    </details>

    @if ($selectedTag !== null)
        <div class="tag-filter">
            <span>依標籤篩選</span>
            <span class="active-tag">
                <span>{{ $selectedTag }}</span>
                <a class="tag-clear" href="{{ route('todos.index') }}"
                   aria-label="取消「{{ $selectedTag }}」標籤篩選" title="取消篩選">×</a>
            </span>
        </div>
    @endif

    <ul class="items">
        @forelse ($todos as $todo)
            @php($editBag = 'editTodo'.$todo->id)
            @php($editing = $errors->getBag($editBag)->any())
            <li class="todo-item {{ $todo->is_completed ? 'completed' : '' }}">
                <form method="POST" action="{{ route('todos.toggle', ['todo' => $todo] + $filter) }}">
                    @csrf
                    @method('PATCH')
                    <button class="toggle" type="submit" aria-label="{{ $todo->is_completed ? '改為未完成' : '標記為完成' }}：{{ $todo->title }}">
                        <span aria-hidden="true">{{ $todo->is_completed ? '☑' : '☐' }}</span>
                    </button>
                </form>
                <div class="content">
                    <strong class="todo-title">{{ $todo->title }}</strong>
                    @if (filled($todo->description))
                        <p class="todo-desc">{{ $todo->description }}</p>
                    @endif
                    @if ($todo->tags->isNotEmpty())
                        <ul class="tags" aria-label="標籤">
                            @foreach ($todo->tags as $tag)
                                <li><a href="{{ route('todos.index', ['tag' => $tag->name]) }}">{{ $tag->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="actions">
                    <button type="button" data-edit aria-expanded="{{ $editing ? 'true' : 'false' }}" aria-controls="edit-{{ $todo->id }}"
                            class="action-icon" aria-label="編輯：{{ $todo->title }}" title="編輯">
                        <img src="{{ asset('icons/pencil.png') }}" alt="" width="20" height="20">
                    </button>
                    <form data-delete method="POST" action="{{ route('todos.destroy', ['todo' => $todo] + $filter) }}">
                        @csrf
                        @method('DELETE')
                        <button class="action-icon delete" type="submit" aria-label="刪除：{{ $todo->title }}" title="刪除">
                            <img src="{{ asset('icons/delete.png') }}" alt="" width="20" height="20">
                        </button>
                    </form>
                </div>

                <form id="edit-{{ $todo->id }}" class="edit-panel" method="POST"
                      action="{{ route('todos.update', ['todo' => $todo] + $filter) }}" @if (! $editing) hidden @endif>
                    @csrf
                    @method('PUT')
                    @include('todos.fields', ['bag' => $editBag, 'item' => $todo])
                    <div class="edit-actions">
                        <button class="primary" type="submit">儲存</button>
                        <button type="button" data-cancel>取消</button>
                    </div>
                </form>
            </li>
        @empty
            <li class="empty">
                @if ($selectedTag !== null)
                    目前沒有「{{ $selectedTag }}」標籤的待辦。<a href="{{ route('todos.index') }}">查看全部</a>
                @else
                    目前沒有待辦，可以從上方新增第一筆。
                @endif
            </li>
        @endforelse
    </ul>
</main>
<dialog id="appearance-dialog" aria-labelledby="appearance-title">
    <form method="dialog">
        <h2 id="appearance-title">外觀設定</h2>
        <div class="size-label">
            <label for="appearance-size">字型大小</label>
            <output id="size-value" for="appearance-size">16 px</output>
        </div>
        <input type="range" id="appearance-size" min="14" max="24" step="1" value="16">
        <div class="color-setting">
            <label for="appearance-color">主題顏色</label>
            <input type="color" id="appearance-color" value="#195693">
        </div>
        <div class="settings-actions">
            <button type="button" data-appearance-reset>恢復預設</button>
            <button type="submit" class="primary">完成</button>
        </div>
    </form>
</dialog>
</div>
<footer class="icon-credit">
    <details>
        <summary>版權聲明</summary>
        <ul>
            <li><a href="https://www.flaticon.com/free-icons/pencil">Pencil icons created by Ilham Fitrotul Hayat - Flaticon</a></li>
            <li><a href="https://www.flaticon.com/free-icons/delete">Delete icons created by Ilham Fitrotul Hayat - Flaticon</a></li>
        </ul>
    </details>
</footer>
</body>
</html>
