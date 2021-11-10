<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Customer;

use Bloomreach\EngagementConnector\Model\Customer\AddressResolver;
use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for render region field for customer
 */
class Region implements RenderInterface
{
    /**
     * @var AddressResolver
     */
    private $addressResolver;

    /**
     * @param AddressResolver $addressResolver
     */
    public function __construct(AddressResolver $addressResolver)
    {
        $this->addressResolver = $addressResolver;
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
        $region = '';

        if ($entity instanceof CustomerInterface) {
            $address = $this->addressResolver->getAddressForCustomerInterface($entity);
            $region = $address ? $address->getRegion() : '';
            return $region ? $region->getRegion() : '';
        } elseif (($entity instanceof Customer) && $entity->getEntityId()) {
            $address = $this->addressResolver->getAddressForCustomer($entity);
            $region = $address ? $address->getRegion() : $entity->getData($fieldCode);
        }

        return is_object($region) || is_array($region) ? '' : (string) $region;
    }
}
