<?php
/**
 * Account form: registration (account null) and the user's own profile.
 *
 * @var string     $cancel_url
 * @var array|null $account
 * @var bool       $password_required
 * @var string     $save_label
 */
$languages = get_languages();
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_text('username', lang('Users.users input username'), $account, ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_text('first_name', lang('Users.users input first_name'), $account, ['required' => true]) ?>
        <?= fg_text('last_name', lang('Users.users input last_name'), $account, ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_text('email', lang('Users.users input email'), $account, ['required' => true, 'type' => 'email', 'width' => 'col-sm-6']) ?>
    </div>

    <div class="row">
        <?= fg_select('language', lang('Users.users input language'), $languages, $account, ['required' => true, 'width' => 'col-sm-6', 'default' => session('language')]) ?>
    </div>

    <div class="row">
        <?= fg_open('password', lang('Users.users input password'), ['required' => $password_required]) ?>
            <?= form_password(['name' => 'password', 'id' => 'password', 'value' => '', 'class' => 'form-control', 'autocomplete' => 'new-password']) ?>
        </div>
        <?= fg_open('password_repeat', lang('Users.users input password_repeat'), ['required' => $password_required]) ?>
            <?= form_password(['name' => 'password_repeat', 'id' => 'password_repeat', 'value' => '', 'class' => 'form-control', 'autocomplete' => 'new-password']) ?>
        </div>
        <?php if (! $password_required) : ?>
            <span class="help-block"><br><?= lang('Users.users help passwords') ?></span>
        <?php endif ?>
    </div>

    <div class="row pull-right">
        <a class="btn btn-link" href="<?= esc($cancel_url, 'attr') ?>"><?= lang('Core.core button cancel') ?></a>
        <button type="submit" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> <?= esc($save_label) ?></button>
    </div>

<?= form_close() ?>
