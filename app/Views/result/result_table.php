<?php
/**
 * @var \App\Libraries\ListState  $state
 * @var list<array<string,mixed>> $rows
 * @var int                       $total
 * @var string                    $pagination
 */
$columns = [
    'cDMDID'       => 'CDMD ID2',
    'cDMDID1'      => 'CDMD ID1',
    'prevID'       => 'prev ID',
    'cDMDTempID'   => 'temp ID',
    'DMDmutation'  => 'DMD mutation',
    'testFindings' => 'test findings',
];
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 text-left">
                <h3 class="panel-title">List Results</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('result/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Result</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'resultsID', 'ID') ?></td>
                <td><?= sort_header($state, 'testTypeID', 'test type') ?></td>
                <?php foreach ($columns as $column => $label) : ?>
                    <td><?= sort_header($state, $column, $label) ?></td>
                <?php endforeach ?>
                <td class="text-right">Actions</td>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <?= filter_cell($state, 'cDMDID', 'CDMD ID2') ?>
                <?= filter_cell($state, 'cDMDID1', 'CDMD ID1') ?>
                <?= filter_cell($state, 'prevID', 'prevID') ?>
                <?= filter_cell($state, 'cDMDTempID', 'temp ID') ?>
                <th colspan="2"></th>
                <th><?= filter_buttons($state) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'resultsID') ?>>
                            <?= (int) $row['resultsID'] ?>
                            <a href="<?= site_url('indivDetail/view/' . (int) $row['individualID']) ?>">(Detail)</a>
                        </td>
                        <td<?= sorted_attr($state, 'testTypeID') ?>><?= esc($row['testType'] ?? $row['testTypeID']) ?></td>
                        <?php foreach (array_keys($columns) as $column) : ?>
                            <td<?= sorted_attr($state, $column) ?>><?= esc($row[$column]) ?></td>
                        <?php endforeach ?>
                        <td><?= row_actions('result', $row['resultsID']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="9"><?= lang('Core.core error no_results') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
    <?= form_close() ?>

    <?= view('partials/list_footer', compact('state', 'total', 'pagination')) ?>
</div>

<?= view('partials/delete_modals', [
    'module'   => 'result',
    'label'    => 'Result',
    'idKey'    => 'resultsID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => 'sample ' . $r['sampleID'] . ', individual ' . $r['individualID'],
]) ?>
