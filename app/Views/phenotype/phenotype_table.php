<?php
/**
 * @var string     $this_url
 * @var list<array> $phenotypes
 * @var int        $total
 * @var array      $filters
 * @var array      $query   current sort/dir/limit/filters (no page)
 * @var string     $pagination
 * @var int        $limit
 * @var string     $sort
 * @var string     $dir
 */

// Sortable header: clicking the current column flips the direction, any other column starts ascending
$sortHeader = static function (string $column, string $label) use ($this_url, $query, $sort, $dir): string {
    $newDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
    $url    = $this_url . '?' . http_build_query(['sort' => $column, 'dir' => $newDir] + $query);
    $arrow  = $sort === $column ? ' <span class="glyphicon glyphicon-arrow-' . ($dir === 'asc' ? 'up' : 'down') . '"></span>' : '';

    return '<a href="' . esc($url, 'attr') . '">' . esc($label) . '</a>' . $arrow;
};
$sorted = static fn (string $column): string => $sort === $column ? ' class="sorted"' : '';
$filterCell = static function (string $field, string $placeholder) use ($filters): string {
    $class = isset($filters[$field]) ? ' class="has-success"' : '';

    return "<th{$class}>" . form_input([
        'name'        => $field,
        'id'          => $field,
        'class'       => 'form-control input-sm',
        'placeholder' => $placeholder,
        'value'       => $filters[$field] ?? '',
    ]) . '</th>';
};
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 text-left">
                <h3 class="panel-title">List Phenotypes</h3>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success tooltips" href="<?= site_url('phenotype/add') ?>" title="Add New Phenotype" data-toggle="tooltip"><span class="glyphicon glyphicon-plus-sign"></span> Add New Phenotype</a>
            </div>
        </div>
    </div>

    <?= form_open($this_url . '?' . http_build_query($query), ['role' => 'form', 'id' => 'filters']) ?>
    <table class="table table-striped table-hover-warning">
        <thead>
            <tr>
                <td><?= $sortHeader('phenotypeID', 'ID') ?></td>
                <td><?= $sortHeader('mildSevere', 'mild/severe') ?></td>
                <td><?= $sortHeader('WCage', 'WCage') ?></td>
                <td><?= $sortHeader('DOB', 'DOB') ?></td>
                <td><?= $sortHeader('reportedDMDMutation', 'reported DMD Mutation') ?></td>
                <td><?= $sortHeader('DMDorBMD', 'DMDorBMD') ?></td>
                <td class="pull-right">Actions</td>
            </tr>

            <?php // search filters ?>
            <tr>
                <th></th>
                <?= $filterCell('mildSevere', '') ?>
                <th></th>
                <th></th>
                <?= $filterCell('reportedDMDMutation', 'mutation') ?>
                <?= $filterCell('DMDorBMD', 'DMDorBMD') ?>
                <th>
                    <div class="text-right">
                        <a href="<?= $this_url ?>" class="btn btn-danger btn-sm tooltips" data-toggle="tooltip" title="Reset"><span class="glyphicon glyphicon-refresh"></span> <?= lang('Core.core button reset') ?></a>
                        <button type="submit" name="submit" value="filter" class="btn btn-success btn-sm tooltips" data-toggle="tooltip" title="<?= esc(lang('Admin.admin tooltip filter'), 'attr') ?>"><span class="glyphicon glyphicon-filter"></span> <?= lang('Core.core button filter') ?></button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total && $phenotypes) : ?>
                <?php foreach ($phenotypes as $phenotype) : ?>
                    <?php $id = (int) $phenotype['phenotypeID'] ?>
                    <tr>
                        <td<?= $sorted('phenotypeID') ?>>
                            <?= $id ?>&nbsp;
                            <a href="<?= site_url('indivDetail/view/' . (int) $phenotype['individualID']) ?>">(Detail)</a>
                        </td>
                        <td<?= $sorted('mildSevere') ?>><?= esc($phenotype['mildSevere']) ?></td>
                        <td<?= $sorted('WCage') ?>><?= esc($phenotype['WCage']) ?></td>
                        <td<?= $sorted('DOB') ?>><?= esc($phenotype['DOB']) ?></td>
                        <td<?= $sorted('reportedDMDMutation') ?>><?= esc($phenotype['reportedDMDMutation']) ?></td>
                        <td<?= $sorted('DMDorBMD') ?>><?= esc($phenotype['DMDorBMD']) ?></td>
                        <td>
                            <div class="text-right">
                                <div class="btn-group">
                                    <a href="#modal-<?= $id ?>" data-toggle="modal" class="btn btn-danger btn-xs" title="Delete"><span class="glyphicon glyphicon-trash"></span></a>
                                    <a href="<?= site_url("phenotype/edit/{$id}") ?>" class="btn btn-warning btn-xs" title="Edit"><span class="glyphicon glyphicon-pencil"></span></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr>
                    <td colspan="7"><?= lang('Core.core error no_results') ?></td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
    <?= form_close() ?>

    <div class="panel-footer">
        <div class="row">
            <div class="col-md-2 text-left">
                <label><?= sprintf(lang('Admin.admin label rows'), $total) ?></label>
            </div>
            <div class="col-md-2 text-left">
                <?php if ($total > 10) : ?>
                    <select id="limit" class="form-control">
                        <?php foreach ([10, 25, 50, 75, 100] as $option) : ?>
                            <option value="<?= $option ?>"<?= $limit === $option ? ' selected' : '' ?>><?= $option ?> <?= lang('Admin.admin input items_per_page') ?></option>
                        <?php endforeach ?>
                    </select>
                <?php endif ?>
            </div>
            <div class="col-md-8">
                <?= $pagination ?>
            </div>
        </div>
    </div>
</div>

<?php // Delete confirmations (CI3 linked to these modals but never defined them) ?>
<?php foreach ($phenotypes ?? [] as $phenotype) : ?>
    <?php $id = (int) $phenotype['phenotypeID'] ?>
    <div class="modal fade" id="modal-<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-label-<?= $id ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?= form_open("phenotype/delete/{$id}") ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal-label-<?= $id ?>">Delete phenotype <?= $id ?>?</h4>
                    </div>
                    <div class="modal-body">
                        This permanently deletes phenotype <?= $id ?>
                        (<?= esc(trim($phenotype['individualFirstName'] . ' ' . $phenotype['individualLastName'])) ?>).
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('Core.core button cancel') ?></button>
                        <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Delete</button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
<?php endforeach ?>

<script>
// Items-per-page dropdown: reload the list with the new limit, back on page 1
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('limit');
    if (!select) return;
    select.addEventListener('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('limit', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });
});
</script>
