<?php
/**
 * @var \App\Libraries\ListState  $state
 * @var list<array<string,mixed>> $rows
 * @var int                       $total
 * @var string                    $pagination
 * @var string                    $study  ?cmd= from the Study menu, or ''
 */
$columns = [
    'cDMDID'              => 'CDMD ID2',
    'cDMDID1'             => 'CDMD ID1',
    'celllineID'          => 'cell line',
    'needleBxID'          => 'needle Bx',
    'prevID'              => 'prev ID',
    'cDMDTempID'          => 'temp ID',
    'individualFirstName' => 'First name',
    'individualLastName'  => 'Last name',
];
$filters = [
    'cDMDID'              => 'CDMD ID2',
    'cDMDID1'             => 'CDMD ID1',
    'needleBxID'          => 'needleBxID',
    'prevID'              => 'prevID',
    'cDMDTempID'          => 'cDMDTempID',
    'individualFirstName' => 'first name',
    'individualLastName'  => 'last name',
];
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 text-left">
                <h3 class="panel-title">List individuals <span class="text-danger"><?= esc($study) ?></span></h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('individual/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Indiv</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'individualID', 'ID') ?></td>
                <?php foreach ($columns as $column => $label) : ?>
                    <td><?= sort_header($state, $column, $label) ?></td>
                <?php endforeach ?>
                <td class="text-right">Actions</td>
            </tr>
            <tr>
                <th></th>
                <?php foreach (array_keys($columns) as $column) : ?>
                    <?= isset($filters[$column]) ? filter_cell($state, $column, $filters[$column]) : '<th></th>' ?>
                <?php endforeach ?>
                <th><?= filter_buttons($state) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'individualID') ?>>
                            <a href="<?= site_url('indivDetail/view/' . (int) $row['individualID']) ?>"><?= (int) $row['individualID'] ?></a>
                        </td>
                        <?php foreach (array_keys($columns) as $column) : ?>
                            <td<?= sorted_attr($state, $column) ?>><?= esc($row[$column]) ?></td>
                        <?php endforeach ?>
                        <td><?= row_actions('individual', $row['individualID']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="10"><?= lang('Core.core error no_results') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
    <?= form_close() ?>

    <?= view('partials/list_footer', compact('state', 'total', 'pagination')) ?>
</div>

<?= view('partials/delete_modals', [
    'module'   => 'individual',
    'label'    => 'Individual',
    'idKey'    => 'individualID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim($r['individualFirstName'] . ' ' . $r['individualLastName']),
]) ?>
