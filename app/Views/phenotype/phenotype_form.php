<?php
/**
 * Add/edit phenotype form.
 *
 * @var string      $cancel_url
 * @var array|null  $phenotype
 * @var int|null    $phenotypeID       edit only
 * @var array       $yesNoArray
 * @var int|string  $dataIndividualID   edit only: preselects the individual select2
 * @var string      $dataIndividualName edit only
 */
$phenotype ??= [];

// Value for a field: what was just posted (after a failed save), else the stored value
$value = static fn (string $field): string => (string) set_value($field, $phenotype[$field] ?? '', false);

$errorClass = static fn (string $field): string => validation_show_error($field) !== '' ? ' has-error' : '';

$text = static function (string $field, string $label, string $width = 'col-sm-4') use ($value, $errorClass): string {
    return '<div class="form-group ' . $width . $errorClass($field) . '">'
        . form_label($label, $field, ['class' => 'control-label'])
        . ' <span class="required">*</span>'
        . form_input(['name' => $field, 'id' => $field, 'value' => $value($field), 'class' => 'form-control'])
        . '</div>';
};

$select = static function (string $field, string $label, array $options, bool $required = true, string $width = 'col-sm-4') use ($value, $errorClass): string {
    return '<div class="form-group ' . $width . $errorClass($field) . '">'
        . form_label($label, $field, ['class' => 'control-label'])
        . ($required ? ' <span class="required">*</span>' : '')
        . form_dropdown($field, $options, $value($field), ['id' => $field, 'class' => 'form-control'])
        . '</div>';
};
?>

<?= form_open(current_url(), ['role' => 'form']) ?>

    <?php if (isset($phenotypeID)) : ?>
        <?= form_hidden('phenotypeID', (string) $phenotypeID) ?>
    <?php endif ?>

    <div class="row">
        <div class="form-group col-sm-6<?= $errorClass('individualSearch') ?>">
            <?= form_label('Individual', 'individualSearch', ['class' => 'control-label']) ?>
            <span class="required">*</span>
            <select class="js-example-basic-single" name="individualSearch" id="individualSearch"></select>
        </div>
    </div>

    <div class="row">
        <?= $select('maleFemale', 'Gender', ['M' => 'Male', 'F' => 'Female'], true, 'col-sm-2') ?>
        <?= $text('DOB', 'Date of Birth (YYYY-MM-DD)') ?>
    </div>

    <div class="row">
        <?= $text('age', 'Current Age') ?>
        <?= $text('ageOfDiagnosis', 'Age of diagnosis') ?>
    </div>

    <div class="row">
        <?= $select('DMDorBMD', 'DMD/BMD/WT', ['DMD' => 'DMD', 'BMD' => 'BMD', 'WT' => 'WT', 'notDMD' => 'not DMD', 'DMDcarrier' => 'carrier DMD']) ?>
        <?= $text('initialDx', 'Initial Diagnosis (if not DMD/BMD)') ?>
    </div>

    <div class="row">
        <?= $text('reportedDMDmutation', 'Reported Mutation') ?>
        <?= $text('mildSevere', 'Mild or Severe') ?>
    </div>

    <div class="row">
        <?= $text('WCage', 'WC age (-99 if still walking)') ?>
        <?= $select('geneticReportAvailable', 'Genetic Report Available?', $yesNoArray) ?>
    </div>

    <div class="row">
        <?= $select('biopsyAvailable', 'Biopsy Report Available?', $yesNoArray) ?>
        <?= $select('questionnaireAvailable', 'Questionnaire Available?', $yesNoArray) ?>
    </div>

    <div class="row">
        <?= $text('steroidHistory', 'Steroid History') ?>
        <?= $text('supplementHistory', 'Supplement History') ?>
    </div>

    <div class="row">
        <?= $select('cognitiveDeficiency', 'Cognitive Deficiency', $yesNoArray) ?>
        <?= $select('cardiomyopathy', 'Cardiomyopathy', $yesNoArray) ?>
    </div>

    <div class="row">
        <?= $text('otherStudies', 'Other Studies') ?>
        <?= $text('notes', 'Notes') ?>
    </div>

    <div class="row">
        <?= $select('deceased', 'Deceased?', $yesNoArray, false) ?>
    </div>

    <?php // Preselects the individual in the select2 (see dmdtrack.js) ?>
    <input type="hidden" name="dataIndividualID" id="dataIndividualID" value="<?= esc((string) ($dataIndividualID ?? set_value('individualSearch')), 'attr') ?>">
    <input type="hidden" name="dataIndividualName" id="dataIndividualName" value="<?= esc((string) ($dataIndividualName ?? ''), 'attr') ?>">

    <div class="row pull-right">
        <a class="btn btn-link" href="<?= esc($cancel_url, 'attr') ?>"><?= lang('Core.core button cancel') ?></a>
        <button type="submit" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> <?= lang('Core.core button save') ?></button>
    </div>

<?= form_close() ?>
