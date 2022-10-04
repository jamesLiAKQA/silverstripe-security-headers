<?php

namespace Guttmann\SilverStripe;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;

class SecurityHeaderControllerExtension extends Extension
{

    public function onAfterInit()
    {
        $response = $this->owner->getResponse();

        $headersToSend = (array) Config::inst()->get('Guttmann\SilverStripe\SecurityHeaderControllerExtension', 'headers');
        $xHeaderMap = (array) Config::inst()->get('Guttmann\SilverStripe\SecurityHeaderControllerExtension', 'x_headers_map');
        $overrideCSP = (boolean)Config::inst()->get('Guttmann\SilverStripe\SecurityHeaderControllerExtension', 'override_via_cms');

        foreach ($headersToSend as $header => $value) {
            if (empty($value)) {
                continue;
            }

            if ($header === 'Content-Security-Policy') {
                if (!$this->browserHasWorkingCSPImplementation()) {
                    continue;
                }

                if ($overrideCSP && SiteConfig::current_site_config()->OverrideYML === 1) {
                    $customCSP = SiteConfig::current_site_config()->CustomCSP;
                    $response->addHeader($header, preg_replace('/\r|\n/', '', $customCSP));
                } else {
                    $response->addHeader($header, $value);
                }
            } else {
                $response->addHeader($header, $value);
            }

            if (isset($xHeaderMap[$header])) {
                foreach ($xHeaderMap[$header] as $xHeader) {
                    $response->addHeader($xHeader, $value);
                }
            }
        }
    }

    private function browserHasWorkingCSPImplementation()
    {
        $agent = $this->owner->getRequest()->getHeader('User-Agent') ?? '';
        $agent = strtolower($agent);

        if (strpos($agent, 'safari') === false) {
            return true;
        }

        $split = explode('version/', $agent);

        if (!isset($split[1])) {
            return true;
        }

        $version = trim($split[1]);
        $versions = explode('.', $version);

        if (isset($versions[0]) && $versions[0] <= 5) {
            return false;
        }

        return true;
    }

}
