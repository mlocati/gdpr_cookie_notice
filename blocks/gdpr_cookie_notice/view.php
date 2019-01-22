<?php
defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Permission\Checker;

/* @var bool $previewing */
/* @var bool $read */

// When $read is false
/* @var int|string $bID */
/* @var string $title */
/* @var string $content */
/* @var string $agreeText */
/* @var string $position */

// When $read is false and $previewing is false
/* @var bool $postConsentReload */
/* @var string $postConsentJavascriptFunction */
/* @var bool $interactionImpliesOk */
/* @var array $gtm */
/* @var array $cookie */

$c = Page::getCurrentPage();

if ($previewing === false && $c->isEditMode()) {
    $localization = Localization::getInstance();
    $localization->pushActiveContext('ui');
    ?>
    <div class="ccm-edit-mode-disabled-item">
        <?= t('Cookies Notice is disabled in edit mode.') ?>
    </div>
    <?php
    $localization->popActiveContext();
} elseif ($previewing === true || $read === false) {
    $wrapperClasses = [];
    if ($previewing === false) {
        $wrapperClasses[] = 'gdpr_cookie_notice-position-' . $position;
        $cp = new Checker($c);
        if ($cp->canViewToolbar()) {
            $wrapperClasses[] = 'gdpr_cookie_notice-withtoolbar';
        }
    } else {
        $wrapperClasses[] = 'gdpr_cookie_notice-preview';
    }
    ?>
    <div id="gdpr_cookie_notice-<?= $bID ?>" class="gdpr_cookie_notice <?= implode(' ', $wrapperClasses) ?>" data-bid="<?= $bID ?>">
        <div class="gdpr_cookie_notice-container">
            <?php
            if ($title !== '') {
                ?>
                <div class="gdpr_cookie_notice-title"><?= h($title) ?></div>
                <?php
            }
            ?>
            <div class="gdpr_cookie_notice-content"><?= $content ?></div>
            <div class="gdpr_cookie_notice-close"><?= h($agreeText) ?></div>
            <div class="gdpr_cookie_notice-clearfix"></div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        <?php
        if ($previewing === false) {
            ?>
            $('#gdpr_cookie_notice-<?= $bID ?>').gdprCookieNotify(<?= json_encode([
                'postConsentReload' => $postConsentReload,
                'interactionImpliesOk' => $interactionImpliesOk,
                'postConsentJavascriptFunction' => $postConsentJavascriptFunction,
                'gtm' => $gtm,
                'cookie' => $cookie,
            ]) ?>);
            <?php
        } else {
            ?>
            $('#gdpr_cookie_notice-<?= $bID ?> *').on('click', function(e) {
                e.preventDefault();
            });
            <?php
        }
        ?>
    });
    </script>
    <?php
}
