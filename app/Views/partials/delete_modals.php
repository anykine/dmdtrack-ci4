<?php
/**
 * Delete confirmation dialogs for a list (opened by row_actions()' trash button).
 * CI3 linked to these dialogs but never defined them, and deleted with a GET link.
 *
 * @var string                    $module  e.g. 'consent'
 * @var string                    $label   e.g. 'Consent'
 * @var string                    $idKey   row key holding the id
 * @var list<array<string,mixed>> $rows
 * @var callable|null             $describe row => extra text shown in the dialog
 */
$describe ??= null;
?>
<?php foreach ($rows as $row) : ?>
    <?php $id = (int) $row[$idKey] ?>
    <div class="modal fade" id="delete-<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="delete-label-<?= $id ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?= form_open("{$module}/delete/{$id}") ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="delete-label-<?= $id ?>">Delete <?= esc(strtolower($label)) ?> <?= $id ?>?</h4>
                    </div>
                    <div class="modal-body">
                        This permanently deletes <?= esc(strtolower($label)) ?> <?= $id ?><?= $describe ? ' (' . esc($describe($row)) . ')' : '' ?>.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('Core.core button cancel') ?></button>
                        <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> <?= lang('Admin.admin button delete') ?></button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
<?php endforeach ?>
