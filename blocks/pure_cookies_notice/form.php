<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017
 */

defined('C5_EXECUTE') or die("Access Denied.");
/** @var \Concrete\Core\Form\Service\Form $form */
$form = \Core::make('helper/form');
$color = \Core::make('helper/form/color');
?>
<div class="pure-cookies-notice-edit-container">

    <div class="form-group">
        <?php echo $form->label('title',t('Title'));?>
        <?php echo $form->text('title', $title, ['placeholder' => t('Optional'), 'maxlength' => 255]);?>
    </div>

    <div class="form-group">
        <?php echo $form->label('content',t('Content'));?>
        <?php
        /** @var \Concrete\Core\Editor\CkeditorEditor $editor */
        $editor =  \Core::make("editor");
        echo $editor->outputStandardEditor('content', $controller->getContentEditMode());
        ?>
    </div>

    <div class="form-group">
        <?php echo $form->label('agreeText',t('Consent text'));?>
        <?php echo $form->text('agreeText', $agreeText, ['placeholder' => t('Optional. \'Ok\' by default'), 'maxlength' => 255]);?>
    </div>

    <div class="form-group">
        <?php echo $form->label('position',t('Position'));?>
        <?php echo $form->select('position', $positions, $position,['placeholder' => t('Optional. \'Ok\' by default')]);?>
    </div>

    <div class="form-group">
        <?php echo $form->label('textColor',t('Text color'));?>
        <?php
        $color->output('textColor', $textColor, ['appendTo' => 'body']);
        ?>
        <div class="help-block"><?php echo t('Optional. Leave empty to use the default color from styles.')?></div>
    </div>

    <div class="form-group">
        <?php echo $form->label('backgroundColor',t('Background color'));?>
        <?php
        $color->output('backgroundColor', $backgroundColor, ['appendTo' => 'body','showAlpha' => true]);
        ?>
        <div class="help-block"><?php echo t('Optional. Leave empty to use the default color from styles.')?></div>
    </div>

</div>

<script>
    $(function() {
        $().pureInputLengthCounter($('.pure-cookies-notice-edit-container input[maxlength]'));
    });
</script>
