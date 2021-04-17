<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class PaylinkInfo {
    public Client $client;
    public Customer $customer;
    public ?Matter $matter;
    public ?DefaultDepositAccounts $defaultDepositAccounts;
    public ?string $memo;
    public ?array $paymentMethods; // This is a list of strings
    public bool $surchargeEnabled;
    public ?Balance $balanceStatement;
    public ?string $externalId;
    public ?string $note;
    public string $url;
    public ?string $webhookDetails;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $deleted;
    public bool $isProcessing;
    public DateTime $latestActivity;
    public string $status;
}
