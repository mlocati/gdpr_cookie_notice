<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
defined('C5_EXECUTE') or die('Access Denied.');

/* @var Concrete\Core\Form\Service\Widget\Color $color */
/* @var Concrete\Package\PureCookiesNotice\Block\PureCookiesNotice\Controller $controller */
/* @var Concrete\Core\Form\Service\Form $form */
/* @var Concrete\Core\Block\View\BlockView $this */
/* @var Concrete\Core\Block\View\BlockView $view */
?>
<div class="pure-cookies-notice-edit-container">

    <div class="form-group">
        <?= $form->label('title', t('Title')) ?>
        <?= $form->text('title', $title, ['placeholder' => t('Optional'), 'maxlength' => 255]) ?>
    </div>

    <div class="form-group">
        <?= $form->label('content', t('Content')) ?>
        <?php
        $editor = Core::make('editor');
        /* @var Concrete\Core\Editor\CkeditorEditor $editor */
        echo $editor->outputStandardEditor('content', $controller->getContentEditMode());
        ?>
    </div>

    <div class="form-group">
        <?= $form->label('agreeText', t('Consent text')) ?>
        <?= $form->text('agreeText', $agreeText, ['placeholder' => t('Optional. \'Ok\' by default'), 'maxlength' => 255]) ?>
    </div>

    <div class="form-group">
        <?= $form->label('position', t('Position')) ?>
        <?= $form->select('position', $positions, $position, ['placeholder' => t('Optional. \'Ok\' by default')]) ?>
    </div>

    <div class="form-group">
        <?= $form->label('interactionImpliesOk', t('Block instances')) ?>
        <?= $form->select(
            'interactionImpliesOk',
            [
                '0' => t('Site visitors must click the Consent button to agree'),
                '1' => t('Cookies agreement is done also on page clicks and scrolls'),
            ],
            empty($interactionImpliesOk) ? '0' : '1',
            ['required' => 'required']
        ) ?>
    </div>

    <div class="form-group">
        <?= $form->label('sitewideCookie', t('Block instances')) ?>
        <?= $form->select(
            'sitewideCookie',
            [
                '1' => t('Site visitors have to accept the warning of a single block instance'),
                '0' => t('Site visitors must accept the warning for every block instance'),
            ],
            empty($sitewideCookie) ? '0' : '1',
            ['required' => 'required']
        ) ?>
    </div>

    <?php
    if ($geolocationSupported) {
        ?>
        <div class="form-group">
            <?= $form->label('onlyForEU', t('Target visitors')) ?>
            <?= $form->select(
                'onlyForEU',
                [
                    '1' => t('Show only for site visitors from the European Union'),
                    '0' => t('Show for every site visitor'),
                ],
                $onlyForEU ? '1' : '0',
                ['required' => 'required']
            ) ?>
        </div>
        <?php
    }
    ?>

    <div class="form-group">
        <?= $form->label('textColor', t('Text color')) ?>
        <?php $color->output('textColor', $textColor, ['appendTo' => 'body']) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the default color from styles.') ?></div>
    </div>

    <div class="form-group">
        <?= $form->label('linkColor', t('Link color')) ?>
        <?php $color->output('linkColor', $linkColor, ['appendTo' => 'body']) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the text color.') ?></div>
    </div>

    <div class="form-group">
        <?= $form->label('backgroundColor', t('Background color')) ?>
        <?php $color->output('backgroundColor', $backgroundColor, ['appendTo' => 'body', 'showAlpha' => true]) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the default color from styles.') ?></div>
    </div>

</div>

<script>
    $(function() {
        $().pureInputLengthCounter($('.pure-cookies-notice-edit-container input[maxlength]'));
    });
</script>
