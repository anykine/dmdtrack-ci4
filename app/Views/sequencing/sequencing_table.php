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
                <h3 class="panel-title">List Sequencing</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('sequencing/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Sequencing</a>
            </div>
        </div>
    </div>

    <?= form_open($state->url(), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'sequencingID', 'ID') ?></td>
                <td><?= sort_header($state, 'sequencingTypeID', 'Type') ?></td>
                <td><?= sort_header($state, 'cDMDID', 'CDMD ID') ?></td>
                <td><?= sort_header($state, 'prevID', 'prev ID') ?></td>
                <td><?= sort_header($state, 'cDMDTempID', 'temp ID') ?></td>
                <td><?= sort_header($state, 'sequencingQueued', "Q'd") ?></td>
                <td><?= sort_header($state, 'sequencingSubmitted', 'Submitted') ?></td>
                <td><?= sort_header($state, 'sequencingCompleted', 'Completed') ?></td>
                <td><?= sort_header($state, 'sequencingCenterBatchIdentifier', 'Batch') ?></td>
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
                        <td<?= sorted_attr($state, 'sequencingID') ?>>
                            <?= (int) $row['sequencingID'] ?>
                            <a href="<?= site_url('indivDetail/view/' . (int) $row['individualID']) ?>">(Detail)</a>
                        </td>
                        <td<?= sorted_attr($state, 'sequencingTypeID') ?>><?= esc($row['sequencingType']) ?></td>
                        <td<?= sorted_attr($state, 'cDMDID') ?>><?= esc($row['cDMDID']) ?></td>
                        <td<?= sorted_attr($state, 'prevID') ?>><?= esc($row['prevID']) ?></td>
                        <td<?= sorted_attr($state, 'cDMDTempID') ?>><?= esc($row['cDMDTempID']) ?></td>
                        <td<?= sorted_attr($state, 'sequencingQueued') ?>><?= esc($row['sequencingQueued']) ?></td>
                        <td<?= sorted_attr($state, 'sequencingSubmitted') ?>><?= esc($row['sequencingSubmitted']) ?></td>
                        <td<?= sorted_attr($state, 'sequencingCompleted') ?>><?= esc($row['sequencingCompleted']) ?></td>
                        <td<?= sorted_attr($state, 'sequencingCenterBatchIdentifier') ?>><?= esc($row['sequencingBatchIdentifier']) ?></td>
                        <td><?= row_actions('sequencing', $row['sequencingID']) ?></td>
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
    'module'   => 'sequencing',
    'label'    => 'Sequencing',
    'idKey'    => 'sequencingID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim($r['sequencingType'] . ', individual ' . $r['individualID'], ' ,'),
]) ?>
