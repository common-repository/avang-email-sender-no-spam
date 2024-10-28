<?php

namespace AvangPhpApi;

class ComposeRawMessage {

    protected $client;
    public $attributes = [];

    public function __construct($client) {
        $this->client = $client;
        $this->attributes['rcpt_to'] = [];
    }

    public function mailFrom($address) {
        $this->attributes['mail_from'] = $address;
    }

    public function rcptTo($address) {
        $this->attributes['rcpt_to'][] = $address;
    }

    public function data($data) {
        $this->attributes['data'] = base64_encode($data);
    }

    public function send() {
        $result = $this->client->createRequest('send', 'raw', $this->attributes);

        return new ComposeResult($this->client, $result);
    }

}
