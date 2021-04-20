<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Paylink {
    public Customer $customer;
    public Client $client;
    public ?Matter $matter;
    public int $outstanding;
    public ?Balance $balance;
    public int $paid;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public ?Balance $balanceStatement;
    public ?DefaultDepositAccounts $defaultDepositAccounts;
    public ?string $externalId;
    public bool $isProcessing;
    public DateTime $latestActivity;
    public ?string $note;
    public ?string $memo;
    public ?array $paymentMethods; // This is a list of strings
    public string $status;
    public bool $surchargeEnabled;
    public string $url;
    public ?string $webhookDetails;
}
