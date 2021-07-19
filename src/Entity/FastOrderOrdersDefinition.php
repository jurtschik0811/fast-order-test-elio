<?php declare(strict_types=1);

namespace Js\FastOrder\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FastOrderOrdersDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fast_order_orders';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FastOrderOrdersEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FastOrderOrdersCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new DateTimeField('created_at', 'createdAt')),
            (new StringField('session_id', 'sessionID')),
            (new JsonField('orders', 'orders')),
            (new IntField('quantity_total', 'quantityTotal')),
            (new StringField('price_total', 'priceTotal'))
        ]);
    }
}
