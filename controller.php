<?php 
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
namespace Concrete\Package\PureCookiesNotice;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package as PackageInstaller;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends PackageInstaller
{
    protected $pkgHandle = 'pure_cookies_notice';
    protected $appVersionRequired = '8.1';
    protected $pkgVersion = '1.3.2';

    public function getPackageName()
    {
        return t('Cookies Notice');
    }

    public function getPackageDescription()
    {
        return t('Customizable notifications of users for anything, including the use of cookies (The Cookie Law Explained).');
    }

    public function on_start()
    {
        //*******************************************
        //Assets
        $al = \Concrete\Core\Asset\AssetList::getInstance();

        //CSS
        $al->register(
            'css', //asset type
            'pure_cookies_notice/edit', //asset name
            'blocks/pure_cookies_notice/form.css', //path
            [],
            'pure_cookies_notice' //from package
        );

        //JS
        $al->register(
            'javascript', //asset type
            'bootstrap/tab', //asset name
            'assets/js/bootstrap-tab.min.js', //path
            ['minify' => false],
            'pure_cookies_notice' //from package
        );
        //********************
    }

    public function install()
    {
        /** @var $pkg \Concrete\Core\Entity\Package() */
        $pkg = parent::install();

        $blockType = BlockType::getByHandle($this->pkgHandle);
        if (!is_object($blockType)) {
            BlockType::installBlockType($this->pkgHandle, $pkg);
        }
    }
}
