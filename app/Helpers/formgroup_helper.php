<?php

/**
 * Bootstrap 3 form-group helpers for the module add/edit forms.
 *
 * Each CI3 form repeated ~6 lines per field (label, required star, input, set_value,
 * form_error class). These keep the same markup in one call. Values come from what was
 * just posted (after a failed save), else from the stored row.
 *
 * Options for all of them:
 *   'width'    Bootstrap column class (default col-sm-4)
 *   'required' show the red star (only use it when the controller validates the field)
 *   'column'   row key when it differs from the field name
 *   'default'  value for a new record
 */

if (! function_exists('fg_value')) {
    function fg_value(string $field, ?array $row, array $opt = []): string
    {
        $column  = $opt['column'] ?? $field;
        $default = $row !== null && array_key_exists($column, $row) ? $row[$column] : ($row === null ? ($opt['default'] ?? '') : '');

        return (string) set_value($field, (string) $default, false);
    }
}

if (! function_exists('fg_open')) {
    function fg_open(string $field, string $label, array $opt = []): string
    {
        $error = validation_show_error($field) !== '' ? ' has-error' : '';
        $width = $opt['width'] ?? 'col-sm-4';

        return '<div class="form-group ' . $width . $error . '">'
            . form_label(esc($label), $field, ['class' => 'control-label'])
            . (! empty($opt['required']) ? ' <span class="required">*</span>' : '');
    }
}

if (! function_exists('fg_text')) {
    /**
     * Text box. Extra option: 'type' (e.g. email).
     */
    function fg_text(string $field, string $label, ?array $row, array $opt = []): string
    {
        return fg_open($field, $label, $opt)
            . form_input([
                'name'  => $field,
                'id'    => $field,
                'value' => fg_value($field, $row, $opt),
                'class' => 'form-control',
                'type'  => $opt['type'] ?? 'text',
            ])
            . '</div>';
    }
}

if (! function_exists('fg_select')) {
    /**
     * Dropdown.
     *
     * @param array<int|string, string> $options value => label
     */
    function fg_select(string $field, string $label, array $options, ?array $row, array $opt = []): string
    {
        return fg_open($field, $label, $opt)
            . form_dropdown($field, array_map('esc', $options), fg_value($field, $row, $opt), ['id' => $field, 'class' => 'form-control'])
            . '</div>';
    }
}

if (! function_exists('fg_radios')) {
    /**
     * Radio buttons (CI3 used these for the yes/no and PE/SE fields).
     *
     * @param array<int|string, string> $choices value => label, default Yes(1)/No(0)
     */
    function fg_radios(string $field, string $label, ?array $row, array $opt = [], array $choices = ['1' => 'Yes', '0' => 'No']): string
    {
        $current = fg_value($field, $row, $opt);
        $html    = fg_open($field, $label, $opt);
        $i       = 0;

        foreach ($choices as $value => $text) {
            $value = (string) $value;
            $html .= '<div class="radio"><label>'
                . form_radio(['name' => $field, 'id' => $field . '-' . (++$i), 'value' => $value, 'checked' => $current !== '' && $current === $value])
                . ' ' . esc($text) . '</label></div>';
        }

        return $html . '</div>';
    }
}

if (! function_exists('fg_select2')) {
    /**
     * Empty <select> that dmdtrack.js turns into a select2 search box (the id picks the
     * search URL, e.g. individualSearch -> individual/GetIndividualSearch).
     */
    function fg_select2(string $id, string $label, array $opt = []): string
    {
        return fg_open($id, $label, $opt + ['width' => 'col-sm-6'])
            . '<select class="js-example-basic-single form-control" name="' . esc($id, 'attr') . '" id="' . esc($id, 'attr') . '" style="width: 100%"></select>'
            . '</div>';
    }
}

if (! function_exists('fg_preselect')) {
    /**
     * The hidden pair dmdtrack.js reads to show the current choice in a select2 box,
     * e.g. fg_preselect('dataIndividualID', 12, 'dataIndividualName', 'Jane Doe').
     */
    function fg_preselect(string $idField, int|string|null $id, string $nameField, ?string $name): string
    {
        return '<input type="hidden" id="' . esc($idField, 'attr') . '" value="' . esc((string) $id, 'attr') . '">'
            . '<input type="hidden" id="' . esc($nameField, 'attr') . '" value="' . esc((string) $name, 'attr') . '">';
    }
}

if (! function_exists('fg_buttons')) {
    /**
     * Cancel + Save buttons at the bottom of a form.
     */
    function fg_buttons(string $cancelUrl, ?string $saveLabel = null): string
    {
        return '<div class="row pull-right">'
            . '<a class="btn btn-link" href="' . esc($cancelUrl, 'attr') . '">' . lang('Core.core button cancel') . '</a> '
            . '<button type="submit" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> ' . esc($saveLabel ?? lang('Core.core button save')) . '</button>'
            . '</div>';
    }
}
