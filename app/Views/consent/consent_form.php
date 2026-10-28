<?php
/**
 * Add/edit consent.
 *
 * @var string     $cancel_url
 * @var array|null $consent   edit only: includes individualID/Name and consentType
 */
// CI3 defaulted Sent/Received to Yes on a new consent
$yesFirst = ['default' => '1', 'required' => true, 'width' => 'col-sm-3'];
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_select2('individualSearch', 'Individual', ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_select2('consentTypeSearch', 'Consent Type', ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_radios('consentSent', 'Consent Sent?', $consent, $yesFirst) ?>
        <?= fg_text('consentSentDate', 'Consent Sent Date (YYYY-MM-DD)', $consent) ?>
    </div>

    <div class="row">
        <?= fg_radios('consentReceived', 'Consent Received?', $consent, $yesFirst) ?>
        <?= fg_text('consentReceivedDate', 'Consent Received Date (YYYY-MM-DD)', $consent) ?>
    </div>

    <?php // Current choices for the select2 boxes (see dmdtrack.js) ?>
    <?= fg_preselect('dataIndividualID', set_value('individualSearch', (string) ($consent['individualID'] ?? '')), 'dataIndividualName', trim(($consent['individualFirstName'] ?? '') . ' ' . ($consent['individualLastName'] ?? ''))) ?>
    <?= fg_preselect('dataConsentTypeID', set_value('consentTypeSearch', (string) ($consent['cConsentTypeID'] ?? '')), 'dataConsentType', $consent['consentType'] ?? '') ?>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
