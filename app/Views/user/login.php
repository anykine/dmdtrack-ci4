<?= form_open('login', ['class' => 'form-signin']) ?>
    <?= form_input(['name' => 'username', 'id' => 'username', 'class' => 'form-control', 'placeholder' => lang('Users.users input username_email'), 'maxlength' => 256, 'value' => set_value('username')]) ?>
    <?= form_password(['name' => 'password', 'id' => 'password', 'class' => 'form-control', 'placeholder' => lang('Users.users input password'), 'maxlength' => 72, 'autocomplete' => 'off']) ?>
    <?= form_submit(['name' => 'submit', 'class' => 'btn btn-lg btn-success btn-block'], lang('Core.core button login')) ?>
    <?php // "Forgot password" and "Register" links come back when those pages are ported ?>
<?= form_close() ?>
