<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Page\Admin;

use Sylius\Behat\Page\Admin\Order\IndexPage;

final class OrderIndexPage extends IndexPage implements OrderIndexPageInterface
{
    public function createOrder(): void
    {
        $this->getDocument()->clickLink('Create');
    }

    public function countOrders(array $parameters): int
    {
        try {
            $rows = $this->tableAccessor->getRowsWithFields($this->getElement('table'), $parameters);

            return count($rows);
        } catch (\Exception $exception) {
            return 0;
        }
    }
}
