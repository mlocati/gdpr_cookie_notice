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
    <ul id="pure-cookies-notice-edit-tabs" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#pure-cookies-notice-edit-basics" aria-controls="pure-cookies-notice-edit-basics" role="tab" data-toggle="tab"><?php echo t('Basics')?></a></li>
        <li role="presentation"><a href="#pure-cookies-notice-edit-colors" aria-controls="pure-cookies-notice-edit-colors" role="tab" data-toggle="tab"><?php echo t('Colors')?></a></li>
        <li role="presentation"><a href="#pure-cookies-notice-edit-advanced" aria-controls="pure-cookies-notice-edit-advanced" role="tab" data-toggle="tab"><?php echo t('Advanced')?></a></li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="pure-cookies-notice-edit-basics">
            <fieldset>
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
            </fieldset>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="pure-cookies-notice-edit-colors">
            <fieldset>
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
            </fieldset>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="pure-cookies-notice-edit-advanced">
            <fieldset>
                <div class="form-group">
                    <?= $form->label('position', t('Position')) ?>
                    <?= $form->select('position', $positions, $position, ['placeholder' => t('Optional. \'Ok\' by default')]) ?>
                </div>

                <div class="form-group">
                    <?= $form->label('interactionImpliesOk', t('Closing triggers')) ?>
                    <?= $form->select(
                        'interactionImpliesOk',
                        [
                            '0' => t('Close on click button only'),
                            '1' => t('Close on any clicks (anywhere) and scrolls'),
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
            </fieldset>
        </div>
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
</div>

<script>
    $(function() {
        $().pureInputLengthCounter($('.pure-cookies-notice-edit-container input[maxlength]'));

        $('#pure-cookies-notice-edit-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        })
    });
</script>
