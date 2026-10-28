<?php
/**
 * Everything about one individual (CI3 views/individual/detail.php).
 *
 * @var array      $individual
 * @var list<array> $family, $consents, $samples, $sequencings, $results
 * @var array|null $contact, $referrer, $phenotype
 */
$id = (int) $individual['individualID'];

// "label: value" cells, CI3 style
$cells = static function (array $pairs): string {
    $html = '';
    foreach ($pairs as $label => $value) {
        $html .= '<td><p class="text-info">' . esc($label) . ':</p></td><td>' . esc((string) $value) . '</td>';
    }

    return $html;
};

$section = static function (string $anchor, string $title, string $addUrl, string $addLabel): string {
    return '<a id="' . $anchor . '"></a><div class="panel-heading"><div class="row">'
        . '<div class="col-md-6 text-left"><h3 class="panel-title">' . esc($title) . '</h3></div>'
        . '<div class="col-md-6 text-right"><a class="btn btn-success" href="' . site_url($addUrl) . '"><span class="glyphicon glyphicon-plus-sign"></span> ' . esc($addLabel) . '</a></div>'
        . '</div></div>';
};

$jump = '<div class="text-center">'
    . '<a href="#individual">Individual</a> | <a href="#contact">Contact</a> | <a href="#phenotype">Phenotype</a> | '
    . '<a href="#consent">Consent</a> | <a href="#sample">Sample</a> | <a href="#sequencing">Sequencing</a> | <a href="#results">Result</a>'
    . '</div>';

