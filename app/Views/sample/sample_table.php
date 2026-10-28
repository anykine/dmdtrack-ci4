<?php
/**
 * @var \App\Libraries\ListState  $state
 * @var list<array<string,mixed>> $rows
 * @var int                       $total
 * @var string                    $pagination
 */
$columns = [
    'sampleTypeID'    => 'Type',
    'cDMDID'          => 'CDMD ID',
    'prevID'          => 'prev ID',
    'cDMDTempID'      => 'temp ID',
    'sampleReceived'  => 'Recv',
    'extracted'       => 'Extract',
    'sampleQueued'    => "Q'd",
    'sampleSequenced' => "Seq'd",
];
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 text-left">
                <h3 class="panel-title">List Samples</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('sample/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Sample</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'sampleID', 'ID') ?></td>
                <?php foreach ($columns as $column => $label) : ?>
                    <td><?= sort_header($state, $column, $label) ?></td>
                <?php endforeach ?>
                <td class="text-right">Actions</td>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <?= filter_cell($state, 'cDMDID', 'CDMD ID') ?>
                <?= filter_cell($state, 'prevID', 'prevID') ?>
                <?= filter_cell($state, 'cDMDTempID', 'temp ID') ?>
                <th colspan="4"></th>
                <th><?= filter_buttons($state) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'sampleID') ?>>
                            <?= (int) $row['sampleID'] ?>
                            <a href="<?= site_url('indivDetail/view/' . (int) $row['individualID']) ?>">(Detail)</a>
                        </td>
                        <td<?= sorted_attr($state, 'sampleTypeID') ?>><?= esc($row['sampleType'] ?? $row['sSampleTypeID']) ?></td>
                        <?php foreach (array_slice(array_keys($columns), 1) as $column) : ?>
                            <td<?= sorted_attr($state, $column) ?>><?= esc($row[$column]) ?></td>
                        <?php endforeach ?>
                        <td><?= row_actions('sample', $row['sampleID']) ?></td>
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
    'module'   => 'sample',
    'label'    => 'Sample',
    'idKey'    => 'sampleID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim(($r['sampleType'] ?? '') . ', individual ' . $r['individualID'], ' ,'),
]) ?>
