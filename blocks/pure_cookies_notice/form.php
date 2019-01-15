<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
defined('C5_EXECUTE') or die('Access Denied.');

/* @var Concrete\Core\Application\Service\UserInterface $ui */
/* @var Concrete\Core\Validation\CSRF\Token $token */
/* @var Concrete\Core\Form\Service\Widget\Color $color */
/* @var Concrete\Package\PureCookiesNotice\Block\PureCookiesNotice\Controller $controller */
/* @var Concrete\Core\Form\Service\Form $form */
/* @var Concrete\Core\Block\View\BlockView $this */
/* @var Concrete\Core\Block\View\BlockView $view */

/* @var string $title */
/* @var string $agreeText */
/* @var string $textColor */
/* @var string $linkColor */
/* @var string $backgroundColor */
/* @var array $positions */
/* @var string $position */
/* @var bool $geolocationSupported */
/* @var bool|string $onlyForEU */
/* @var bool|string $interactionImpliesOk */
/* @var bool|string $sitewideCookie */
/* @var string $preConsentGtmBlacklist */
/* @var bool|string $postConsentReload */
/* @var string $postConsentGtmEventName */
/* @var string $postConsentJavascriptFunction */

?>
<div class="pure-cookies-notice-edit-container">

    <?= $ui->tabs([
        ['pure-cookies-notice-edit-basics', t('Basics'), true],
        ['pure-cookies-notice-edit-colors', t('Colors')],
        ['pure-cookies-notice-edit-actions', t('Actions')],
        ['pure-cookies-notice-edit-advanced', t('Advanced')],
        ['pure-cookies-notice-edit-preview', t('Preview')],
    ]) ?>

    <div class="ccm-tab-content" id="ccm-tab-content-pure-cookies-notice-edit-basics">
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

    <div class="ccm-tab-content" id="ccm-tab-content-pure-cookies-notice-edit-colors">
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

    <div class="ccm-tab-content" id="ccm-tab-content-pure-cookies-notice-edit-actions">
        <fieldset>
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
        </fieldset>
    </div>

    <div class="ccm-tab-content" id="ccm-tab-content-pure-cookies-notice-edit-advanced">
        <fieldset>
            <div class="form-group">
                <?= $form->label('position', t('Position')) ?>
                <?= $form->select('position', $positions, $position, ['placeholder' => t('Optional. \'Ok\' by default')]) ?>
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

    <div class="ccm-tab-content" id="ccm-tab-content-pure-cookies-notice-edit-preview">
        <div id="ccm-tab-content-pure-cookies-notice-edit-preview-view"></div>
    </div>
</div>

<script>
    $(function() {
        $().pureInputLengthCounter($('.pure-cookies-notice-edit-container input[maxlength]'));
        $('a[data-tab="pure-cookies-notice-edit-preview"]').on('click', function(e) {
            e.preventDefault();
            var $preview = $('#ccm-tab-content-pure-cookies-notice-edit-preview'),
                $form = $preview.closest('form'),
                send = {
                    ccm_token: <?= json_encode($token->generate('pure-cookie-notice-preview')) ?>
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
                    $preview.html(data.html);
                }
            });
        });
    });
</script>
