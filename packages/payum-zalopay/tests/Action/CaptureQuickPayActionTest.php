<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\CaptureQuickPayAction;
use CovaTech\Payum\ZaloPay\Request\Api\DoQuickPay;
use CovaTech\Payum\ZaloPay\Request\CaptureQuickPay;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class CaptureQuickPayActionTest extends GenericActionTest
{
    protected $actionClass = CaptureQuickPayAction::class;

    protected $requestClass = CaptureQuickPay::class;

    public function testExecuteDoQuickPay()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new CaptureQuickPayAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(DoQuickPay::class, $req);
                    $this->assertEquals(1, $req->getModel()['amount']);
                }
            );

        $act->execute(new CaptureQuickPay(['amount' => 1]));
    }
}