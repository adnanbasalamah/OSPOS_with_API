<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var array  $report_data
 */
?>

<?= view('partial/header') ?>

<div id="page_title"><?= esc($title) ?></div>

<?php if (!empty($subtitle)) { ?>
    <div id="subtitle"><?= esc($subtitle) ?></div>
<?php } ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th><?= lang('Reports.report') ?></th>
                <th style="text-align:right"><?= lang('Reports.total') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row) { ?>
                <tr>
                    <td><?= esc($row['label']) ?></td>
                    <td style="text-align:right"><?= to_currency($row['value']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?= view('partial/footer') ?>