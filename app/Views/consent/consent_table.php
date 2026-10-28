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
                <h3 class="panel-title">List Consents</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="<?= site_url('consent/add') ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add New Consent</a>
            </div>
        </div>
    </div>

    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= sort_header($state, 'consentID', 'ID') ?></td>
                <td>Individual</td>
                <td><?= sort_header($state, 'consentTypeID', 'Type') ?></td>
                <td><?= sort_header($state, 'consentSent', 'consentSent') ?></td>
                <td><?= sort_header($state, 'consentReceived', 'consentReceived') ?></td>
                <td class="text-right">Actions</td>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td<?= sorted_attr($state, 'consentID') ?>><?= (int) $row['consentID'] ?></td>
                        <td><a href="<?= site_url('indivDetail/view/' . (int) $row['individualID']) ?>"><?= (int) $row['individualID'] ?></a> <?= esc(trim($row['individualFirstName'] . ' ' . $row['individualLastName'])) ?></td>
                        <td<?= sorted_attr($state, 'consentTypeID') ?>><?= esc($row['cConsentTypeID']) ?> <?= esc($row['consentType'] ?? '') ?></td>
                        <td<?= sorted_attr($state, 'consentSent') ?>><?= esc($row['consentSent']) ?></td>
                        <td<?= sorted_attr($state, 'consentReceived') ?>><?= esc($row['consentReceived']) ?></td>
                        <td><?= row_actions('consent', $row['consentID']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="6"><?= lang('Core.core error no_results') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>

    <?= view('partials/list_footer', compact('state', 'total', 'pagination')) ?>
</div>

<?= view('partials/delete_modals', [
    'module'   => 'consent',
    'label'    => 'Consent',
    'idKey'    => 'consentID',
    'rows'     => $rows,
    'describe' => static fn (array $r): string => trim($r['individualFirstName'] . ' ' . $r['individualLastName'] . ', ' . ($r['consentType'] ?? '')),
]) ?>
