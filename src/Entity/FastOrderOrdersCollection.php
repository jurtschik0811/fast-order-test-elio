<?php declare(strict_types=1);

namespace Js\FastOrder\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(FastOrderOrdersEntity $entity)
 * @method void set(string $key, FastOrderOrdersEntity $entity)
 * @method FastOrderOrdersEntity[]    getIterator()
 * @method FastOrderOrdersEntity[]    getElements()
 * @method FastOrderOrdersEntity|null get(string $key)
 * @method FastOrderOrdersEntity|null first()
 * @method FastOrderOrdersEntity|null last()
 */
class FastOrderOrdersCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FastOrderOrdersEntity::class;
    }
}
