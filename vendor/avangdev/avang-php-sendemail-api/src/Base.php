<?php

namespace AvangPhpApi;

use \GuzzleHttp\Client;

class Base {

    /**
     *
     * @var string
     */
    private $_host;

    /**
     *
     * @var string
     */
    private $_key;

    public function __construct($host, $key) {
        $this->_host = $host;
        $this->_key = $key;
    }

    public function createRequest($controller, $action, $parameters) {
        $url = sprintf('%s/api/v1/%s/%s', $this->_host, $controller, $action);
        $headers = [
            'x-server-api-key' => $this->_key,
            'content-type' => 'application/json',
        ];
        $body = json_encode($parameters);
        $client = new Client;
        $response = $client->request('POST', $url, [
            'body' => $body,
            'headers' => $headers
        ]);
        if ($response->getStatusCode() === 200) {
            $result = $response->getBody()->getContents();
            $json = json_decode($result);
            if ($json->status == 'success') {
                return $json->data;
            } else {
                if (isset($json->data->code)) {
                    throw new \Exception(sprintf('[%s] %s', $json->data->code, $json->data->message));
                } else {
                    throw new \Exception($json->data->message);
                }
            }
        }
    }

}
