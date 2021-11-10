<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Customer;

use Bloomreach\EngagementConnector\Model\Customer\AddressResolver;
use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Address\GetCountryName;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for render country field for customer
 */
class Country implements RenderInterface
{
    /**
     * @var AddressResolver
     */
    private $addressResolver;

    /**
     * @var GetCountryName
     */
    private $getCountryName;

    /**
     * @param AddressResolver $addressResolver
     * @param GetCountryName $getCountryName
     */
    public function __construct(
        AddressResolver $addressResolver,
        GetCountryName $getCountryName
    ) {
        $this->addressResolver = $addressResolver;
        $this->getCountryName = $getCountryName;
    }

    /**
     * Render the value of entity type field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        $countryId = '';

        if ($entity instanceof CustomerInterface) {
            $address = $this->addressResolver->getAddressForCustomerInterface($entity);
            $countryId = $address ? $address->getCountryId() : '';
        } elseif (($entity instanceof Customer) && $entity->getEntityId()) {
            $address = $this->addressResolver->getAddressForCustomer($entity);
            $countryId = $address ? $address->getCountryId() : $entity->getData($fieldCode);
        }

        if (is_object($countryId) || is_array($countryId)) {
            return '';
        }

        return $countryId ? $this->getCountryName->execute($countryId) : '';
    }
}
