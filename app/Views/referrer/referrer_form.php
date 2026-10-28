<?php
/**
 * Add/edit referrer.
 *
 * @var string     $cancel_url
 * @var array|null $referrer
 * @var array      $states
 */
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_text('referrerInstitution', 'Institution', $referrer) ?>
        <?= fg_text('referrerEmail', 'Email', $referrer) ?>
    </div>

    <div class="row">
        <?= fg_text('referrerFirstName', 'FirstName', $referrer, ['required' => true]) ?>
        <?= fg_text('referrerLastName', 'LastName', $referrer, ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_text('referrerAddress1', 'Address1', $referrer) ?>
        <?= fg_text('referrerAddress2', 'Address2', $referrer) ?>
    </div>

    <div class="row">
        <?= fg_text('referrerCity', 'City', $referrer) ?>
        <?= fg_text('referrerZip', 'Zip', $referrer) ?>
    </div>

    <div class="row">
        <?= fg_select('referrerState', 'State', $states, $referrer) ?>
        <?= fg_text('referrerCountry', 'Country', $referrer, ['width' => 'col-sm-6']) ?>
    </div>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
