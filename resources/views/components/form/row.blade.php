@aware(['object', 'showDefaults', 'editable'])

<tr @class(['formster-property-changed' => $changed])>
    <th>
        @if ($editable)
            <label for="{{ $id }}" title="{{ $property->variableName }}">{!! Str::inlineMarkdown($description) ?: $property->variableName !!}</label>
        @else
            <span title="{{ $property->variableName }}">{!! Str::inlineMarkdown($description) ?: $property->variableName !!}</span>
        @endif
    </th>
    <td><x-formster::form.input :$id :$property :$object /></td>
    @if ($showDefaults)
        <td><x-formster::form.input :$id :$property /></td>
    @endif
</tr>
