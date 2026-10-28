<?php
/**
 * Add/edit sequencing run.
 *
 * @var string     $cancel_url
 * @var array|null $sequencing
 * @var array      $sequencingTypes, $sequencingCenters, $platforms, $libraryPreps
 * @var string     $individualID / $individualName  current individual for the select2 box
 * @var string     $sampleID / $sampleName          current sample
 */
// CI3 defaulted these yes/no questions to Yes on a new run
$yes = ['default' => '1', 'width' => 'col-sm-3'];
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_select2('individualSearch', 'Individual', ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_select2('sampleSearch', 'Sample') ?>
        <?= fg_select('sequencingTypeID', 'Sequencing Type (machine/technology)', $sequencingTypes, $sequencing, ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_radios('sequencingQCpassed', 'QC passed?', $sequencing, $yes) ?>
        <?= fg_text('sequencingQCnotes', 'QC Notes', $sequencing, ['width' => 'col-sm-6']) ?>
    </div>

    <div class="row">
        <?= fg_radios('sequencingQueued', 'Queued?', $sequencing, $yes) ?>
        <?= fg_text('sequencingQueuedDate', 'Queued Date (YYYY-MM-DD)', $sequencing) ?>
    </div>

    <div class="row">
        <?= fg_radios('sequencingSubmitted', 'Submitted?', $sequencing, $yes) ?>
        <?= fg_text('sequencingSubmittedDate', 'Submitted Date (YYYY-MM-DD)', $sequencing) ?>
    </div>

    <div class="row">
        <?= fg_radios('sequencingCompleted', 'Sequencing Completed?', $sequencing, $yes) ?>
        <?= fg_text('sequencingCompletedDate', 'Sequenced Date (YYYY-MM-DD)', $sequencing) ?>
        <?= fg_text('sequencingBatchIdentifier', 'Batch ID', $sequencing) ?>
    </div>

    <div class="row">
        <?= fg_select('sequencingCenterID', 'Sequencing Center', $sequencingCenters, $sequencing) ?>
        <?= fg_select('sequencingPlatform', 'Platform', $platforms, $sequencing) ?>
        <?= fg_select('sequencingLibraryID', 'Library', $libraryPreps, $sequencing) ?>
    </div>

    <div class="row">
        <?= fg_radios('sequencingPEorSE', 'Paired/Single End', $sequencing, ['default' => 'PE', 'width' => 'col-sm-3'], ['PE' => 'Paired', 'SE' => 'Single']) ?>
        <?= fg_text('sequencingReadLength', 'sequencingReadLength', $sequencing, ['width' => 'col-sm-3']) ?>
        <?= fg_text('sequencingReadDepth', 'sequencingReadDepth', $sequencing, ['width' => 'col-sm-3']) ?>
        <?= fg_radios('sequencingMultiplexed', 'Multiplexed', $sequencing, $yes) ?>
    </div>

    <div class="row">
        <?= fg_text('otherSampleIDs', 'Other IDs', $sequencing) ?>
        <?= fg_text('sequencingNotes', 'Sample Notes', $sequencing) ?>
        <?= fg_text('sequencingBarcode', 'Barcode', $sequencing) ?>
    </div>

    <div class="row">
        <?= fg_text('sequencingCenterBatchIdentifier', 'Sequencing Center Batch Identifier', $sequencing) ?>
        <?= fg_text('sequencingSampleName', 'Sequencing Sample Name', $sequencing) ?>
    </div>

    <?php // Current choices for the select2 boxes (see dmdtrack.js) ?>
    <?= fg_preselect('dataIndividualID', $individualID, 'dataIndividualName', $individualName) ?>
    <?= fg_preselect('dataSampleID', $sampleID, 'dataSample', $sampleName) ?>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
