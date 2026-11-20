<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $filter = $this->tagFilter($request);
        $selectedTag = $filter['tag'] ?? null;
        $query = Todo::with('tags');
        if ($selectedTag !== null) {
            $query->whereHas('tags', fn ($tags) => $tags->where('name', $selectedTag));
        }
        $todos = $query->orderBy('is_completed')->latest()->orderByDesc('id')->get();

        return view('todos.index', compact('todos', 'filter', 'selectedTag'));
    }

    public function store(Request $request)
    {
        $this->saveTodo($request, new Todo, 'createTodo');

        return redirect()->route('todos.index', $this->tagFilter($request))
            ->with('status', '已新增待辦。')
            ->with('focus_new_todo', true);
    }

    public function update(Request $request, Todo $todo)
    {
        $this->saveTodo($request, $todo, 'editTodo'.$todo->id);

        return redirect()->route('todos.index', $this->tagFilter($request))->with('status', '已儲存修改。');
    }

    public function toggle(Request $request, Todo $todo)
    {
        $todo->update(['is_completed' => ! $todo->is_completed]);
        $status = $todo->is_completed ? '已標記為完成。' : '已標記為未完成。';

        return redirect()->route('todos.index', $this->tagFilter($request))->with('status', $status);
    }

    public function destroy(Request $request, Todo $todo)
    {
        $todo->delete();

        return redirect()->route('todos.index', $this->tagFilter($request))->with('status', '已刪除待辦。');
    }

    private function tagFilter(Request $request): array
    {
        $tag = $request->query('tag');

        return is_string($tag) && $tag !== '' && mb_strlen($tag) <= 40 ? ['tag' => $tag] : [];
    }

    private function saveTodo(Request $request, Todo $todo, string $bag): void
    {
        $data = $request->validateWithBag($bag, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required' => '請輸入事項名稱。',
            'title.string' => '事項名稱必須是文字。',
            'title.max' => '事項名稱最多 255 個字。',
            'description.string' => '說明必須是文字。',
            'tags.string' => '請輸入標籤文字。',
            'tags.max' => '標籤欄位最多 1000 個字。',
        ]);

        $names = null;
        if (array_key_exists('tags', $data)) {
            $names = collect(preg_split('/[,，、]/u', $data['tags'] ?? ''))
                ->map(fn ($name) => Str::trim($name))
                ->reject(fn ($name) => $name === '')
                ->uniqueStrict()
                ->values();
            if ($names->contains(fn ($name) => mb_strlen($name) > 40)) {
                throw ValidationException::withMessages(['tags' => '每個標籤最多 40 個字。'])->errorBag($bag);
            }
        }
        unset($data['tags']);

        DB::transaction(function () use ($todo, $data, $names) {
            if ($todo->exists) {
                $todo = Todo::whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            }
            $todo->fill($data)->save();
            if ($names !== null) {
                $todo->tags()->delete();
                $todo->tags()->createMany($names->map(fn ($name) => ['name' => $name]));
            }
        }, 3);
    }
}
