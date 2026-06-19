<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laravel Todo List</title>

    <style>
        body {
            font-family: "Microsoft JhengHei", "微軟正黑體", sans-serif;
            font-size: 20pt;
            padding: 30px;
        }

        input, button {
            font-family: inherit;
            font-size: 20pt;
        }

        .todo-item {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            padding: 20px 0;
        }

        .todo-title {
            font-size: 26pt;
            color: #000;
            font-weight: bold;
        }

        .todo-desc {
            font-size: 20pt;
            color: #777;
            margin-top: 8px;
        }

        .icon-button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 24pt;
        }

        .completed .todo-title {
            text-decoration: line-through;
            color: #777;
        }

        .add-form {
            margin-bottom: 30px;
        }

        .edit-toggle {
            display: none;
        }

        .edit-panel {
            display: none;
            grid-column: 2 / 5;
            margin-top: 15px;
        }

        .edit-toggle:checked ~ .edit-panel {
            display: block;
        }

        .edit-icon {
            cursor: pointer;
        }
    </style>
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

            <label for="edit-{{ $todo->id }}" class="icon-button edit-icon">✎</label>

            <form method="POST" action="{{ route('todos.destroy', $todo) }}">
                @csrf
                @method('DELETE')
                <button class="icon-button" type="submit">🗑</button>
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
</body>
</html>