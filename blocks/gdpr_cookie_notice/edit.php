<?php

defined('C5_EXECUTE') or die('Access Denied.');

/* @var Concrete\Core\Block\View\BlockView $view */
/* @var Concrete\Core\Form\Service\Form $form */

/* @var Concrete\Core\Application\Service\UserInterface $ui */
/* @var Concrete\Core\Validation\CSRF\Token $token */
/* @var Concrete\Core\Form\Service\Widget\Color $color */
/* @var Concrete\Core\Editor\CkeditorEditor $editor */

/* @var string $defaultAgreeText */
/* @var string $validCookiesRegex */
/* @var array $positions */
/* @var string $defaultCookieName */

/* @var string $title */
/* @var string $content */
/* @var string $agreeText */
/* @var string $textColor */
/* @var string $linkColor */
/* @var string $backgroundColor */
/* @var bool|string $postConsentReload */
/* @var string|string $preConsentGtmBlacklist */
/* @var string $postConsentGtmEventName */
/* @var string $postConsentJavascriptFunction */
/* @var string $position */
/* @var bool|string $onlyForEU */
/* @var bool|string $interactionImpliesOk */
/* @var string $cookieName */
?>

<?= $ui->tabs([
    ['gdpr_cookie_notice-edit-basics', t('Basics'), true],
    ['gdpr_cookie_notice-edit-colors', t('Colors')],
    ['gdpr_cookie_notice-edit-actions', t('Actions')],
    ['gdpr_cookie_notice-edit-advanced', t('Advanced')],
    ['gdpr_cookie_notice-edit-preview', t('Preview')],
]) ?>

