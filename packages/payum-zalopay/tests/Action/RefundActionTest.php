<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\RefundAction;
use CovaTech\Payum\ZaloPay\Request\Api\DoRefund;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class RefundActionTest extends GenericActionTest
{
    protected $actionClass = RefundAction::class;

    protected $requestClass = Refund::class;

    public function testExecuteDoGetListMerchantBanks()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new RefundAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(DoRefund::class, $req);
                    $this->assertEquals(1, $req->getModel()['amount']);
                }
            );

        $act->execute(new Refund(['amount' => 1]));
    }
}