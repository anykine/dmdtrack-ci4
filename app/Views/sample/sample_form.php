<?php
/**
 * Add/edit sample.
 *
 * @var string     $cancel_url
 * @var array|null $sample
 * @var array      $sampleTypes, $sampleActions, $extractionTypes, $sequencingCenters
 * @var string     $individualID / $individualName  current individual for the select2 box
 * @var string     $parentID / $parentName          current parent sample
 */
// CI3 defaulted these yes/no questions to Yes on a new sample
$yes = ['default' => '1', 'width' => 'col-sm-3'];
$no  = ['width' => 'col-sm-3'];
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_select2('individualSearch', 'Individual', ['required' => true]) ?>
        <?= fg_select('sampleTypeID', 'Sample Type', $sampleTypes, $sample, ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_select2('sampleParentSearch', 'Sample Parent ID') ?>
    </div>

    <div class="row">
        <?= fg_select('sampleActionID', 'Sample Action', $sampleActions, $sample) ?>
        <?= fg_select('sampleExtractionTypeID', 'Sample Extraction Type', $extractionTypes, $sample) ?>
    </div>

    <div class="row">
        <?= fg_text('concentrationNanodrop', 'Nanodrop Concentration (ng/ul)', $sample, ['width' => 'col-sm-3']) ?>
        <?= fg_text('quality260280', '260/280', $sample, ['width' => 'col-sm-3']) ?>
        <?= fg_text('quality260230', '260/230', $sample, ['width' => 'col-sm-3']) ?>
        <?= fg_text('concentrationQubit', 'Qubit Concentration (ng/ul)', $sample, ['width' => 'col-sm-3']) ?>
    </div>

    <div class="row">
        <?= fg_text('tempSampleID', 'Temp Sample ID', $sample) ?>
        <?= fg_radios('sampleExhausted', 'Sample Exhausted?', $sample, $no) ?>
    </div>

    <div class="row">
        <?= fg_text('sampleDesc', 'sample desc (muscle type/location)', $sample) ?>
        <?= fg_text('sampleMassMg', 'Muscle sample mass (mg)', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('kitSent', 'Collection kit sent?', $sample, $yes) ?>
        <?= fg_text('kitSentDate', 'Kit Sent Date (YYYY-MM-DD)', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('sampleReceived', 'Sample Received?', $sample, $yes) ?>
        <?= fg_text('sampleReceivedDate', 'Sample Received Date (YYYY-MM-DD)', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('extracted', 'Extracted?', $sample, $yes) ?>
        <?= fg_text('extractedDate', 'Extracted Date (YYYY-MM-DD)', $sample) ?>
        <?= fg_text('extractID', 'Extract ID', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('sampleQueued', 'Queued?', $sample, $yes) ?>
        <?= fg_text('sampleQueuedDate', 'Queued Date (YYYY-MM-DD)', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('sampleSubmitted', 'Submitted?', $sample, $yes) ?>
        <?= fg_text('sampleSubmittedDate', 'Submitted Date (YYYY-MM-DD)', $sample) ?>
    </div>

    <div class="row">
        <?= fg_radios('sampleSequenced', 'Sequenced?', $sample, $yes) ?>
        <?= fg_text('sampleSequencedDate', 'Sequenced Date (YYYY-MM-DD)', $sample) ?>
        <?= fg_text('sampleSequencedID', 'Sample Sequenced ID', $sample) ?>
    </div>

    <div class="row">
        <?= fg_text('otherSampleIDs', 'Other IDs', $sample) ?>
        <?= fg_text('sampleNotes', 'Sample Notes', $sample) ?>
    </div>

    <div class="row">
        <?= fg_select('sequencingCenter', 'Sequencing Center', $sequencingCenters, $sample) ?>
    </div>

    <?php // Current choices for the select2 boxes (see dmdtrack.js) ?>
    <?= fg_preselect('dataIndividualID', $individualID, 'dataIndividualName', $individualName) ?>
    <?= fg_preselect('dataSampleParentID', $parentID, 'dataSampleParent', $parentName) ?>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
