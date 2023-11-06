<?php

use Alby\Client;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function client(): Client
    {
        $access_token = '';
        return new Client($access_token);
    }
    public function testCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            Client::class,
            $this->client(),
        );
    }

    public function testCanGetBalance(): void
    {
        $client = $this->client();
        $client->init();
        $this->assertIsNumeric($client->getBalance()['balance']);
    }

    public function testCanGetInfo(): void
    {
        $client = $this->client();
        $client->init();
        $this->assertIsString($client->getInfo()['alias']);
    }

    public function testCanAddInvoice(): void
    {
        $client = $this->client();
        $client->init();
        $response = $client->addInvoice([
            'value' => 23,
            'memo' => 'test invoice'
        ]);
        // print_r($response);
        $this->assertIsString($response['payment_request']);
        $this->assertIsString($response['payment_hash']);
    }

    public function testCanGetInvoice(): void
    {
        $client = $this->client();
        $client->init();
        $response = $client->addInvoice([
            'value' => 23,
            'memo' => 'test invoice'
        ]);
        $invoice = $client->getInvoice($response['payment_hash']);

        $this->assertArrayHasKey('settled', $invoice);
    }
}