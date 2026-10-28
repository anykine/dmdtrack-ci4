<?php
/**
 * Add/edit contact. Every field is optional (CI3 showed a red star on all of them but
 * checked none).
 *
 * @var string     $cancel_url
 * @var array|null $contact
 * @var array      $relationships
 * @var array      $states
 */
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_text('contactFirstName', 'First Name', $contact) ?>
        <?= fg_text('contactLastName', 'Last Name', $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactPatientFirstName', 'Patient First Name', $contact) ?>
        <?= fg_text('contactPatientLastName', 'Patient Last Name', $contact) ?>
    </div>

    <div class="row">
        <?= fg_select('contactRelationship', 'Relation to patient', $relationships, $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactEmail1', 'Email', $contact) ?>
        <?= fg_text('contactEmail2', 'Email2', $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactPhone1', 'Phone1', $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactAddress1', 'Address1', $contact) ?>
        <?= fg_text('contactAddress2', 'Address2', $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactCity', 'City', $contact) ?>
        <?= fg_select('contactState', 'State', $states, $contact) ?>
    </div>

    <div class="row">
        <?= fg_text('contactZip', 'Zipcode', $contact) ?>
        <?= fg_text('contactCountry', 'Country', $contact) ?>
    </div>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
