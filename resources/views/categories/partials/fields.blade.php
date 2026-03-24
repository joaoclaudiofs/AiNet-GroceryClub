@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
@endphp

<div class="flex flex-wrap gap-8">
    <div class="grow mt-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
            <flux:input name="name"
                        label="Name"
                        :value="old('name', $category->name)"
                        :disabled="$readonly" />
        </div>

        <div>
            <flux:textarea name="description"
                           label="Description"
                           :disabled="$readonly"
                           :resize="$readonly ? 'none' : 'vertical'"
                           rows="auto">{{ old('description', $category->description) }}</flux:textarea>
        </div>
    </div>
</div>
