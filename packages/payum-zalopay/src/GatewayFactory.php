<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay;

use CovaTech\Payum\ZaloPay\Action\Api\CreateOrderAction;
use CovaTech\Payum\ZaloPay\Action\Api\DoGetListBankMerchantsAction;
use CovaTech\Payum\ZaloPay\Action\Api\DoQuickPayAction;
use CovaTech\Payum\ZaloPay\Action\Api\DoRefundAction;
use CovaTech\Payum\ZaloPay\Action\Api\QueryRefundAction;
use CovaTech\Payum\ZaloPay\Action\Api\QueryTransactionAction;
use CovaTech\Payum\ZaloPay\Action\CaptureAction;
use CovaTech\Payum\ZaloPay\Action\CaptureQuickPayAction;
use CovaTech\Payum\ZaloPay\Action\GetListMerchantBanksAction;
use CovaTech\Payum\ZaloPay\Action\RefundAction;
use CovaTech\Payum\ZaloPay\Action\RefundStatusAction;
use CovaTech\Payum\ZaloPay\Action\StatusAction;
use CovaTech\Payum\ZaloPay\Action\SyncAction;
use CovaTech\Payum\ZaloPay\Action\SyncRefundDetailAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as BaseGatewayFactory;

final class GatewayFactory extends BaseGatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(
            [
                'payum.factory_name' => 'zalopay',
                'payum.factory_title' => 'ZaloPay',
                'payum.action.capture' => new CaptureAction(),
                'payum.action.api.create_order' => new CreateOrderAction(),
                'payum.action.capture_quick_pay' => new CaptureQuickPayAction(),
                'payum.action.api.do_quick_pay' => new DoQuickPayAction(),
                'payum.action.sync' => new SyncAction(),
                'payum.action.api.query_transaction' => new QueryTransactionAction(),
                'payum.action.refund' => new RefundAction(),
                'payum.action.api.do_refund' => new DoRefundAction(),
                'payum.action.sync_refund_detail' => new SyncRefundDetailAction(),
                'payum.action.api.query_refund' => new QueryRefundAction(),
                'payum.action.status' => new StatusAction(),
                'payum.action.refund_status' => new RefundStatusAction(),
                'payum.action.get_list_merchant_banks' => new GetListMerchantBanksAction(),
                'payum.action.api.do_get_list_merchant_banks' => new DoGetListBankMerchantsAction()
            ]
        );

        if (false == $config['payum.api']) {
            $config['payum.required_options'] = ['app_id', 'key1', 'key2'];
            $config['payum.default_options'] = [
                'app_id' => '',
                'key1' => '',
                'key2' => '',
                'sandbox' => true,
                'public_key' => null
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $options = [
                    'app_id' => $config['app_id'],
                    'key1' => $config['key1'],
                    'key2' => $config['key2'],
                    'sandbox' => $config['sandbox'],
                    'public_key' => $config['public_key']
                ];

                return new ApiV2($options, $config['httplug.client'], $config['httplug.message_factory']);
            };
        }

        parent::populateConfig($config);
    }
}