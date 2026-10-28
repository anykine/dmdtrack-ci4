<?php
/**
 * Add/edit individual.
 *
 * @var string     $cancel_url
 * @var array|null $individual
 * @var string     $referrerID / $referrerName  current referrer for the select2 box
 * @var string     $contactID  / $contactName   current contact for the select2 box
 */
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <div class="col-sm-6">
            To add an individual, add:
            <ul class="list-group">
                <li class="list-group-item">Contact</li>
                <li class="list-group-item">Individual</li>
                <li class="list-group-item">Phenotype</li>
                <li class="list-group-item">Consent</li>
                <li class="list-group-item">Sample</li>
            </ul>
        </div>
    </div>

    <div class="row">
        <?= fg_text('cDMDID', 'CDMD ID2 (CDMD 2NNN)', $individual) ?>
        <?= fg_text('cDMDID1', 'CDMD ID1 (CDMD 1NNN)', $individual) ?>
    </div>

    <div class="row">
        <?= fg_text('celllineID', 'celllineID', $individual) ?>
        <?= fg_text('needleBxID', 'needle Bx ID', $individual) ?>
    </div>

    <div class="row">
        <?= fg_text('prevID', 'prevID', $individual) ?>
        <?= fg_text('cDMDTempID', 'CDMD TempID', $individual) ?>
    </div>

    <div class="row">
        <?= fg_text('individualFirstName', 'First Name', $individual) ?>
        <?= fg_text('individualMiddleName', 'Middle', $individual) ?>
    </div>

    <div class="row">
        <?= fg_text('individualLastName', 'Last Name', $individual) ?>
    </div>

    <div class="row">
        <?= fg_select2('referrerSearch', 'Referrer (Search by institution)') ?>
    </div>

    <div class="row">
        <?= fg_select2('contactSearch', 'Contact (Search by parent/child name)') ?>
    </div>

    <?php // Current choices for the select2 boxes (see dmdtrack.js) ?>
    <?= fg_preselect('dataReferrerID', $referrerID, 'dataReferrer', $referrerName) ?>
    <?= fg_preselect('dataContactID', $contactID, 'dataContact', $contactName) ?>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
