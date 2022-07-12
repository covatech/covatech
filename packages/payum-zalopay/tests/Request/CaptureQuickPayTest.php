<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Request;

use CovaTech\Payum\ZaloPay\Request\CaptureQuickPay;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class CaptureQuickPayTest extends TestCase
{
    public function testIsSubClassOfGeneric()
    {
        $this->assertTrue(is_a(CaptureQuickPay::class, Generic::class, true));
    }
}