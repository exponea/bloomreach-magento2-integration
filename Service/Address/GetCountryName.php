<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Address;

use Magento\Directory\Model\CountryFactory;

/**
 * The class is responsible for retrieving country name by country code
 */
class GetCountryName
{
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var array
     */
    private $countryCache = [];

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(CountryFactory $countryFactory)
    {
        $this->countryFactory = $countryFactory;
    }

    /**
     * Retrieve country name
     *
     * @param string $countryCode
     *
     * @return string
     */
    public function execute(string $countryCode): string
    {
        if (!array_key_exists($countryCode, $this->countryCache)) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $this->countryCache[$countryCode] = $country ? (string) $country->getName() : '';
        }

        return $this->countryCache[$countryCode];
    }
}
