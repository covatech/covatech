<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\RefundStatusAction;
use CovaTech\Payum\ZaloPay\ApiV2;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class RefundStatusActionTest extends GenericActionTest
{
    protected $actionClass = RefundStatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    /**
     * @dataProvider provideRefundDetails
     */
    public function testHumanStatus(array $detail, string $status)
    {
        $detail['m_refund_id'] = 1;

        $act = new RefundStatusAction();
        $act->execute($humanStatus = new GetHumanStatus($detail));

        $this->assertEquals($humanStatus->getValue(), $status);
    }

    public function provideRefundDetails(): array
    {
        return [
            'success' => [
                'detail' => [
                    'return_code' => ApiV2::SUCCESS,
                ],
                'status' => GetHumanStatus::STATUS_REFUNDED
            ],
            'failure' => [
                'detail' => [
                    'return_code' => ApiV2::FAIL,
                ],
                'status' => GetHumanStatus::STATUS_FAILED
            ],
            'pending' => [
                'detail' => [
                    'return_code' => ApiV2::PROCESSING,
                ],
                'status' => GetHumanStatus::STATUS_PENDING
            ],
            'unknown' => [
                'detail' => [
                    'return_code' => -1
                ],
                'status' => GetHumanStatus::STATUS_UNKNOWN
            ]
        ];
    }

    public function provideSupportedRequests(): \Iterator
    {
        foreach (parent::provideSupportedRequests() as $args) {
            $args[0]->getModel()['m_refund_id'] = 1;

            yield $args;
        }
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield from parent::provideNotSupportedRequests();
        yield [new GetHumanStatus([])];
    }
}