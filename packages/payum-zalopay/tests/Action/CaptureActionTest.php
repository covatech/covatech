<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\CaptureAction;
use CovaTech\Payum\ZaloPay\Request\Api\CreateOrder;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

    public function testExecuteCreateOrder()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new CaptureAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(CreateOrder::class, $req);
                    $this->assertEquals(1, $req->getModel()['amount']);
                }
            );

        $act->execute(new Capture(['amount' => 1]));
    }
}