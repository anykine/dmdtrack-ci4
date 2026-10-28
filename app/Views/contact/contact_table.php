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
                <h3 class="panel-title">List Contacts</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('contact/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Contact</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'contactID', 'contact ID') ?></td>
                <td><?= sort_header($state, 'contactLastName', 'Last Name') ?></td>
                <td><?= sort_header($state, 'contactFirstName', 'First Name') ?></td>
                <td><?= sort_header($state, 'contactPatientLastName', 'Patient Last Name') ?></td>
                <td><?= sort_header($state, 'contactPatientFirstName', 'Patient First Name') ?></td>
                <td class="text-right">Actions</td>
            </tr>
            <tr>
                <th></th>
                <?= filter_cell($state, 'contactLastName', 'last name') ?>
                <?= filter_cell($state, 'contactFirstName', 'first name') ?>
                <?= filter_cell($state, 'contactPatientLastName', 'pat last name') ?>
                <?= filter_cell($state, 'contactPatientFirstName', 'pat first name') ?>
                <th><?= filter_buttons($state) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'contactID') ?>><?= (int) $row['contactID'] ?></td>
                        <td<?= sorted_attr($state, 'contactLastName') ?>><?= esc($row['contactLastName']) ?></td>
                        <td<?= sorted_attr($state, 'contactFirstName') ?>><?= esc($row['contactFirstName']) ?></td>
                        <td<?= sorted_attr($state, 'contactPatientLastName') ?>><?= esc($row['contactPatientLastName']) ?></td>
                        <td<?= sorted_attr($state, 'contactPatientFirstName') ?>><?= esc($row['contactPatientFirstName']) ?></td>
                        <td><?= row_actions('contact', $row['contactID']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="6"><?= lang('Core.core error no_results') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
    <?= form_close() ?>

    <?= view('partials/list_footer', compact('state', 'total', 'pagination')) ?>
</div>

<?= view('partials/delete_modals', [
    'module'   => 'contact',
    'label'    => 'Contact',
    'idKey'    => 'contactID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim($r['contactFirstName'] . ' ' . $r['contactLastName']),
]) ?>
