<?php
/**
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= lang('Reports.report_input') ?></div>

<?= form_open('#', ['id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>

    <div class="form-group form-group-sm">
        <?= form_label(lang('Reports.date_range'), 'report_date_range_label', ['class' => 'control-label col-xs-2 required']) ?>
        <div class="col-xs-3">
            <?= form_input(['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
        </div>
    </div>

    <?php
    echo form_button(
        [
            'name'    => 'generate_report',
            'id'      => 'generate_report',
            'content' => lang('Common.submit'),
            'class'   => 'btn btn-primary btn-sm'
        ]
    );
    ?>

<?= form_close() ?>

<?= view('partial/footer') ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/daterangepicker') ?>

        $("#generate_report").click(function() {
            window.location = [window.location, start_date, end_date].join("/");
        });
    });
</script>