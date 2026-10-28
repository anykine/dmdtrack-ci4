<?php
/**
 * Footer of a module list: row count, items-per-page dropdown and page links.
 *
 * @var \App\Libraries\ListState $state
 * @var int                      $total
 * @var string                   $pagination
 */
?>
<div class="panel-footer">
    <div class="row">
        <div class="col-md-2 text-left">
            <label><?= sprintf(lang('Admin.admin label rows'), $total) ?></label>
        </div>
        <div class="col-md-2 text-left">
            <?php if ($total > 10) : ?>
                <select id="limit" class="form-control">
                    <?php foreach ([10, 25, 50, 75, 100] as $option) : ?>
                        <option value="<?= $option ?>"<?= $state->limit === $option ? ' selected' : '' ?>><?= $option ?> <?= lang('Admin.admin input items_per_page') ?></option>
                    <?php endforeach ?>
                </select>
            <?php endif ?>
        </div>
        <div class="col-md-8">
            <?= $pagination ?>
        </div>
    </div>
</div>

<script>
// Items-per-page dropdown: reload the list with the new limit, back on page 1
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('limit');
    if (!select) return;
    select.addEventListener('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('limit', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });
});
</script>
