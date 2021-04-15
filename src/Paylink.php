<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Paylink {
    public Customer $customer;
    public Client $client;
    public Matter $matter;
    public int $outstanding;
    public Balance $balance;
    public int $paid;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public Balance $balanceStatement;
    public DefaultDepositAccounts $defaultDepositAccounts;
    public string $externalId;
    public bool $isProcessing;
    public DateTime $latestActivity;
    public string $note;
    public string $memo;
    public string $paymentMethods; // This is a list of strings
    public string $status;
    public bool $surchargeEnabled;
    public string $url;
    public string $webhookDetails;
}
class Operating
{
    public int $amount;
}

class Trust
{
    public int $amount;
}

class CreatePaylink
{
    public string $client;
    public string $customer;
    public string $matter;
    public DefaultDepositAccounts $defaultDepositAccounts;
    public string $memo;
    public string $externalId;
    public Operating $operating;
    public array $paymentMethods; // This is a list of strings
    public bool $surchargeEnabled;
    public Trust $trust;
}
class PaylinkInfo {
    public Client $client;
    public Customer $customer;
    public Matter $matter;
    public DefaultDepositAccounts $defaultDepositAccounts;
    public string $memo;
    public array $paymentMethods; // This is a list of strings
    public bool $surchargeEnabled;
    public Balance $balanceStatement;
    public string $externalId;
    public string $note;
    public string $url;
    public string $webhookDetails;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public bool $deleted;
    public bool $isProcessing;
    public DateTime $latestActivity;
    public string $status;
}

class CreatePaylinkResponse {
    public PaylinkInfo $result;
}
