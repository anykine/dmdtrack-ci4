<?php
/**
 * @var \App\Libraries\ListState  $state
 * @var list<array<string,mixed>> $rows
 * @var int                       $total
 * @var string                    $pagination
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 text-left">
                <h3 class="panel-title">List Referrers</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('referrer/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Referrer</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'referrerID', 'ID') ?></td>
                <td><?= sort_header($state, 'referrerInstitution', 'Institution') ?></td>
                <td><?= sort_header($state, 'referrerFirstName', 'First Name') ?></td>
                <td><?= sort_header($state, 'referrerLastName', 'Last Name') ?></td>
                <td class="text-right">Actions</td>
            </tr>
            <tr>
                <th></th>
                <?= filter_cell($state, 'referrerInstitution', 'institution') ?>
                <?= filter_cell($state, 'referrerFirstName', 'first name') ?>
                <?= filter_cell($state, 'referrerLastName', 'last name') ?>
                <th><?= filter_buttons($state) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'referrerID') ?>><?= (int) $row['referrerID'] ?></td>
                        <td<?= sorted_attr($state, 'referrerInstitution') ?>><?= esc($row['referrerInstitution']) ?></td>
                        <td<?= sorted_attr($state, 'referrerFirstName') ?>><?= esc($row['referrerFirstName']) ?></td>
                        <td<?= sorted_attr($state, 'referrerLastName') ?>><?= esc($row['referrerLastName']) ?></td>
                        <td><?= row_actions('referrer', $row['referrerID']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="5"><?= lang('Core.core error no_results') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
    <?= form_close() ?>

    <?= view('partials/list_footer', compact('state', 'total', 'pagination')) ?>
</div>

<?= view('partials/delete_modals', [
    'module'   => 'referrer',
    'label'    => 'Referrer',
    'idKey'    => 'referrerID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim($r['referrerFirstName'] . ' ' . $r['referrerLastName'] . ', ' . $r['referrerInstitution'], ' ,'),
]) ?>
