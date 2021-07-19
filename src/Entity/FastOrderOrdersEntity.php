<?php declare(strict_types=1);

namespace Js\FastOrder\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FastOrderOrdersEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $orders;

    /**
     * @var string
     */
    protected $quantityTotal;

    /**
     * @var string
     */
    protected $priceTotal;

    /**
     * @var string
     */
    protected $sessionID;

    /**
     * @return string
     */
    public function getOrders(): string
    {
        return $this->orders;
    }

    /**
     * @param string $orders
     */
    public function setOrders(string $orders): void
    {
        $this->orders = $orders;
    }

    /**
     * @return string
     */
    public function getQuantityTotal(): string
    {
        return $this->quantityTotal;
    }

    /**
     * @param string $quantityTotal
     */
    public function setQuantityTotal(string $quantityTotal): void
    {
        $this->quantityTotal = $quantityTotal;
    }

    /**
     * @return string
     */
    public function getPriceTotal(): string
    {
        return $this->priceTotal;
    }

    /**
     * @param string $priceTotal
     */
    public function setPriceTotal(string $priceTotal): void
    {
        $this->priceTotal = $priceTotal;
    }

    /**
     * @return string
     */
    public function getSessionID(): string
    {
        return $this->sessionID;
    }

    /**
     * @param string $sessionID
     */
    public function setSessionID(string $sessionID): void
    {
        $this->sessionID = $sessionID;
    }
}
