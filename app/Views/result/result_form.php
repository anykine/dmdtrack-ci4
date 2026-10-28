<?php
/**
 * Add/edit result.
 *
 * @var string     $cancel_url
 * @var array|null $result
 * @var array      $testTypes, $platforms, $libraryPreps, $sequencingCenters
 * @var string     $individualID / $individualName  current individual for the select2 box
 * @var string     $sampleID / $sampleName          current sample
 */
$w3 = ['width' => 'col-sm-3'];
?>
<?= form_open(current_url(), ['role' => 'form']) ?>

    <div class="row">
        <?= fg_select2('individualSearch', 'Individual', ['required' => true]) ?>
    </div>

    <div class="row">
        <?= fg_select2('sampleSearch', 'Sample', ['required' => true]) ?>
        <?= fg_select('testTypeID', 'Test Type', $testTypes, $result) ?>
    </div>

    <div class="row">
        <?= fg_select('platformID', 'Platform', $platforms, $result, ['width' => 'col-sm-6']) ?>
        <?= fg_select('libraryPrepID', 'Library', $libraryPreps, $result) ?>
    </div>

    <div class="row">
        <?php // CI3 defaulted a new result to Paired ?>
        <?= fg_radios('PEorSE', 'Paired or Single End', $result, ['default' => 'PE', 'width' => 'col-sm-6'], ['PE' => 'Paired', 'SE' => 'Single']) ?>
    </div>

    <div class="row">
        <?= fg_text('DMDmutation', 'DMD mutation', $result, $w3) ?>
        <?= fg_text('testFindings', 'Test Findings', $result, $w3) ?>
        <?= fg_text('testFindingsDMDexonic', 'Test Findings DMD exonic mutation', $result, $w3) ?>
        <?= fg_text('testFindingsHGVS', 'Test Findings HGVS', $result, $w3) ?>
    </div>

    <div class="row">
        <?= fg_text('rawDataPath', 'Fastq/Raw data path', $result, ['width' => 'col-sm-6']) ?>
        <?= fg_text('bamFilePath', 'BAM file path', $result, ['width' => 'col-sm-6']) ?>
    </div>

    <div class="row">
        <?= fg_text('sequencingID', 'Sequencing ID', $result) ?>
        <?= fg_text('flowcell', 'Flowcell ID', $result, $w3) ?>
    </div>

    <div class="row">
        <?= fg_text('lane', 'Lane', $result) ?>
        <?= fg_text('barcode', 'Barcode', $result, $w3) ?>
    </div>

    <div class="row">
        <?= fg_text('readDepth', 'readDepth', $result) ?>
        <?= fg_text('readLength', 'readLength', $result, $w3) ?>
    </div>

    <div class="row">
        <?= fg_select('sequencingCenter', 'Sequencing Center', $sequencingCenters, $result) ?>
    </div>

    <?php // Current choices for the select2 boxes (see dmdtrack.js) ?>
    <?= fg_preselect('dataIndividualID', $individualID, 'dataIndividualName', $individualName) ?>
    <?= fg_preselect('dataSampleID', $sampleID, 'dataSample', $sampleName) ?>

    <?= fg_buttons($cancel_url) ?>

<?= form_close() ?>
