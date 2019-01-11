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

    private function generateStylesheet()
    {
        $lines = ['<style>'];
        $lines[] = "#pure-cookies-notice-{$this->bID} {";
        if (!empty($this->textColor)) {
            $lines[] = "\tcolor: {$this->textColor};";
        }
        if (!empty($this->backgroundColor)) {
            $lines[] = "\tbackground: {$this->backgroundColor};";
        }
        $lines[] = '}';
        $lines[] = "#pure-cookies-notice-{$this->bID} a {";
        if (!empty($this->linkColor)) {
            $lines[] = "\tcolor: {$this->linkColor};";
        }
        elseif (!empty($this->textColor)) {
            $lines[] = "\tcolor: {$this->textColor};";
        }
        $lines[] = '}';
        $lines[] = "#pure-cookies-notice-{$this->bID} .pure-cookies-notice-close-button {";
        if (!empty($this->textColor)) {
            $lines[] = "\tcolor: {$this->textColor};";
            $lines[] = "\tborder-color: {$this->textColor};";
        }
        $lines[] = '}';
        $lines[] = '</style>';

        return implode("\n", $lines);
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
        $this->set('position', 'bottom');
        $this->edit();
    }

    public function edit()
    {
        $this->set('geolocationSupported', $this->geolocationSupported());
        $this->set('color', $this->app->make('helper/form/color'));
        $this->requireAsset('css', 'pure_cookies_notice/edit');
        $this->requireAsset('javascript', 'bootstrap/tab');
        $this->set('positions', [
            'top' => t('Top'),
            'bottom' => t('Bottom'),
        ]);
    }

    public function view()
    {
        if ($this->shouldShowAgreement()) {
            $this->set('read', false);
            $this->set('content', $this->getContent());
        } else {
            $this->set('read', true);
        }
    }

    public function validate($data)
    {
        $e = $this->app->make('error');

        if (empty($data['content'])) {
            $e->add(t(/*i18n: %s is the name of a field */'Required field: %s', 'Content'));
        }
        if (empty($data['position'])) {
            $e->add(t(/*i18n: %s is the name of a field */'Required field: %s', 'Position'));
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

        parent::save($data);
        $this->tracker->track($this);
    }

    public function delete()
    {
        parent::delete();
        $this->tracker->forget($this);
    }
}
