<?php

namespace LeKoala\CookieConsent;

use SilverStripe\i18n\i18n;
use SilverStripe\View\Requirements;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Control\Cookie;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;

/**
 * Add cookie consent to your website
 *
 * @link https://www.cookieconsent.com/
 * @link https://cookiesandyou.com/
 * @link https://www.cookiebot.com/en/gdpr-cookies/
 */
class CookieConsent
{
    use Configurable;

    // an array like {"strictly-necessary":true,"functionality":true,"tracking":false,"targeting":false}
    const COOKIE_CONSENT_LEVEL = 'cookie_consent_level';
    // true/false depending on sttate
    const COOKIE_CONSENT_ACCEPTED = 'cookie_consent_user_accepted';

    /**
     * @config
     * @var string
     */
    private static $use_cdn = false;

    /**
     * @config
     * @var string
     */
    private static $opts = [
        "notice_banner_type" => "interstitial",
        "consent_type" => "express",
        "palette" => "dark",
        "change_preferences_selector" => "#cookieconsent-preferences",
    ];

    /**
     * Add requirements
     *
     * Make sure to call this AFTER you have defined scripts that should be loaded conditionally
     * @link https://stackoverflow.com/questions/45794634/loading-google-analytics-after-page-load-by-appending-script-in-head-doesnt-alw
     * @return void
     */
    public static function requirements()
    {
        $SiteConfig = null;
        if (class_exists(SiteConfig::class)) {
            $SiteConfig = SiteConfig::current_site_config();
        }

        $conf = self::config();

        // options to pass to js constructor
        $opts = $conf->opts;

        // some options are autoconfigured
        $opts['language'] = self::getLanguage();
        // otherwise you can set it manually in yml
        if ($SiteConfig) {
            $opts['website_name'] = $SiteConfig->Title;
        }

        $privacyLink = 'https://cookiesandyou.com/';
        // If we have a privacy notice, use it!
        if ($conf->privacy_notice_class && class_exists($conf->privacy_notice_class)) {
            $privacyNotice = DataObject::get_one($conf->privacy_notice_class);
            if ($privacyNotice) {
                $privacyLink = $privacyNotice->Link();
            }
        }
        $opts['cookies_policy_url'] = $privacyLink;

        $jsonOpts = json_encode($opts);

        // Include script
        $use_cdn = self::config()->use_cdn;
        if ($use_cdn) {
            Requirements::javascript("//www.cookieconsent.com/releases/3.1.0/cookie-consent.js");
        } else {
            Requirements::javascript("lekoala/silverstripe-cookieconsent:javascript/cookie-consent-3.1.0.min.js");
        }

        // Include custom init
        $js = <<<JS
cookieconsent.run($jsonOpts);
JS;
        Requirements::customScript($js, 'CookiesConsentInit');
    }

    /**
     * Get a valid language based on current locale
     * @return string
     */
    public static function getLanguage()
    {
        $lang = substr(i18n::get_locale(), 0, 2);
        if (in_array($lang, self::getAvailableLanguages())) {
            return $lang;
        }
        return 'en';
    }

    /**
     * @return array
     */
    public static function getAvailableLanguages()
    {
        return [
            'en',
            'de',
            'fr',
            'es',
            'ca_es',
            'it',
            'nl',
            'pt',
            'fi',
            'hu',
            'cs',
            'hr',
            'da',
            'sl',
            'pl',
            'ro',
            'sr',
            'bg',
            'cy',
        ];
    }

    /**
     * Clear requirements, useful if you don't want any popup on a specific page after init
     *
     * @return void
     */
    public static function clearRequirements()
    {
        $use_cdn = self::config()->use_cdn;
        if ($use_cdn) {
            Requirements::clear("//www.cookieconsent.com/releases/3.1.0/cookie-consent.js");
        } else {
            Requirements::clear("lekoala/silverstripe-cookieconsent:javascript/cookie-consent-3.1.0.min.js");
        }
        Requirements::clear('CookiesConsentInit');
    }

    /**
     * @return bool
     */
    public static function isAllowed()
    {
        return (bool)Cookie::get(self::COOKIE_CONSENT_ACCEPTED);
    }

    /**
     * Helper method to set cookies if accepted
     *
     * @param string $name
     * @param string $value
     * @param integer $expiry
     * @param bool $httpOnly
     * @return void
     */
    public static function setCookie($name, $value, $expiry = 90, $httpOnly = true)
    {
        if (self::isAllowed()) {
            $secure = Director::is_https();
            $path = $domain = null;
            return Cookie::set($name, $value, $expiry, $path, $domain, $secure, $httpOnly);
        }
        return false;
    }

    /**
     * @return void
     */
    public static function clearStatus()
    {
        return Cookie::force_expiry(self::COOKIE_CONSENT_ACCEPTED);
    }

    /**
     * @return void
     */
    public static function forceAllow()
    {
        return Cookie::set(self::COOKIE_CONSENT_ACCEPTED, true, 90, null, null, false, false);
    }
}
