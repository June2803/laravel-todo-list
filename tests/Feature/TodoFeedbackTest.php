<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the same browser session when an action redirects to the list.
        $this->withCookie(config('session.cookie'), session()->getId());
    }

    public function test_new_todo_displays_feedback_once(): void
    {
        $response = $this->post(route('todos.store'), ['title' => '練習新增']);

        $response->assertRedirect(route('todos.index'));
        $this->assertDatabaseHas('todos', ['title' => '練習新增']);
        $this->get(route('todos.index'))->assertOk()->assertSeeText('已新增待辦。');
        $this->get(route('todos.index'))->assertDontSeeText('已新增待辦。');
    }

    public function test_missing_title_does_not_report_success(): void
    {
        $this->from(route('todos.index'))
            ->post(route('todos.store'), ['title' => ''])
            ->assertSessionHasErrors(['title'], null, 'createTodo')
            ->assertSessionMissing('status');

        $this->assertDatabaseCount('todos', 0);
    }

    public function test_edit_and_both_completion_states_return_matching_feedback(): void
    {
        $todo = Todo::create(['title' => '練習編輯']);

        $this->put(route('todos.update', $todo), ['title' => '已修改標題'])
            ->assertSessionHas('status', '已儲存修改。');
        $this->assertSame('已修改標題', $todo->fresh()->title);

        $this->patch(route('todos.toggle', $todo))
            ->assertSessionHas('status', '已標記為完成。');
        $this->assertTrue((bool) $todo->fresh()->is_completed);

        $this->patch(route('todos.toggle', $todo))
            ->assertSessionHas('status', '已標記為未完成。');
        $this->assertFalse((bool) $todo->fresh()->is_completed);
    }

    public function test_deleted_todo_returns_feedback(): void
    {
        $todo = Todo::create(['title' => '練習刪除']);

        $this->delete(route('todos.destroy', $todo))
            ->assertRedirect(route('todos.index'))
            ->assertSessionHas('status', '已刪除待辦。');

        $this->assertModelMissing($todo);
    }

    public function test_rejected_edit_keeps_the_right_editor_open_and_other_fields_intact(): void
    {
        $edited = Todo::create(['title' => '原本的事項', 'description' => '原本說明']);
        $other = Todo::create(['title' => '另一筆事項']);
        $bag = 'editTodo'.$edited->id;

        $this->from(route('todos.index'))
            ->put(route('todos.update', $edited), ['title' => ' ', 'description' => '剛輸入的說明'])
            ->assertSessionHasErrors(['title'], null, $bag)
            ->assertSessionMissing('status');

        $page = $this->get(route('todos.index'))->assertOk();
        $document = new \DOMDocument;
        $document->loadHTML($page->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $html = new \DOMXPath($document);
        $this->assertSame('剛輸入的說明', $html->evaluate("string(//*[@id='$bag-description'])"));
        $this->assertSame('請輸入事項名稱。', $html->evaluate("string(//*[@id='$bag-title-error'])"));
        $this->assertFalse($html->evaluate("boolean(//*[@id='edit-{$edited->id}']/@hidden)"));
        $this->assertTrue($html->evaluate("boolean(//*[@id='edit-{$other->id}']/@hidden)"));
        $this->assertSame('', $html->evaluate("string(//*[@id='createTodo-description'])"));
        $this->assertSame('另一筆事項', $html->evaluate("string(//*[@id='editTodo{$other->id}-title']/@value)"));
        $this->assertSame('原本說明', $edited->fresh()->description);
    }

    public function test_invalid_types_and_long_titles_show_validation_feedback(): void
    {
        $this->from(route('todos.index'))
            ->post(route('todos.store'), ['title' => str_repeat('字', 256), 'description' => ['無效輸入']])
            ->assertSessionHasErrors(['title', 'description'], null, 'createTodo');

        $page = $this->get(route('todos.index'))->assertOk();
        $page->assertSeeText('事項名稱最多 255 個字。')
            ->assertSeeText('說明必須是文字。');
        $document = new \DOMDocument;
        $document->loadHTML($page->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $html = new \DOMXPath($document);
        $this->assertTrue($html->evaluate("boolean(//details[@class='add-disclosure']/@open)"));
    }
}
