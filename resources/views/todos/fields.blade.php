@php
    $formErrors = $errors->getBag($bag);
    $title = $formErrors->any() ? old('title') : $item?->title;
    $description = $formErrors->any() ? old('description') : $item?->description;
    $savedTags = $item?->tags->pluck('name')->implode('，') ?? '';
    $tags = $formErrors->any() ? old('tags') : $savedTags;
@endphp
<div class="field">
    <label for="{{ $bag }}-title">事項</label>
    <input id="{{ $bag }}-title" name="title" type="text" required maxlength="255"
           value="{{ is_string($title) ? $title : '' }}"
           @if ($item) data-saved="{{ $item->title }}" @endif
           @if ($formErrors->has('title')) aria-invalid="true" aria-describedby="{{ $bag }}-title-error" @endif>
    @if ($formErrors->has('title'))
        <span class="error" id="{{ $bag }}-title-error">{{ $formErrors->first('title') }}</span>
    @endif
</div>
<div class="field">
    <label for="{{ $bag }}-description">說明（選填）</label>
    <textarea id="{{ $bag }}-description" name="description" rows="2"
              @if ($item) data-saved="{{ $item->description }}" @endif
              @if ($formErrors->has('description')) aria-invalid="true" aria-describedby="{{ $bag }}-description-error" @endif>{{ is_string($description) ? $description : '' }}</textarea>
    @if ($formErrors->has('description'))
        <span class="error" id="{{ $bag }}-description-error">{{ $formErrors->first('description') }}</span>
    @endif
</div>
<div class="field">
    <label for="{{ $bag }}-tags">標籤（選填）</label>
    <input id="{{ $bag }}-tags" name="tags" type="text" maxlength="1000" placeholder="多個標籤請用逗號（,）分隔"
           value="{{ is_string($tags) ? $tags : '' }}"
           @if ($item) data-saved="{{ $savedTags }}" @endif
           @if ($formErrors->has('tags')) aria-invalid="true" aria-describedby="{{ $bag }}-tags-error" @endif>
    @if ($formErrors->has('tags'))
        <span class="error" id="{{ $bag }}-tags-error">{{ $formErrors->first('tags') }}</span>
    @endif
</div>
