<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
defined('C5_EXECUTE') or die('Access Denied.');
$c = Page::getCurrentPage();
if ($c->isEditMode()) {
    ?>
    <div class="ccm-edit-mode-disabled-item">
        <?= t('Cookies Notice is disabled in edit mode.') ?>
    </div>
    <?php
} elseif (empty($read)) {
    $wrapperClasses = [
        $position,
    ];
    $cp = new Permissions($c);
    if (is_object($cp) && $cp->canViewToolbar()) {
        $wrapperClasses[] = 'has-toolbar';
    }
    ?>
    <div id="pure-cookies-notice-<?= $bID ?>" class="pure-cookies-notice-wrapper <?= implode(' ', $wrapperClasses) ?>" data-bid="<?= $bID ?>">
        <div class="pure-cookies-notice-container">
            <?php
            if (!empty($title)) {
                ?>
                <div class="pure-cookies-notice-title"><?= $title ?></div>
                <?php
            }
            ?>
            <div class="pure-cookies-notice-content">
                <?= $content ?>
            </div>
            <div class="pure-cookies-notice-close-button">
                <?= empty($agreeText) ? t('Ok') : $agreeText ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#pure-cookies-notice-<?= $bID ?>').pureCookiesNotify(<?= json_encode([
                'sitewideCookie' => !empty($sitewideCookie),
                'interactionImpliesOk' => !empty($interactionImpliesOk),
            ]) ?>);
        });
    </script>
    <?php
}
