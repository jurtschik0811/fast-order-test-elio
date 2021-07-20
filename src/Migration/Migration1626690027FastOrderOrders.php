<?php declare(strict_types=1);

namespace Js\FastOrder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1626690027FastOrderOrders extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1626690027;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `fast_order_orders` (
                `id`                BINARY(16),
                `created_at`        DATETIME(3) NULL,
                `updated_at`        DATETIME(3) NULL,
                `session_id`        VARCHAR(255) NULL,
                `orders`            JSON,
                `quantity_total`    INT(5),
                `price_total`       DOUBLE(10, 2),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB
                DEFAULT CHARSET=utf8mb4
                COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
