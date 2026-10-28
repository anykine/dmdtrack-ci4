<?php
/**
 * @var string $cancel_url
 */
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_text('email', lang('Users.users input email'), null, ['required' => true, 'type' => 'email', 'width' => 'col-sm-6']) ?>
    </div>

    <div class="row pull-right">
        <a class="btn btn-link" href="<?= esc($cancel_url, 'attr') ?>"><?= lang('Core.core button cancel') ?></a>
        <button type="submit" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> <?= lang('Users.users button reset_password') ?></button>
    </div>

<?= form_close() ?>
