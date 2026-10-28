<?php

/**
 * View helpers for the module list pages (replace the repeated sort-link/filter markup in
 * the CI3 *_table.php views).
 */

use App\Libraries\ListState;

if (! function_exists('sort_header')) {
    /**
     * Sortable column header link with the up/down arrow on the current column.
     */
    function sort_header(ListState $state, string $column, string $label): string
    {
        $arrow = $state->sort === $column
            ? ' <span class="glyphicon glyphicon-arrow-' . ($state->dir === 'asc' ? 'up' : 'down') . '"></span>'
            : '';

        return '<a href="' . esc($state->sortUrl($column), 'attr') . '">' . esc($label) . '</a>' . $arrow;
    }
}

if (! function_exists('sorted_attr')) {
    /**
     * ' class="sorted"' on cells of the sorted column.
     */
    function sorted_attr(ListState $state, string $column): string
    {
        return $state->sort === $column ? ' class="sorted"' : '';
    }
}

if (! function_exists('filter_cell')) {
    /**
     * <th> with a filter text box (green when the filter is in use).
     */
    function filter_cell(ListState $state, string $field, string $placeholder = ''): string
    {
        $class = isset($state->filters[$field]) ? ' class="has-success"' : '';

        return "<th{$class}>" . form_input([
            'name'        => $field,
            'id'          => 'filter-' . $field,
            'class'       => 'form-control input-sm',
            'placeholder' => $placeholder,
            'value'       => $state->filters[$field] ?? '',
        ]) . '</th>';
    }
}

if (! function_exists('filter_buttons')) {
    /**
     * Reset + Filter buttons for the last filter cell.
     */
    function filter_buttons(ListState $state): string
    {
        return '<div class="text-right">'
            . '<a href="' . esc($state->baseUrl, 'attr') . '" class="btn btn-danger btn-sm" title="Reset"><span class="glyphicon glyphicon-refresh"></span> ' . lang('Core.core button reset') . '</a> '
            . '<button type="submit" name="submit" value="filter" class="btn btn-success btn-sm" title="' . esc(lang('Admin.admin tooltip filter'), 'attr') . '"><span class="glyphicon glyphicon-filter"></span> ' . lang('Core.core button filter') . '</button>'
            . '</div>';
    }
}

if (! function_exists('row_actions')) {
    /**
     * Delete (opens the confirm dialog) and Edit buttons for a list row.
     */
    function row_actions(string $module, int|string $id): string
    {
        $id = (int) $id;

        return '<div class="text-right"><div class="btn-group">'
            . '<a href="#delete-' . $id . '" data-toggle="modal" class="btn btn-danger btn-xs" title="Delete"><span class="glyphicon glyphicon-trash"></span></a>'
            . '<a href="' . site_url("{$module}/edit/{$id}") . '" class="btn btn-warning btn-xs" title="Edit"><span class="glyphicon glyphicon-pencil"></span></a>'
            . '</div></div>';
    }
}
