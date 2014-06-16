<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\Provider\Provider;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\Segment;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class Country extends Base
{    
    protected $fieldName = 'location_country';
    protected $fieldType = 'CHAR(3) NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('countryCode');
        $segment->setName('UserCountry_Country');
        $segment->setAcceptedValues('de, us, fr, in, es, etc.');
        $this->addSegment($segment);

        $segment = new Segment();
        $segment->setSegment('continentCode');
        $segment->setName('UserCountry_Continent');
        $segment->setSqlFilter('Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent');
        $segment->setAcceptedValues('eur, asi, amc, amn, ams, afr, ant, oce');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_Country');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $this->getUrlOverrideValueIfAllowed('country', $request);

        if ($value !== false) {
            return $value;
        }

        $userInfo = $this->getUserInfo($request, $visitor);
        $country  = $this->getLocationDetail($userInfo, LocationProvider::COUNTRY_CODE_KEY);

        if (!empty($country) && $country != Visit::UNKNOWN_CODE) {

            return strtolower($country);
        }

        $country = $this->getCountryUsingProviderExtensionIfValid($userInfo['ip']);

        if (!empty($country)) {
            return $country;
        }

        return Visit::UNKNOWN_CODE;
    }

    private function getCountryUsingProviderExtensionIfValid($ipAddress)
    {
        $hostname = $this->getHost($ipAddress);
        $hostnameExtension = Provider::getCleanHostname($hostname);

        $hostnameDomain = substr($hostnameExtension, 1 + strrpos($hostnameExtension, '.'));
        if ($hostnameDomain == 'uk') {
            $hostnameDomain = 'gb';
        }

        if (array_key_exists($hostnameDomain, Common::getCountriesList())) {
            return $hostnameDomain;
        }

        return false;
    }

    /**
     * Returns the hostname given the IP address string
     *
     * @param string $ip IP Address
     * @return string hostname (or human-readable IP address)
     */
    private function getHost($ip)
    {
        return trim(strtolower(@IP::getHostByAddr($ip)));
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getUrlOverrideValueIfAllowed('country', $request);
    }
}