<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Request\Api;

use CovaTech\Payum\ZaloPay\Request\Api\CreateOrder;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class CreateOrderTest extends TestCase
{
    public function testIsSubClassOfGeneric()
    {
        $this->assertTrue(is_a(CreateOrder::class, Generic::class, true));
    }
}