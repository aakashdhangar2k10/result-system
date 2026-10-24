<select name="{{ $name }}" class="form-select form-select-sm">
    @foreach (\App\Models\Result\ResultTemplate::ALIGN as $value => $label)
        <option value="{{ $value }}" @selected(old($name, $template->{$name}) === $value)>{{ $label }}</option>
    @endforeach
</select>