<div class="ccm-tab-content" id="ccm-tab-content-gdpr_cookie_notice-edit-basics">
    <div class="form-group">
        <?= $form->label('title', t('Title')) ?>
        <?= $form->text('title', $title, ['placeholder' => t('Optional'), 'maxlength' => 255]) ?>
    </div>

    <div class="form-group">
        <?= $form->label('content', t('Content')) ?>
        <?= $editor->outputStandardEditor('content', $content) ?>
    </div>

    <div class="form-group">
        <?= $form->label('agreeText', t('Consent text')) ?>
        <?= $form->text('agreeText', $agreeText, ['placeholder' => t('Optional. \'%s\' by default', $defaultAgreeText), 'maxlength' => 255]) ?>
    </div>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-gdpr_cookie_notice-edit-colors">
    <div class="form-group">
        <?= $form->label('textColor', t('Text color')) ?>
        <?php $color->output('textColor', $textColor, ['appendTo' => 'body', 'showAlpha' => true]) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the default color.') ?></div>
    </div>

    <div class="form-group">
        <?= $form->label('linkColor', t('Link color')) ?>
        <?php $color->output('linkColor', $linkColor, ['appendTo' => 'body', 'showAlpha' => true]) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the text color.') ?></div>
    </div>

    <div class="form-group">
        <?= $form->label('backgroundColor', t('Background color')) ?>
        <?php $color->output('backgroundColor', $backgroundColor, ['appendTo' => 'body', 'showAlpha' => true]) ?>
        <div class="help-block"><?= t('Optional. Leave empty to use the default color.') ?></div>
    </div>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-gdpr_cookie_notice-edit-actions">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <?= $form->checkbox('postConsentReload', '1', $postConsentReload); ?>
                <?= t('Reload page') ?>
            </label>
        </div>
    </div>
    <div class="form-group">
        <?= $form->label('preConsentGtmBlacklist', t('Google Tag Manager blacklisted tags when not accepted') . ' <a href="https://developers.google.com/tag-manager/devguide#restricting-tag-deployment" target="_blank"><i class="fa fa-info-circle"></i></a>') ?>
        <?= $form->text('preConsentGtmBlacklist', $preConsentGtmBlacklist, ['placeholder' => t('Space-separated list. Optional')]) ?>
    </div>
    <div class="form-group">
        <?= $form->label('postConsentGtmEventName', t('Google Tag Manager event to raise when accepted'), ['class' => 'launch-tooltip', 'title' => t('The event will be fired when the users already agreed or when they agree.')]) ?>
        <?= $form->text('postConsentGtmEventName', $postConsentGtmEventName, ['placeholder' => t('Optional')]) ?>
    </div>
    <div class="form-group">
        <?= $form->label('postConsentJavascriptFunction', t('Custom Javascript function to call when accepted'), ['class' => 'launch-tooltip', 'title' => t('The function will be called when the users already agreed or when they agree.')]) ?>
        <?= $form->text('postConsentJavascriptFunction', $postConsentJavascriptFunction, ['placeholder' => t('Only the function name. Optional.'), 'pattern' => '[$a-zA-Z_][$a-zA-Z_0-9]*']) ?>
    </div>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-gdpr_cookie_notice-edit-advanced">
    <div class="form-group">
        <?= $form->label('position', t('Position')) ?>
        <?= $form->select('position', $positions, $position, ['placeholder' => t('Optional. \'Ok\' by default')]) ?>
    </div>

    <div class="form-group">
        <?= $form->label('onlyForEU', t('Target visitors')) ?>
        <?= $form->select(
            'onlyForEU',
            [
                '0' => t('Show for every site visitor'),
                '1' => t('Show only for site visitors from the European Union'),
            ],
            $onlyForEU ? '1' : '0',
            ['required' => 'required']
        ) ?>
    </div>

    <div class="form-group">
        <?= $form->label('interactionImpliesOk', t('Closing and agreement triggers'), ['class' => 'launch-tooltip', 'data-html' => 'true', 'title' => t("In some countries, any page interaction implies acceptance of the privacy policy (it's called %1\$s); if unsure, select the %2\$s option", '<i>soft opt-in</i>', '<b>' . t('Close on click button only') . '</b>')]) ?>
        <?= $form->select(
            'interactionImpliesOk',
            [
                '0' => t('Close on click button only'),
                '1' => t('Close on any clicks (anywhere) and scrolls'),
            ],
            $interactionImpliesOk ? '1' : '0',
            ['required' => 'required']
        ) ?>
    </div>

    <div class="form-group">
        <?= $form->label('cookieName', t('Custom cookie name'), ['class' => 'launch-tooltip', 'title' => t('Valid cookie names include letters, digits, and some special characters (like dashes, underscores, and a few others).')]) ?>
        <?= $form->text('cookieName', $cookieName, ['placeholder' => $defaultCookieName, 'pattern' => h($validCookiesRegex)]) ?>
    </div>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-gdpr_cookie_notice-edit-preview">
</div>

<script>
    $(function() {
        var $preview = $('#ccm-tab-content-gdpr_cookie_notice-edit-preview'),
            $form = $preview.closest('form'),
            $css = $('style#gdpr_cookie_notice-preview-style');
        $form.closest('.ui-dialog').on('dialogclose', function() {
            $css.remove();
        });
        $('a[data-tab="gdpr_cookie_notice-edit-preview"]').on('click', function(e) {
            e.preventDefault();
            var send = {
                ccm_token: <?= json_encode($token->generate('gdpr_cookie_notice-preview')) ?>
            }
            $preview.empty();
            $form.find('input,textarea,select').each(function() {
                var $field = $(this),
                    name = $field.attr('name');
                if (name) {
                    send[name] = $field.val();
                }
            });
            new ConcreteAjaxRequest({
                url: <?= json_encode((string) $view->action('generate_preview')) ?>,
                data: send,
                dataType: 'json',
                success: function(data) {
                    if ($css.length === 0) {
                        $('head').append($css = $('<style id="gdpr_cookie_notice-preview-style" />'));
                    }
                    $css.html(data.css);
                    $preview.html(data.html);
                }
            });
        });
    });
</script>
