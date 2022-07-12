<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Request\Api;

use CovaTech\Payum\ZaloPay\Request\Api\VerifyHttpBody;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class VerifyHttpBodyTest extends TestCase
{
    public function testCanGetBody()
    {
        $body = random_bytes(32);
        $req = new VerifyHttpBody($body, []);

        $this->assertEquals($body, $req->getBody());
    }

    public function testIsSubClassOfGeneric()
    {
        $this->assertTrue(is_a(VerifyHttpBody::class, Generic::class, true));
    }
}