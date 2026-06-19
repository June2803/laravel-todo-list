<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Todo List</title>
    <link rel="stylesheet" href="{{ asset('css/todo.css') }}">

</head>

<body>
    <h1>Todo List</h1>

    <form class="add-form" method="POST" action="{{ route('todos.store') }}">
        @csrf
        <input type="text" name="title" placeholder="待辦事項" required>
        <input type="text" name="description" placeholder="說明">
        <button type="submit">新增</button>
    </form>

    @foreach ($todos as $todo)
        <div class="todo-item {{ $todo->is_completed ? 'completed' : '' }}">

            <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                @csrf
                @method('PATCH')
                <button class="icon-button" type="submit">
                    {{ $todo->is_completed ? '☑' : '☐' }}
                </button>
            </form>

            <div>
                <div class="todo-title">{{ $todo->title }}</div>
                <div class="todo-desc">{{ $todo->description }}</div>
            </div>

            <input type="checkbox" id="edit-{{ $todo->id }}" class="edit-toggle">

            <label for="edit-{{ $todo->id }}" class="icon-button edit-icon">
                <img src="{{ asset('icons/pencil.png') }}" class="todo-icon" alt="edit">
            </label>

            <form method="POST" action="{{ route('todos.destroy', $todo) }}">
                @csrf
                @method('DELETE')
                <button class="icon-button" type="submit">
                    <img src="{{ asset('icons/delete.png') }}" class="todo-icon" alt="delete">
                </button>
            </form>

            <div class="edit-panel">
                <form method="POST" action="{{ route('todos.update', $todo) }}">
                    @csrf
                    @method('PUT')

                    <input type="text" name="title" value="{{ $todo->title }}" required>
                    <input type="text" name="description" value="{{ $todo->description }}">

                    <button type="submit">儲存</button>
                </form>
            </div>
        </div>
    @endforeach

    <footer class="icon-credit">
        <details>
            <summary>聲明</summary>

            <a href="https://www.flaticon.com/free-icons/pencil" title="pencil icons">
                Pencil icons created by Ilham Fitrotul Hayat - Flaticon
            </a>
            <br>
            <a href="https://www.flaticon.com/free-icons/delete" title="delete icons">
                Delete icons created by Ilham Fitrotul Hayat - Flaticon
            </a>
    </footer>

</body>
</html>