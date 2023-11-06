<?php

namespace Alby;

require_once "contracts/AlbyClient.php";

use \GuzzleHttp;
use Alby\Contracts\AlbyClient;

class Client implements AlbyClient
{
  private $client;
  private $access_token;
  private $refresh_token;

  public function __construct($access_token)
  {
    $this->url = "https://api.getalby.com";
    $this->access_token = $access_token;
  }

  // deprecated
  public function init()
  {
    return true;
  }

  private function request($method, $path, $body = null)
  {
    $headers = [
      "Accept" => "application/json",
      "Content-Type" => "application/json",
      "Access-Control-Allow-Origin" => "*",
      "Authorization" => "Bearer {$this->access_token}",
      "User-Agent" => "alby-php",
    ];

    $requestBody = $body ? json_encode($body) : null;
    $request = new GuzzleHttp\Psr7\Request(
      $method,
      $path,
      $headers,
      $requestBody
    );
    $response = $this->client()->send($request);
    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
      $responseBody = $response->getBody()->getContents();
      return json_decode($responseBody, true);
    } else {
      // raise exception
    }
  }

  public function getInfo(): array
  {
    $data = $this->request("GET", "/user/me");
    $data["alias"] = "🐝 getalby.com";
    return $data;
  }

  public function getBalance()
  {
    $data = $this->request("GET", "/balance");
    return $data;
  }

  private function client()
  {
    if ($this->client) {
      return $this->client;
    }
    $options = ["base_uri" => $this->url, 'timeout' => 10];
    $this->client = new GuzzleHttp\Client($options);
    return $this->client;
  }

  public function isConnectionValid(): bool
  {
    return !empty($this->access_token);
  }

  public function addInvoice($invoice): array
  {
    $params = [ "amount" => $invoice["value"], "memo" => $invoice["memo"] ];
    if (array_key_exists("description_hash", $invoice) && !empty($invoice["description_hash"])) {
      $params['description_hash'] = $invoice['description_hash'];
    }
    if (array_key_exists("unhashed_description", $invoice) && !empty($invoice["unhashed_description"])) {
      $params['description'] = $invoice['unhashed_description'];
    }
    $data = $this->request("POST", "/invoices", $params);
    $data["id"] = $data["payment_hash"];
    $data["r_hash"] = $data["payment_hash"];
    return $data;
  }

  public function getInvoice($rHash): array
  {
    $invoice = $this->request("GET", "/invoices/{$rHash}");
    return $invoice;
  }

  public function isInvoicePaid($rHash): bool
  {
    $invoice = $this->getInvoice($rHash);
    return $invoice["settled"];
  }
}