$noResults = '<tr><td colspan="6">' . lang('Core.core error no_results') . '</td></tr>';
?>
<div class="panel panel-primary">
    <?= $section('individual', 'Individual Detail', 'individual/add', 'Add New Individual') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <tr>
                <td><p class="text-info">Individual ID</p></td>
                <td><a href="<?= site_url("individual/edit/{$id}") ?>"><?= $id ?></a></td>
            </tr>
            <tr>
                <td><p class="text-info">Individual Name</p></td>
                <td><?= esc(\App\Models\IndividualModel::name($individual)) ?></td>
            </tr>
            <tr>
                <td><p class="text-info">CDMD ID2/ID1/Temp</p></td>
                <td><?= esc($individual['cDMDID'] . ' / ' . $individual['cDMDID1'] . ' / ' . $individual['cDMDTempID']) ?></td>
            </tr>
            <tr>
                <td><p class="text-info">Cell lineID/Needle Biopsy/Previous</p></td>
                <td><?= esc($individual['celllineID'] . ' / ' . $individual['needleBxID'] . ' / ' . $individual['prevID']) ?></td>
            </tr>
            <tr>
                <td><p class="text-info">Referrer</p></td>
                <td>
                    <?php if ($referrer) : ?>
                        <a href="<?= site_url('referrer/edit/' . (int) $referrer['referrerID']) ?>"><?= (int) $referrer['referrerID'] ?></a>
                        <?= esc(trim($referrer['referrerFirstName'] . ' ' . $referrer['referrerLastName'] . ', ' . $referrer['referrerInstitution'], ' ,')) ?>
                    <?php else : ?>
                        <?= lang('Core.core error no_results') ?>
                    <?php endif ?>
                </td>
            </tr>
            <tr>
                <td><p class="text-info">Family members</p></td>
                <td>
                    <?php if ($family) : ?>
                        <?php foreach ($family as $member) : ?>
                            <a href="<?= site_url('indivDetail/view/' . (int) $member['familyIndividualID']) ?>"><?= (int) $member['familyIndividualID'] ?></a>
                            (<?= esc($member['familyRelationType']) ?>),
                        <?php endforeach ?>
                    <?php else : ?>
                        <?= lang('Core.core error no_results') ?>
                    <?php endif ?>
                </td>
            </tr>
        </tbody>
    </table>

    <?= $jump ?>

    <?= $section('contact', 'Contact Info', 'contact/add', 'Add New Contact') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php if ($contact) : ?>
                <tr>
                    <td><p class="text-info">contactID</p></td>
                    <td><a href="<?= site_url('contact/edit/' . (int) $contact['contactID']) ?>"><?= (int) $contact['contactID'] ?></a></td>
                    <?= $cells(['contact Name' => trim($contact['contactFirstName'] . ' ' . $contact['contactLastName'])]) ?>
                </tr>
            <?php else : ?>
                <?= $noResults ?>
            <?php endif ?>
        </tbody>
    </table>

    <?= $section('phenotype', 'Phenotype Info', 'phenotype/add', 'Add New Phenotype') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php if ($phenotype) : ?>
                <tr>
                    <td><p class="text-info">Pheno ID:</p></td>
                    <td><a href="<?= site_url('phenotype/edit/' . (int) $phenotype['phenotypeID']) ?>"><?= (int) $phenotype['phenotypeID'] ?></a></td>
                    <?= $cells(['DMDorBMD' => $phenotype['DMDorBMD'], 'M/F' => $phenotype['maleFemale'], 'mild/severe' => $phenotype['mildSevere'], 'WCage' => $phenotype['WCage']]) ?>
                </tr>
                <tr>
                    <?= $cells(['DOB' => $phenotype['DOB'], 'Age' => $phenotype['age'], 'AgeDx' => $phenotype['ageOfDiagnosis'], 'mutation' => $phenotype['reportedDMDmutation']]) ?>
                </tr>
                <tr>
                    <?= $cells(['Deceased' => $phenotype['deceased'] ?? '', 'Steroid' => $phenotype['steroidHistory'], 'Notes' => $phenotype['notes']]) ?>
                </tr>
            <?php else : ?>
                <?= $noResults ?>
            <?php endif ?>
        </tbody>
    </table>

    <?= $section('consent', 'Consent', 'consent/add', 'Add New Consent') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php foreach ($consents as $consent) : ?>
                <tr>
                    <td><p class="text-info">consentID:</p></td>
                    <td><a href="<?= site_url('consent/edit/' . (int) $consent['consentID']) ?>"><?= (int) $consent['consentID'] ?></a></td>
                    <?= $cells(['Type' => $consent['consentType']]) ?>
                </tr>
            <?php endforeach ?>
            <?= $consents ? '' : $noResults ?>
        </tbody>
    </table>

    <?= $section('sample', 'Sample Info', 'sample/add', 'Add New Sample') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php foreach ($samples as $sample) : ?>
                <tr>
                    <td><p class="text-info">sampleID:</p></td>
                    <td><a href="<?= site_url('sample/edit/' . (int) $sample['sampleID']) ?>"><?= (int) $sample['sampleID'] ?></a></td>
                    <?= $cells(['Type' => $sample['sampleType']]) ?>
                </tr>
                <tr>
                    <?= $cells(['Received' => $sample['sampleReceived'], 'Extracted' => $sample['extracted'], 'ND conc' => $sample['concentrationNanodrop'], 'qubit' => $sample['concentrationQubit'], '260/280' => $sample['quality260280'], '260/230' => $sample['quality260230']]) ?>
                </tr>
                <tr>
                    <?= $cells(['Queued' => $sample['sampleQueued'], 'Submitted' => $sample['sampleSubmitted'], 'Sequenced' => $sample['sampleSequenced'], 'Action' => $sample['sampleAction']]) ?>
                </tr>
                <tr><td colspan="12">&nbsp;</td></tr>
            <?php endforeach ?>
            <?= $samples ? '' : $noResults ?>
        </tbody>
    </table>

    <?= $jump ?>

    <?= $section('sequencing', 'Sequencing Info', 'sequencing/add', 'Add New Sequencing') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php foreach ($sequencings as $seq) : ?>
                <tr>
                    <td><p class="text-info">sequencingID:</p></td>
                    <td><a href="<?= site_url('sequencing/edit/' . (int) $seq['sequencingID']) ?>"><?= (int) $seq['sequencingID'] ?></a></td>
                    <?= $cells(['Type' => $seq['sequencingType']]) ?>
                </tr>
                <tr>
                    <?= $cells(['Queued' => $seq['sequencingQueued'], 'Submitted' => $seq['sequencingSubmitted'], 'Completed' => $seq['sequencingCompleted'], 'Batch' => $seq['sequencingBatchIdentifier'], 'Platform' => $seq['sequencingPlatform']]) ?>
                </tr>
                <tr>
                    <?= $cells(['Paired/Single end' => $seq['sequencingPEorSE'], 'Read length' => $seq['sequencingReadLength'], 'Read Depth' => $seq['sequencingReadDepth'], 'Multiplex' => $seq['sequencingMultiplexed'], 'Barcode' => $seq['sequencingBarcode']]) ?>
                </tr>
                <tr><td colspan="12">&nbsp;</td></tr>
            <?php endforeach ?>
            <?= $sequencings ? '' : $noResults ?>
        </tbody>
    </table>

    <?= $section('results', 'Results Info', 'result/add', 'Add New Result') ?>
    <table class="table table-striped table-hover-warning">
        <tbody>
            <?php foreach ($results as $result) : ?>
                <tr>
                    <td><p class="text-info">Result ID:</p></td>
                    <td><a href="<?= site_url('result/edit/' . (int) $result['resultsID']) ?>"><?= (int) $result['resultsID'] ?></a></td>
                    <?= $cells(['Type' => $result['testType']]) ?>
                </tr>
                <tr>
                    <?= $cells(['DMDmutation' => $result['DMDmutation'], 'testFindings' => $result['testFindings']]) ?>
                </tr>
                <tr><td><p class="text-info">rawDataPath:</p></td><td colspan="6"><?= esc($result['rawDataPath']) ?></td></tr>
                <tr><td><p class="text-info">bamFilePath:</p></td><td colspan="6"><?= esc($result['bamFilePath']) ?></td></tr>
                <tr>
                    <?= $cells(['sequencingCenter' => $result['sequencingCenter'], 'platformID' => $result['platformID'], 'libraryPrepID' => $result['libraryPrepID'], 'barcode' => $result['barcode'], 'readDepth' => $result['readDepth'], 'PEorSE' => $result['PEorSE'], 'readLength' => $result['readLength']]) ?>
                </tr>
                <tr><td colspan="14">&nbsp;</td></tr>
            <?php endforeach ?>
            <?= $results ? '' : $noResults ?>
        </tbody>
    </table>
</div>
