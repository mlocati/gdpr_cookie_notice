<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017
 */
defined('C5_EXECUTE') or die("Access Denied.");
$c = \Page::getCurrentPage();
if ($c->isEditMode()) {
    ?>
    <div class="ccm-edit-mode-disabled-item">
        <?php echo t('Cookies Notice is disabled in edit mode.') ?>
    </div>
    <?php
} else if (!$read) {
    $wrapperClasses = [
        $position
    ];

    $cp = new \Permissions($c);
    if (is_object($cp) && $cp->canViewToolbar()) {
        $wrapperClasses[] = 'has-toolbar';
    }

    ?>
    <style>
        #pure-cookies-notice-<?php echo $bID?> {
            <?php if (isset($textColor) && !empty($textColor)) {?>
            color: <?php echo $textColor?>;<?php
            }
            if (isset($backgroundColor) && !empty($backgroundColor)) {?>
            background: <?php echo $backgroundColor?>;<?php
            }?>
        }

        #pure-cookies-notice-<?php echo $bID?> a {
            <?php if (isset($textColor) && !empty($textColor)) {?>
            color: <?php echo $textColor?>;<?php
            }?>
        }

        #pure-cookies-notice-<?php echo $bID?> .pure-cookies-notice-close-button {
            <?php if (isset($textColor) && !empty($textColor)) {?>
            color: <?php echo $textColor?>;
            border-color: <?php echo $textColor?>;<?php
            }?>
        }
    </style>

    <div id="pure-cookies-notice-<?php echo $bID?>" class="pure-cookies-notice-wrapper <?php echo implode(' ', $wrapperClasses) ?>" data-bid="<?php echo $bID?>">
        <div class="pure-cookies-notice-container">
            <?php
            if (!empty($title)) {
                ?><div class="pure-cookies-notice-title"><?php echo $title?></div><?php
            }
            ?>
            <div class="pure-cookies-notice-content">
                <?php echo $content?>
            </div>
            <div class="pure-cookies-notice-close-button">
                <?php echo $agreeText?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#pure-cookies-notice-<?php echo $bID?>').pureCookiesNotify();
        });
    </script>
    <?php
}

