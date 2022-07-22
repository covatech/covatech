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
use CovaTech\Payum\ZaloPay\ApiV2;
use CovaTech\Payum\ZaloPay\Request\Api\CreateOrder;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
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

        $gateway
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function ($req) {
                    $this->assertInstanceOf(CreateOrder::class, $req);
                    $this->assertInstanceOf(ArrayObject::class, $model = $req->getModel());
                    $this->assertEquals(1, $model['amount']);
                    $model['return_code'] = ApiV2::SUCCESS;
                }),
                $this->returnCallback(function ($req) {
                    $this->assertInstanceOf(Sync::class, $req);
                    $this->assertEquals(1, $req->getModel()['amount']);
                    $this->assertEquals(ApiV2::SUCCESS, $req->getModel()['return_code']);
                }),
            );
        $act->setGateway($gateway);
        $act->execute(new Capture(['amount' => 1]));
    }

    public function testExecuteCreateOrderSkipSyncRequestWhenFail()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new CaptureAction();

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(CreateOrder::class, $req);
                    $this->assertInstanceOf(ArrayObject::class, $model = $req->getModel());
                    $this->assertEquals(1, $model['amount']);
                    $model['return_code'] = ApiV2::FAIL;
                }
            );
        $act->setGateway($gateway);
        $act->execute(new Capture(['amount' => 1]));
    }
}