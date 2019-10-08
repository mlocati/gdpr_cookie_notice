<?php

namespace Concrete\Package\GdprCookieNotice\Block\GdprCookieNotice;

use Concrete\Core\Asset\JavascriptInlineAsset;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Block\View\BlockView;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Geolocator\GeolocationResult;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Session\SessionValidatorInterface;
use Concrete\Core\Statistics\UsageTracker\AggregateTracker;
use Concrete\Core\Statistics\UsageTracker\TrackableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Punic\Territory;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController implements TrackableInterface, FileTrackableInterface
{
    /**
     * Notice position: top.
     *
     * @var string
     */
    const POSITION_TOP = 'top';

    /**
     * Notice position: bottom.
     *
     * @var string
     */
    const POSITION_BOTTOM = 'bottom';

    /**
     * Regular expression to validate cookie names.
     *
     * @see https://tools.ietf.org/search/rfc6265#section-4.1.1
     * @see https://tools.ietf.org/search/rfc2616#section-2.2
     *
     * @var string
     */
    const VALID_COOKIES_REGEX = '[\!\#\$%&\'\*\+\-\.0-9A-Z\^_`a-z\|~]{0,255}';

    /**
     * @var string
     */
    const DEFAULT_TEXT_COLOR = '#ffffff';

    /**
     * @var string
     */
    const DEFAULT_LINK_COLOR = '#ffff00';

    /**
     * @var string
     */
    const DEFAULT_BACKGROUND_COLOR = 'rgba(0, 40, 136, 0.8)';

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $agreeText;

    /**
     * @var string
     */
    public $textColor;

    /**
     * @var string
     */
    public $linkColor;

    /**
     * @var string
     */
    public $backgroundColor;

    /**
     * @var bool|string
     */
    public $postConsentReload;

    /**
     * @var string
     */
    public $preConsentGtmBlacklist;

    /**
     * @var string
     */
    public $postConsentGtmEventName;

    /**
     * @var string
     */
    public $postConsentJavascriptFunction;

    /**
     * @var string
     */
    public $position;

    /**
     * @var bool|string
     */
    public $onlyForEU;

    /**
     * @var bool|string
     */
    public $interactionImpliesOk;

    /**
     * @var string
     */
    public $cookieName;

    /**
     * @var string
     */
    protected $btTable = 'btGdprCookieNotice';

    /**
     * @var string
     */
    protected $btDefaultSet = 'other';

    /**
     * @var int
     */
    protected $btInterfaceWidth = 600;

    /**
     * @var int
     */
    protected $btInterfaceHeight = 465;

    /**
     * @var bool
     */
    protected $btCacheBlockRecord = true;

    /**
     * @var bool
     */
    protected $btCacheBlockOutput = false;

    /**
     * @var bool
     */
    protected $btCacheBlockOutputOnPost = true;

    /**
     * @var bool
     */
    protected $btSupportsInlineEdit = false;

    /**
     * @var bool
     */
    protected $btSupportsInlineAdd = false;

    /**
     * @var bool
     */
    protected $supportSavingNullValues = true;

    /**
     * @var \Concrete\Core\Statistics\UsageTracker\AggregateTracker
     */
    protected $tracker;

    /**
     * @var bool
     */
    private $previewing = false;

    /**
     * @var bool|null
     */
    private $shouldShowAgreementResult;

    /**
     * @var bool|null
     */
    private $isVisitorFromEU;

    /**
     * @param mixed $obj
     * @param \Concrete\Core\Statistics\UsageTracker\AggregateTracker $tracker
     */
    public function __construct($obj = null, AggregateTracker $tracker = null)
    {
        parent::__construct($obj);
        $this->tracker = $tracker;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeName()
     */
    public function getBlockTypeName()
    {
        return t('GDPR Cookie Notice');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeDescription()
     */
    public function getBlockTypeDescription()
    {
        return t('Show a cookie-based notice.');
    }

    /**
     * @return string
     */
    public function getSearchableContent()
    {
        return $this->title . ' ' . $this->content;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedFiles()
     */
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

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedCollection()
     */
    public function getUsedCollection()
    {
        return $this->getCollectionObject();
    }

    public function on_start()
    {
        parent::on_start();
        if ($this->bID && $this->bID !== 'preview' && $this->shouldShowAgreement()) {
            $this->requireAsset('javascript', 'jquery');
            $this->requireAsset('font-awesome');
            $this->addHeaderItem("<style>\n" . $this->generateCss() . "\n</style>");
        }
    }

    public function add()
    {
        $this->set('title', '');
        $this->content = '';
        $this->set('agreeText', '');
        $this->set('textColor', self::DEFAULT_TEXT_COLOR);
        $this->set('linkColor', self::DEFAULT_LINK_COLOR);
        $this->set('backgroundColor', self::DEFAULT_BACKGROUND_COLOR);
        $this->set('postConsentReload', false);
        $this->set('preConsentGtmBlacklist', '');
        $this->set('postConsentGtmEventName', '');
        $this->set('postConsentJavascriptFunction', '');
        $this->set('position', self::POSITION_BOTTOM);
        $this->set('onlyForEU', false);
        $this->set('interactionImpliesOk', false);
        $this->set('cookieName', '');
        $this->edit();
    }

    public function edit()
    {
        $this->set('ui', $this->app->make('helper/concrete/ui'));
        $this->set('token', $this->app->make('token'));
        $this->set('color', $this->app->make('helper/form/color'));
        $this->set('editor', $this->app->make('editor'));
        $this->set('validCookiesRegex', self::VALID_COOKIES_REGEX);
        $this->set('defaultAgreeText', $this->getDefaultAgreeText());
        $this->set('positions', [
            self::POSITION_TOP => t('Top'),
            self::POSITION_BOTTOM => t('Bottom'),
        ]);
        $this->set('defaultCookieName', $this->app->make('config')->get('gdpr_cookie_notice::cookie.defaultName'));
        $this->set('content', LinkAbstractor::translateFromEditMode((string) $this->content));
    }

    public function registerViewAssets($outputContent = '')
    {
        if ($this->previewing === false) {
            if ($this->shouldShowAgreement()) {
                if ((string) $this->preConsentGtmBlacklist !== '') {
                    $jsGTMDataLayerName = json_encode($this->app->make('config')->get('gdpr_cookie_notice::google_tag_manager.dataLayerName'));
                    $jsBlacklist = json_encode(['gtm.blacklist' => explode(' ', $this->preConsentGtmBlacklist)]);
                    $asset = new JavascriptInlineAsset();
                    $asset->setAssetPosition(JavascriptInlineAsset::ASSET_POSITION_HEADER);
                    $asset->setAssetURL("(window[{$jsGTMDataLayerName}]=window[{$jsGTMDataLayerName}]||[]).push({$jsBlacklist});");
                    $this->requireAsset($asset);
                }
            } else {
                if ((string) $this->postConsentGtmEventName !== '') {
                    $jsGTMDataLayerName = json_encode($this->app->make('config')->get('gdpr_cookie_notice::google_tag_manager.dataLayerName'));
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
            $this->set('content', LinkAbstractor::translateFrom($this->content));
            $this->set('agreeText', $this->agreeText === '' ? $this->getDefaultAgreeText() : $this->agreeText);
            $this->set('position', $this->position);
            if (!$this->previewing) {
                $config = $this->app->make('config');
                $this->set('interactionImpliesOk', (bool) $this->interactionImpliesOk);
                $this->set('postConsentReload', (bool) $this->postConsentReload);
                $this->set('postConsentJavascriptFunction', (string) $this->postConsentJavascriptFunction);
                $gtm = [
                    'dataLayerName' => $config->get('gdpr_cookie_notice::google_tag_manager.dataLayerName'),
                    'postConsentEventName' => (string) $this->postConsentGtmEventName,
                ];
                $this->set('gtm', $gtm);
                $cookie = [
                    'name' => $this->getCookieName(),
                    'duration' => (int) $config->get('gdpr_cookie_notice::cookie.duration'),
                    'path' => (string) $config->get('gdpr_cookie_notice::cookie.path'),
                    'domain' => (string) $config->get('gdpr_cookie_notice::cookie.domain'),
                ];
                if ($cookie['path'] === '') {
                    $cookie['path'] = rtrim($this->app->make('app_relative_path'), '/') . '/';
                }
                $this->set('cookie', $cookie);
            }
        } else {
            $this->set('read', true);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::validate()
     */
    public function validate($args)
    {
        $check = $this->normalizeData($args);

        return is_array($check) ? parent::validate($args) : $check;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::save()
     */
    public function save($args)
    {
        $data = $this->normalizeData($args);

        if (!is_array($data)) {
            throw new UserMessageException($data->toText());
        }
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
        if (!$token->validate('gdpr_cookie_notice-preview')) {
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

        $blockTypeController->bID = 'preview';
        $blockTypeController->title = $post->get('title');
        $blockTypeController->content = LinkAbstractor::translateTo($post->get('content'));
        $blockTypeController->agreeText = $post->get('agreeText');
        $blockTypeController->position = $post->get('position');
        $blockTypeController->textColor = $post->get('textColor');
        $blockTypeController->linkColor = $post->get('linkColor');
        $blockTypeController->backgroundColor = trim($post->get('backgroundColor', ''));
        $blockTypeController->previewing = true;
        $css = $this->generateCss($blockTypeController, true);
        $blockView = new BlockView($blockType);
        ob_start();
        $blockView->render('view');
        $html = ob_get_contents();
        ob_end_clean();

        return $this->app->make(ResponseFactoryInterface::class)->json(['css' => $css, 'html' => $html]);
    }

    /**
     * @param array|mixed $data
     *
     * @return \Concrete\Core\Error\ErrorList\ErrorList|array
     */
    private function normalizeData($data)
    {
        $e = $this->app->make('error');
        if (!is_array($data)) {
            $data = [];
        }
        $normalized = [
            'title' => isset($data['title']) ? trim($data['title']) : '',
            'content' => isset($data['content']) ? trim($data['content']) : '',
            'agreeText' => isset($data['agreeText']) ? trim($data['agreeText']) : '',
            'textColor' => isset($data['textColor']) ? trim($data['textColor']) : '',
            'linkColor' => isset($data['linkColor']) ? trim($data['linkColor']) : '',
            'backgroundColor' => isset($data['backgroundColor']) ? trim($data['backgroundColor']) : '',
            'postConsentReload' => empty($data['postConsentReload']) ? 0 : 1,
            'preConsentGtmBlacklist' => isset($data['preConsentGtmBlacklist']) ? trim(preg_replace('/\s+/', ' ', $data['preConsentGtmBlacklist'])) : '',
            'postConsentGtmEventName' => isset($data['postConsentGtmEventName']) ? trim($data['postConsentGtmEventName']) : '',
            'postConsentJavascriptFunction' => isset($data['postConsentJavascriptFunction']) ? trim($data['postConsentJavascriptFunction']) : '',
            'position' => isset($data['position']) ? $data['position'] : self::POSITION_BOTTOM,
            'onlyForEU' => empty($data['onlyForEU']) ? 0 : 1,
            'interactionImpliesOk' => empty($data['interactionImpliesOk']) ? 0 : 1,
            'cookieName' => isset($data['cookieName']) ? trim($data['cookieName']) : '',
        ];
        if (mb_strlen($data['title']) > 255) {
            $e->add(t('The maximum length for the "%s" field is %s characters.', t('Title'), 255), 'title', t('Title'));
        }
        if ($normalized['content'] === '') {
            $e->add(t('The field "%s" is required.', t('Content')), 'content', t('Content'));
        } else {
            $normalized['content'] = LinkAbstractor::translateTo($normalized['content']);
        }
        if (mb_strlen($data['agreeText']) > 255) {
            $e->add(t('The maximum length for the "%s" field is %s characters.', t('Consent text'), 255), 'agreeText', t('Consent text'));
        }
        if (!in_array($normalized['position'], [self::POSITION_TOP, self::POSITION_BOTTOM], true)) {
            $e->add(t('Please specify a valid value for the "%s" field.', t('Position')), 'position', t('Position'));
        }
        if (!preg_match('/^' . self::VALID_COOKIES_REGEX . '$/i', $data['cookieName'])) {
            $e->add(t('Please specify a valid value for the "%s" field.', t('Custom cookie name')), 'cookieName', t('Custom cookie name'));
        }

        return $e->has() ? $e : $normalized;
    }

    /**
     * Is the current site visitor from EU?
     *
     * @return bool
     */
    private function isVisitorFromEU()
    {
        if ($this->isVisitorFromEU === null) {
            $svi = $this->app->make(SessionValidatorInterface::class);
            if (!method_exists($svi, 'hasActiveSession') || $svi->hasActiveSession()) {
                $session = $this->app->make('session');
                if ($session->has('gdprCookieNoticeEUVisitor')) {
                    $this->isVisitorFromEU = (bool) $session->get('gdprCookieNoticeEUVisitor');
                }
            } else {
                $session = null;
            }
            if ($this->isVisitorFromEU === null) {
                $geolocated = $this->app->make(GeolocationResult::class);
                $countryCode = $geolocated->getCountryCode();
                if ($countryCode === '') {
                    // Geolocation failed -> let's assume it's EU
                    $this->isVisitorFromEU = true;
                } else {
                    // Geolocation succeeded: let's check if the country is in the EU
                    $this->isVisitorFromEU = in_array($countryCode, Territory::getChildTerritoryCodes('EU'));
                }
                if ($session !== null) {
                    $session->set('gdprCookieNoticeEUVisitor', $this->isVisitorFromEU);
                }
            }
        }

        return $this->isVisitorFromEU;
    }

    /**
     * Get the name of the cookie to be used.
     *
     * @return string
     */
    private function getCookieName()
    {
        $cookieName = (string) $this->cookieName;
        if ($cookieName === '') {
            $cookieName = $this->app->make('config')->get('gdpr_cookie_notice::cookie.defaultName');
        }

        return $cookieName;
    }

    /**
     * Should we show the agreement for the current user?
     *
     * @return bool
     */
    private function shouldShowAgreement()
    {
        if ($this->shouldShowAgreementResult === null) {
            $cookieName = $this->getCookieName();
            $cookie = $this->app->make('cookie');
            if ($cookie->get($cookieName)) {
                $this->shouldShowAgreementResult = false;
            } elseif ($this->onlyForEU) {
                $this->shouldShowAgreementResult = $this->isVisitorFromEU();
            } else {
                $this->shouldShowAgreementResult = true;
            }
        }

        return $this->shouldShowAgreementResult;
    }

    /**
     * @param Controller|null $controller
     * @param bool $includeDefault
     *
     * @return string
     */
    private function generateCss(Controller $controller = null, $includeDefault = false)
    {
        if ($controller === null) {
            $controller = $this;
        }
        $textColor = $controller->textColor;
        if ($textColor === '') {
            $textColor = self::DEFAULT_TEXT_COLOR;
        }
        $linkColor = $controller->linkColor;
        if ($linkColor === '') {
            $linkColor = self::DEFAULT_LINK_COLOR;
        }
        $backgroundColor = $controller->backgroundColor;
        if ($backgroundColor === '') {
            $backgroundColor = self::DEFAULT_BACKGROUND_COLOR;
        }
        $default = '';
        if ($includeDefault) {
            $locator = $this->app->make(FileLocator::class);
            $locator->addPackageLocation('gdpr_cookie_notice');
            $record = $locator->getRecord(DIRNAME_BLOCKS . '/gdpr_cookie_notice/view.css');
            if ($record->exists()) {
                $default = $locator->getFilesystem()->get($record->getFile());
            }
        }

        return <<<EOT
{$default}
#gdpr_cookie_notice-{$controller->bID} {
    color: {$textColor};
    background-color: {$backgroundColor};
}
#gdpr_cookie_notice-{$controller->bID} a:link, #gdpr_cookie_notice-{$controller->bID} a:visited {
    color: {$linkColor};
}
#gdpr_cookie_notice-{$controller->bID} .gdpr_cookie_notice-close {
    color: {$textColor};
    border-color: {$textColor};
}
EOT
        ;
    }

    /**
     * @return string
     */
    private function getDefaultAgreeText()
    {
        return t('Ok');
    }
}
