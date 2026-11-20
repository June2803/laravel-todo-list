<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_can_be_added_edited_cleared_and_filtered(): void
    {
        $this->post(route('todos.store'), ['title' => '準備簡報', 'tags' => ' 工作， 面試、工作,0 '])
            ->assertRedirect(route('todos.index'));
        $todo = Todo::whereTitle('準備簡報')->firstOrFail();
        $this->assertEqualsCanonicalizing(['工作', '面試', '0'], $todo->tags->pluck('name')->all());
        $other = Todo::create(['title' => '整理房間']);
        $other->tags()->create(['name' => '面試準備']);
        Todo::create(['title' => '未分類']);

        $this->get(route('todos.index', ['tag' => '面試']))->assertOk()
            ->assertSeeText('準備簡報')->assertDontSeeText('整理房間')->assertDontSeeText('未分類');
        $this->get(route('todos.index', ['tag' => '0']))->assertOk()
            ->assertSeeText('準備簡報')->assertDontSeeText('整理房間');

        $this->put(route('todos.update', ['todo' => $todo, 'tag' => '工作']), [
            'title' => '準備簡報', 'tags' => '工作,明天',
        ])->assertRedirect(route('todos.index', ['tag' => '工作']));
        $this->assertEqualsCanonicalizing(['工作', '明天'], $todo->tags()->pluck('name')->all());

        $this->put(route('todos.update', $todo), ['title' => '舊表單也能編輯']);
        $this->assertCount(2, $todo->tags()->get());
        $this->put(route('todos.update', $todo), ['title' => '清除標籤', 'tags' => '']);
        $this->assertCount(0, $todo->tags()->get());
        $this->get(route('todos.index', ['tag' => '工作']))->assertOk()
            ->assertSeeText('目前沒有「工作」標籤的待辦。')->assertSeeText('查看全部');
    }

    public function test_invalid_tag_keeps_original_data_and_deleting_a_todo_removes_only_its_tags(): void
    {
        $todo = Todo::create(['title' => '原始標題']);
        $other = Todo::create(['title' => '另一筆']);
        $todo->tags()->create(['name' => '共用']);
        $other->tags()->create(['name' => '共用']);

        $this->put(route('todos.update', $todo), ['title' => '修改名稱', 'tags' => str_repeat('字', 41)])
            ->assertSessionHasErrors(['tags'], null, 'editTodo'.$todo->id);
        $this->assertSame('原始標題', $todo->fresh()->title);
        $this->assertDatabaseCount('todo_tags', 2);

        $this->delete(route('todos.destroy', ['todo' => $todo, 'tag' => '共用']))
            ->assertRedirect(route('todos.index', ['tag' => '共用']));
        $this->assertDatabaseCount('todo_tags', 1);
        $this->assertDatabaseHas('todo_tags', ['todo_id' => $other->id, 'name' => '共用']);
    }
}
