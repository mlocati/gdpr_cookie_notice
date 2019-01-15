<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
/* @var bool $previewing */
/* @var bool $read */
/* @var int|string $bID */
/* @var string $title */
/* @var string $content */
/* @var string $agreeText */
/* @var string $position */
/* @var bool|string $sitewideCookie */
/* @var bool|string $interactionImpliesOk */
/* @var bool|string $postConsentReload */
/* @var string|null $postConsentGtmEventName */
/* @var string|null $postConsentJavascriptFunction */
/* @var string $gtmDataLayerName */

defined('C5_EXECUTE') or die('Access Denied.');
$c = Page::getCurrentPage();
if (empty($previewing) && $c->isEditMode()) {
    $localization = Localization::getInstance();
    $localization->pushActiveContext('ui');
    ?>
    <div class="ccm-edit-mode-disabled-item">
        <?php echo t('Cookies Notice is disabled in edit mode.') ?>
    </div>
    <?php
    $localization->popActiveContext();
} elseif (empty($read) || !empty($previewing)) {
    $wrapperClasses = [
        $position,
    ];
    if (empty($previewing)) {
        $cp = new Permissions($c);
        if (is_object($cp) && $cp->canViewToolbar()) {
            $wrapperClasses[] = 'has-toolbar';
        }
    } else {
        $wrapperClasses[] = 'pure-cookies-notice-wrapper-preview';
    }
    ?>
    <div id="pure-cookies-notice-<?php echo $bID ?>" class="pure-cookies-notice-wrapper <?php echo implode(' ', $wrapperClasses) ?>" data-bid="<?php echo $bID ?>">
        <div class="pure-cookies-notice-container">
            <?php
            if (!empty($title)) {
                ?>
                <div class="pure-cookies-notice-title"><?php echo $title ?></div>
                <?php
            }
            ?>
            <div class="pure-cookies-notice-content">
                <?php echo $content ?>
            </div>
            <div class="pure-cookies-notice-close-button">
                <?php echo empty($agreeText) ? t('Ok') : $agreeText ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <?php
    if (empty($previewing)) {
        ?>
        <script>
            $(document).ready(function() {
                $('#pure-cookies-notice-<?php echo $bID ?>').pureCookiesNotify(<?php echo json_encode([
                    'sitewideCookie' => !empty($sitewideCookie),
                    'interactionImpliesOk' => !empty($interactionImpliesOk),
                    'postConsentReload' => !empty($postConsentReload),
                    'postConsentGtmEventName' => (string) $postConsentGtmEventName,
                    'postConsentJavascriptFunction' => (string) $postConsentJavascriptFunction,
                    'gtmDataLayerName' => (string) $gtmDataLayerName,
                ]) ?>);
            });
        </script>
        <?php
    }
}
