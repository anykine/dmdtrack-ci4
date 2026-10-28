<?php
/**
 * Default theme template (moved from assets/themes/default/template.php).
 *
 * Variables come from BaseController::render():
 * @var string        $page_title
 * @var string        $page_header
 * @var string        $content
 * @var array         $css_files
 * @var array         $js_files
 * @var array         $js_files_i18n
 * @var stdClass      $settings
 * @var array|null    $user
 * @var array         $languages
 * @var string        $language
 * @var string        $error
 * @var \Config\Site  $site
 */
$version = esc($site->siteVersion, 'url');
$path    = uri_string();
$active  = static fn (string $section): string => ($path === $section || str_starts_with($path, $section . '/')) ? 'active' : '';
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>?v=<?= $version ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>?v=<?= $version ?>">
    <title><?= esc($page_title) ?> - <?= esc($settings->site_name ?? '') ?></title>
    <meta name="keywords" content="<?= esc($settings->meta_keywords ?? '', 'attr') ?>">
    <meta name="description" content="<?= esc($settings->meta_description ?? '', 'attr') ?>">

    <?php foreach ($css_files as $css) : ?>
        <?php if ($css !== null) : ?>
            <link rel="stylesheet" href="<?= $css ?><?= str_contains($css, '?') ? '&' : '?' ?>v=<?= $version ?>">
        <?php endif ?>
    <?php endforeach ?>
</head>
<body>

    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only"><?= lang('Core.core button toggle_nav') ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?= base_url() ?>"><?= esc($settings->site_name ?? '') ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="<?= $path === '' ? 'active' : '' ?>"><a href="<?= base_url() ?>"><?= lang('Core.core button home') ?></a></li>
                    <li class="<?= $active('individual') ?>"><a href="<?= site_url('individual') ?>">Indiv</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Study<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= site_url('individual?cmd=GenMod') ?>">Genetic Modifier</a></li>
                            <li><a href="<?= site_url('individual?cmd=MGSDD') ?>">Molecular Studies (MGD)</a></li>
                            <li><a href="<?= site_url('individual?cmd=imagingDMD') ?>">Imaging DMD</a></li>
                            <li><a href="<?= site_url('individual?cmd=DMDdx') ?>">Unknown DMD (dna)</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?= site_url('individual?cmd=RNAseqDMD') ?>">RNA-seq DMD</a></li>
                            <li><a href="<?= site_url('individual?cmd=needleBx') ?>">Needle Biospy</a></li>
                            <li><a href="<?= site_url('individual?cmd=aidmd') ?>">Cardiac AIDMD</a></li>
                            <li><a href="<?= site_url('individual?cmd=ennis') ?>">Ennis DMD</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?= site_url('individual?cmd=udn') ?>">UDN</a></li>
                            <li><a href="<?= site_url('individual?cmd=unknownDx') ?>">Unknown Disease (dna)</a></li>
                        </ul>
                    </li>
                    <li class="<?= $active('sequencing') ?>"><a href="<?= site_url('sequencing') ?>">Sequencing</a></li>
                    <li class="<?= $active('sample') ?>"><a href="<?= site_url('sample') ?>">Sample</a></li>
                    <li class="<?= $active('result') ?>"><a href="<?= site_url('result') ?>">Result</a></li>
                    <li class="<?= $active('phenotype') ?>"><a href="<?= site_url('phenotype') ?>">Phenotype</a></li>
                    <li class="<?= $active('referrer') ?>"><a href="<?= site_url('referrer') ?>">Referrer</a></li>
                    <li class="<?= $active('consent') ?>"><a href="<?= site_url('consent') ?>">Consent</a></li>
                    <li class="<?= $active('contact') ?>"><a href="<?= site_url('contact') ?>"><?= lang('Core.core button contact') ?></a></li>
                    <?php if ($user) : ?>
                        <li class="<?= $active('profile') ?>"><a href="<?= site_url('profile') ?>"><?= lang('Core.core button profile') ?></a></li>
                    <?php endif ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($user) : ?>
                        <?php if (! empty($user['is_admin'])) : ?>
                            <li><a href="<?= site_url('admin') ?>"><?= lang('Core.core button admin') ?></a></li>
                        <?php endif ?>
                        <li><a href="<?= site_url('logout') ?>"><?= lang('Core.core button logout') ?></a></li>
                    <?php else : ?>
                        <li class="<?= $active('login') ?>"><a href="<?= site_url('login') ?>"><?= lang('Core.core button login') ?></a></li>
                    <?php endif ?>
                    <li>
                        <span class="dropdown">
                            <button id="session-language" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-default">
                                <i class="fa fa-language"></i>
                                <span class="caret"></span>
                            </button>
                            <ul id="session-language-dropdown" class="dropdown-menu" role="menu" aria-labelledby="session-language">
                                <?php foreach ($languages as $key => $name) : ?>
                                    <li>
                                        <a href="#" rel="<?= esc($key, 'attr') ?>">
                                            <?php if ($key === $language) : ?>
                                                <i class="fa fa-check selected-session-language"></i>
                                            <?php endif ?>
                                            <?= esc($name) ?>
                                        </a>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container theme-showcase" role="main">

        <div class="page-header">
            <h1><?= esc($page_header) ?></h1>
        </div>

        <?php // System messages ?>
        <?php if (session()->getFlashdata('message')) : ?>
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?= esc(session()->getFlashdata('message')) ?>
            </div>
        <?php elseif (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php elseif (validation_errors() !== []) : ?>
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?= validation_list_errors() ?>
            </div>
        <?php elseif ($error !== '') : ?>
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?= esc($error) ?>
            </div>
        <?php endif ?>

        <?= $content ?>

    </div>

    <footer>
        <div class="container">
            <div class="clearfix"><hr /></div>
            <p class="text-muted">
                <?= lang('Core.core text page_rendered') ?>
                | PHP v<?= PHP_VERSION ?>
                | CodeIgniter v<?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
                | <?= esc($settings->site_name ?? '') ?> v<?= esc($site->siteVersion) ?>
            </p>
        </div>
    </footer>

    <?php foreach ($js_files as $js) : ?>
        <?php if ($js !== null) : ?>
            <script type="text/javascript" src="<?= $js ?><?= str_contains($js, '?') ? '&' : '?' ?>v=<?= $version ?>"></script>
        <?php endif ?>
    <?php endforeach ?>
    <?php foreach ($js_files_i18n as $js) : ?>
        <?php if ($js !== null && $js !== '') : ?>
            <script type="text/javascript">
<?= $js ?>

            </script>
        <?php endif ?>
    <?php endforeach ?>

</body>
</html>
