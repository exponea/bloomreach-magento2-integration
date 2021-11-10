<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Order;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Address\GetCountryName;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Address;

/**
 * The class is responsible for rendering the shipping_country field
 */
class ShippingCountry implements RenderInterface
{
    /**
     * @var GetCountryName
     */
    private $getCountryName;

    /**
     * @param GetCountryName $getCountryName
     */
    public function __construct(GetCountryName $getCountryName)
    {
        $this->getCountryName = $getCountryName;
    }

    /**
     * Render the value of order field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        $countryName = '';
        /** @var Address $shippingAddress */
        $shippingAddress = $entity->getShippingAddress();

        if ($shippingAddress) {
            $countryId = $shippingAddress->getCountryId();
            $countryName = $countryId ? $this->getCountryName->execute($countryId) : '';
        }

        return $countryName;
    }
}
