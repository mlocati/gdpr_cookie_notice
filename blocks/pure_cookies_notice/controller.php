<?php
/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017.
 */
namespace Concrete\Package\PureCookiesNotice\Block\PureCookiesNotice;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\Statistics\UsageTracker\AggregateTracker;
use Concrete\Core\Entity\Geolocator;
use Concrete\Core\Geolocator\GeolocationResult;
use Concrete\Core\Geolocator\GeolocatorService;
use Exception;
use Punic\Territory;
use Concrete\Core\Statistics\UsageTracker\TrackableInterface;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Error\UserMessageException;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Block\View\BlockView;
use Concrete\Core\Asset\JavascriptInlineAsset;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController implements TrackableInterface, FileTrackableInterface
{
    protected $btTable = 'btPureCookiesNotice';
    protected $btDefaultSet = 'other';
    protected $btInterfaceWidth = 600;
    protected $btInterfaceHeight = 465;
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = true;
    protected $btSupportsInlineEdit = false;
    protected $btSupportsInlineAdd = false;
    protected $btCacheBlockOutputLifetime = 0;

    public $title;
    public $content;
    public $agreeText = '';
    public $position = 'bottom';
    public $textColor = '#fff';
    public $linkColor = '#ff0';
    public $backgroundColor = 'rgba(0, 40, 140, 0.8)';
    public $interactionImpliesOk;
    public $sitewideCookie;
    public $onlyForEU;
    public $preConsentGtmBlacklist;
    public $postConsentReload;
    public $postConsentGtmEventName;
    public $postConsentJavascriptFunction;
    private $previewing = false;

    /**
     * @var \Concrete\Core\Statistics\UsageTracker\AggregateTracker
     */
    protected $tracker;

    public function getBlockTypeName()
    {
        return t('Cookies Notice');
    }

    public function getBlockTypeDescription()
    {
        return t('Cookies Notice allows you to inform users that your site uses cookies.');
    }

    public function __construct($obj = null, AggregateTracker $tracker = null)
    {
        parent::__construct($obj);
        $this->tracker = $tracker;
    }

    public function getContent()
    {
        return LinkAbstractor::translateFrom($this->content);
    }

    public function getSearchableContent()
    {
        return $this->title . ' ' . $this->content;
    }

    public function getContentEditMode()
    {
        return LinkAbstractor::translateFromEditMode($this->content);
    }

    public function br2nl($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("<br />\n", "\n", $str);

        return $str;
    }

    public function getUsedFiles()
    {
        $files = [];
        $matches = [];
        if (preg_match_all('/\<concrete-picture[^>]*?fID\s*=\s*[\'"]([^\'"]*?)[\'"]/i', $this->content, $matches)) {
            list(, $ids) = $matches;
            foreach ($ids as $id) {
                $files[] = (int) $id;
            }
        }

        return $files;
    }

    public function getUsedCollection()
    {
        return $this->getCollectionObject();
    }

    private function geolocationSupported()
    {
        $config = $this->app->make('config');
        return version_compare($config->get('concrete.version'), '8.2.2b2') >= 0;
    }

    /*
     * Is the current site visitor from EU?
     *
     * @return bool
     */
    private function isVisitorFromEU()
    {
        $session = $this->app->make('session');
        /* @var \Symfony\Component\HttpFoundation\Session\Session $session */
        if ($session->has('pureCookieNotifyEU')) {
            $result = (bool) $session->get('pureCookieNotifyEU');
        } else {
            $geolocator = $this->app->make(Geolocator::class);
            if ($geolocator === null) {
                // No geolocation library
                $geolocated = false;
            } else {
                /* @var Geolocator $geolocator */
                $ip = $this->app->make('ip')->getRequestIPAddress();
                $geolocatorController = $this->app->make(GeolocatorService::class)->getController($geolocator);
                try {
                    $geolocated = $geolocatorController->geolocateIPAddress($ip);
                } catch (Exception $x) {
                    $geolocated = false;
                }
            }
            if ($geolocated === null) {
                // Visitors is on a local network -> not EU
                $result = false;
            } elseif ($geolocated instanceof GeolocationResult) {
                $countryCode = $geolocated->getCountryCode();
                if ($countryCode === '') {
                    // Geolocation failed -> let's assume it's EU
                    $result = true;
                } else {
                    // Geolocation succeeded: let's check if the country is in the EU
                    if (in_array($countryCode, Territory::getChildTerritoryCodes('EU'))) {
                        $result = true;
                    } else {
                        $result = false;
                    }
                }
            } else {
                // Geolocation failed -> let's assume it's EU
                $result = true;
            }
            $session->set('pureCookieNotifyEU', $result);
        }

        return $result;
    }

    /**
     * Calculated result of shouldShowAgreement
     *
     * @var null|bool
     */
    private $shouldShowAgreementResult;

    /**
     * Should we show the agreement for the current user?
     *
     * @return bool
     */
    private function shouldShowAgreement()
    {
        if (!isset($this->shouldShowAgreementResult)) {
            $cookieName = 'pureCookieNotify';
            if (empty($this->sitewideCookie)) {
                $cookieName .= '_' . $this->bID;
            }
            $cookie = $this->app->make('cookie');
            /* @var \Concrete\Core\Cookie\CookieJar $cookie */
            if ($cookie->get($cookieName)) {
                $this->shouldShowAgreementResult = false;
            } elseif ($this->onlyForEU && $this->geolocationSupported()) {
                $this->shouldShowAgreementResult = $this->isVisitorFromEU();
            } else {
                $this->shouldShowAgreementResult = true;
            }
        }

        return $this->shouldShowAgreementResult;
    }

    private static function generateCss(Controller $controller)
    {
        $lines = ['<style>'];
        $lines[] = "#pure-cookies-notice-{$controller->bID} {";
        if (!empty($controller->textColor)) {
            $lines[] = "\tcolor: {$controller->textColor};";
        }
        if (!empty($controller->backgroundColor)) {
            $lines[] = "\tbackground: {$controller->backgroundColor};";
        }
        $lines[] = '}';
        $lines[] = "#pure-cookies-notice-{$controller->bID} a {";
        if (!empty($controller->linkColor)) {
            $lines[] = "\tcolor: {$controller->linkColor};";
        }
        elseif (!empty($controller->textColor)) {
            $lines[] = "\tcolor: {$controller->textColor};";
        }
        $lines[] = '}';
        $lines[] = "#pure-cookies-notice-{$controller->bID} .pure-cookies-notice-close-button {";
        if (!empty($controller->textColor)) {
            $lines[] = "\tcolor: {$controller->textColor};";
            $lines[] = "\tborder-color: {$controller->textColor};";
        }
        $lines[] = '}';
        $lines[] = '</style>';

        return implode("\n", $lines);
    }

    private function generateStylesheet()
    {
        return self::generateCss($this, $this->bID);
    }

    public function on_start()
    {
        parent::on_start(); // TODO: Change the autogenerated stub
        if ($this->shouldShowAgreement()) {
            $this->requireAsset('javascript', 'jquery');
            $this->requireAsset('font-awesome');
            $this->addHeaderItem($this->generateStylesheet());
        }
    }

    public function add()
    {
        $this->set('title', '');
        $this->set('agreeText', '');
        $this->set('position', 'bottom');
        $this->set('textColor', '');
        $this->set('linkColor', '');
        $this->set('backgroundColor', '');
        $this->set('sitewideCookie', false);
        $this->set('onlyForEU', false);
        $this->set('interactionImpliesOk', false);
        $this->set('sitewideCookie', true);
        $this->set('preConsentGtmBlacklist', '');
        $this->set('postConsentReload', false);
        $this->set('postConsentGtmEventName', '');
        $this->set('postConsentJavascriptFunction', '');
        $this->edit();
    }

    public function edit()
    {
        $this->set('ui', $this->app->make('helper/concrete/ui'));
        $this->set('token', $this->app->make('token'));
        $this->set('geolocationSupported', $this->geolocationSupported());
        $this->set('color', $this->app->make('helper/form/color'));
        $this->requireAsset('css', 'pure_cookies_notice/edit');
        $this->set('positions', [
            'top' => t('Top'),
            'bottom' => t('Bottom'),
        ]);
    }

    public function registerViewAssets($outputContent = '')
    {
        if ($this->previewing === false) {
            if ($this->shouldShowAgreement()) {
                if ((string) $this->preConsentGtmBlacklist !== '') {
                    $jsGTMDataLayerName = json_encode($this->app->make('config')->get('pure_cookies_notice::google_tag_manager.dataLayerName'));
                    $jsBlacklist = json_encode(['gtm.blacklist' => explode(' ', $this->preConsentGtmBlacklist)]);
                    $asset = new JavascriptInlineAsset();
                    $asset->setAssetPosition(JavascriptInlineAsset::ASSET_POSITION_HEADER);
                    $asset->setAssetURL("(window[{$jsGTMDataLayerName}]=window[{$jsGTMDataLayerName}]||[]).push({$jsBlacklist});");
                    $this->requireAsset($asset);
                }
            } else {
                if ((string) $this->postConsentGtmEventName !== '') {
                    $jsGTMDataLayerName = json_encode($this->app->make('config')->get('pure_cookies_notice::google_tag_manager.dataLayerName'));
                    $jsEvent = json_encode(['event' => $this->postConsentGtmEventName]);
                    $asset = new JavascriptInlineAsset();
                    $asset->setAssetPosition(JavascriptInlineAsset::ASSET_POSITION_HEADER);
                    $asset->setAssetURL("(window[{$jsGTMDataLayerName}]=window[{$jsGTMDataLayerName}]||[]).push({$jsEvent});");
                    $this->requireAsset($asset);
                }
                if ((string) $this->postConsentJavascriptFunction !== '') {
                    $jsFunction = json_encode($this->postConsentJavascriptFunction);
                    $asset = new JavascriptInlineAsset();
                    $asset->setAssetPosition(JavascriptInlineAsset::ASSET_POSITION_FOOTER);
                    $asset->setAssetURL("if(window[{$jsFunction}])window[{$jsFunction}]();");
                    $this->requireAsset($asset);
                }
            }
        }
    }

    public function view()
    {
        $this->set('previewing', $this->previewing);
        if ($this->previewing || $this->shouldShowAgreement()) {
            $this->set('read', false);
            $this->set('bID', $this->bID);
            $this->set('title', $this->title);
            $this->set('content', $this->getContent());
            $this->set('agreeText', $this->agreeText);
            $this->set('position', $this->position);
            $this->set('postConsentReload', (bool) $this->postConsentReload);
            $this->set('postConsentGtmEventName', (string) $this->postConsentGtmEventName);
            $this->set('postConsentJavascriptFunction', (string) $this->postConsentJavascriptFunction);
            $this->set('gtmDataLayerName', $this->app->make('config')->get('pure_cookies_notice::google_tag_manager.dataLayerName'));
        } else {
            $this->set('read', true);
        }
    }

    public function validate($data)
    {
        $e = $this->app->make('error');

        if (empty($data['content'])) {
            $e->add(t('Field "%s" is required.', t('Content')), 'content', t('Content'));
        }
        if (empty($data['position'])) {
            $e->add(t('Field "%s" is required.', t('Position')), 'position', t('Position'));
        }
        $s = isset($data['postConsentJavascriptFunction']) ? trim($data['postConsentJavascriptFunction']) : ' ';
        if ($s !== '' && !preg_match('/^[$a-zA-Z_][$a-zA-Z_0-9]*$/i', $s)) {
            $e->add(t('Field "%s" is invalid.', t('Custom Javascript function to call when accepted')), 'postConsentJavascriptFunction', t('Custom Javascript function to call when accepted'));
        }

        return $e;
    }

    public function save($data)
    {
        /** @var \Concrete\Core\Utility\Service\Text $th */
        $th = $this->app->make('helper/text');
        $data['title'] = $th->entities(trim($data['title']));

        $data['agreeText'] = $th->entities(trim($data['agreeText']));

        if (isset($data['content'])) {
            $data['content'] = LinkAbstractor::translateTo($data['content']);
        }
        
        $data['interactionImpliesOk'] = empty($data['interactionImpliesOk']) ? 0 : 1;
        $data['sitewideCookie'] = empty($data['sitewideCookie']) ? 0 : 1;
        $data['onlyForEU'] = empty($data['onlyForEU']) ? 0 : 1;
        $data['preConsentGtmBlacklist'] = isset($data['preConsentGtmBlacklist']) ? trim(preg_replace('/\s+/', ' ', $data['preConsentGtmBlacklist'])) : '';
        $data['postConsentReload'] = empty($data['postConsentReload']) ? 0 : 1;
        $data['postConsentGtmEventName'] = isset($data['postConsentGtmEventName']) ? trim($data['postConsentGtmEventName']) : '';
        $data['postConsentJavascriptFunction'] = isset($data['postConsentJavascriptFunction']) ? trim($data['postConsentJavascriptFunction']) : '';

        parent::save($data);
        $this->tracker->track($this);
    }

    public function delete()
    {
        parent::delete();
        $this->tracker->forget($this);
    }

    public function action_generate_preview()
    {
        $token = $this->app->make('token');
        if (!$token->validate('pure-cookie-notice-preview')) {
            if (class_exists(UserMessageException::class)) {
                throw new UserMessageException($token->getErrorMessage());
            }
            throw new Exception($token->getErrorMessage());
        }
        $em = $this->app->make(EntityManagerInterface::class);
        if (method_exists($this, 'getBlockTypeID')) {
            $blockType = $em->find(BlockType::class, $this->getBlockTypeID());
        } else {
            $blockType = $em->getRepository(BlockType::class)->findOneBy(['btHandle' => $this->btHandle]);
        }
        $blockTypeController = $blockType->getController();
        $post = $this->request->request;

        $blockTypeController->bID = md5(mt_rand() . '@' . microtime(true));
        $blockTypeController->title = $post->get('title');
        $blockTypeController->content = LinkAbstractor::translateTo($post->get('content'));
        $blockTypeController->agreeText = $post->get('agreeText');
        $blockTypeController->position = $post->get('position');
        $blockTypeController->textColor = $post->get('textColor');
        $blockTypeController->linkColor = $post->get('linkColor');
        $blockTypeController->backgroundColor = $post->get('backgroundColor');
        $blockTypeController->previewing = true;
        $blockView = new BlockView($blockType);
        ob_start();
        $blockView->render('view');
        $html = ob_get_contents();
        ob_end_clean();
        $html = '<script> $("head").append(' . json_encode(self::generateCss($blockTypeController)) . '); </script>' . $html;
        $html = $this->generateCss($blockTypeController, $blockTypeController->bID, true). $html;

        return $this->app->make(ResponseFactoryInterface::class)->json(['html' => $html]);
    }
}
