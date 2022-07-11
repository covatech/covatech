<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @link https://docs.zalopay.vn/v2/payments/gateway/overview.html
 */
final class ApiV2
{
    public const SUCCESS = 1;

    public const FAIL = 2;

    public const PROCESSING = 3;

    public function __construct(
        private array $options,
        private ClientInterface $httpClient,
        private MessageFactory $messageFactory
    ) {
        $options = ArrayObject::ensureArrayObject($this->options);
        $options->validateNotEmpty(['app_id', 'key1', 'key2', 'sandbox']);
    }

    public function createOrder(array $fields): array
    {
        $fields['app_id'] = $this->options['app_id'];
        $fields['app_time'] ??= time() * 1000;
        $fields['mac'] = $this->generateMac(
            $fields,
            ['app_id', 'app_trans_id', 'app_user', 'amount', 'app_time', 'embed_data', 'item']
        );
        $request = $this->createHttpMessage('/create', $fields);

        return $this->doRequest($request);
    }

    public function quickPay(array $fields): array
    {
        $fields['app_id'] = $this->options['app_id'];
        $fields['app_time'] ??= time() * 1000;
        $fields['mac'] = $this->generateMac(
            $fields,
            ['app_id', 'app_trans_id', 'app_user', 'amount', 'app_time', 'embed_data', 'item', 'payment_code']
        );
        $fields['payment_code'] = $this->publicEncrypt($fields['payment_code']);

        $request = $this->createHttpMessage('/quick_pay', $fields);

        return $this->doRequest($request);
    }

    public function getListMerchantBanks(array $fields): array
    {
        $fields['appid'] = $this->options['app_id'];
        $fields['reqtime'] ??= time() * 1000;
        $fields['mac'] = $this->generateMac($fields, ['appid', 'reqtime']);

        $request = $this->createHttpMessage('/getlistmerchantbanks', $fields);

        return $this->doRequest($request);
    }

    public function queryTransaction(array $fields): array
    {
        $fields['app_id'] = $this->options['app_id'];
        $fields['key1'] = $this->options['key1'];
        $fields['mac'] = $this->generateMac($fields, ['app_id', 'app_trans_id', 'key1']);

        unset($fields['key1']);

        $request = $this->createHttpMessage('/query', $fields);

        return $this->doRequest($request);
    }

    public function refund(array $fields): array
    {
        $fields['app_id'] = $this->options['app_id'];
        $fields['timestamp'] ??= time() * 1000;
        $fields['mac'] = $this->generateMac($fields, ['app_id', 'zp_trans_id', 'amount', 'description', 'timestamp']);

        $request = $this->createHttpMessage('/refund', $fields);

        return $this->doRequest($request);
    }

    public function queryRefund(array $fields): array
    {
        $fields['app_id'] = $this->options['app_id'];
        $fields['timestamp'] ??= time() * 1000;
        $fields['mac'] = $this->generateMac($fields, ['app_id', 'm_refund_id', 'timestamp']);

        $request = $this->createHttpMessage('/query_refund', $fields);

        return $this->doRequest($request);
    }

    public function verifyHttpBody(string $body): bool
    {
        $fields = json_decode($body, true);

        if (null === $fields) {
            throw new \InvalidArgumentException('Http body must be a json string.');
        }

        $fields = ArrayObject::ensureArrayObject($fields);

        if (false == $fields['mac'] || false == $fields['data']) {
            throw new \InvalidArgumentException('`mac` and `data` fields should be exist in http body.');
        }

        return hash_hmac('sha256', $fields['data'], $this->options['key2']) === $fields['mac'];
    }

    private function createHttpMessage(string $path, array $fields): RequestInterface
    {
        $uri = $this->getUri($path);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $body = http_build_query($fields);

        return $this->messageFactory->createRequest('POST', $uri, $headers, $body);
    }

    private function getUri(string $path): string
    {
        if (str_starts_with($path, '/getlistmerchantbanks')) {
            $baseUri = $this->options['sandbox'] ? 'https://sbgateway.zalopay.vn/api' : 'https://gateway.zalopay.vn/api';
        } else {
            $baseUri = $this->options['sandbox'] ? 'https://sb-openapi.zalopay.vn/v2' : 'https://openapi.zalopay.vn/v2';
        }

        return $baseUri . $path;
    }

    private function doRequest(RequestInterface $request): array
    {
        $response = $this->httpClient->sendRequest($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    private function generateMac(array $fields, array $only = null): string
    {
        $plainText = '';
        $only ??= array_keys($fields);
        $fields = ArrayObject::ensureArrayObject($fields);

        $fields->validateNotEmpty($only);

        foreach ($only as $name) {
            $plainText .= sprintf('|%s', $fields[$name]);
        }

        return hash_hmac('sha256', ltrim($plainText, '|'), $this->options['key1']);
    }

    private function publicEncrypt(string $data): string
    {
        if (!isset($this->options['public_key'])) {
            throw new \LogicException('`public_key` must be set to encrypt data.');
        }

        try {
            $isSuccessful = openssl_public_encrypt($data, $encryptedData, $this->options['public_key']);
        } catch (\Exception) {
            $isSuccessful = false;
        }

        if (false === $isSuccessful) {
            throw new \RuntimeException('Fail to encrypt data with `public_key` given.');
        }

        return base64_encode($encryptedData);
    }
}