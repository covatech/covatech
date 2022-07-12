<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\StatusAction;
use CovaTech\Payum\ZaloPay\ApiV2;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    /**
     * @dataProvider provideTransactionDetails
     */
    public function testHumanStatus(array $detail, string $status)
    {
        $act = new StatusAction();
        $act->execute($humanStatus = new GetHumanStatus($detail));

        $this->assertEquals($humanStatus->getValue(), $status);
    }

    public function provideTransactionDetails(): array
    {
        return [
            'success' => [
                'detail' => [
                    'return_code' => ApiV2::SUCCESS,
                ],
                'status' => GetHumanStatus::STATUS_CAPTURED
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

    public function provideNotSupportedRequests(): \Iterator
    {
        yield from parent::provideNotSupportedRequests();
        yield [new GetHumanStatus(['m_refund_id' => 1])];
    }
}