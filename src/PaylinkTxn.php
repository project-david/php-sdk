<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class TxnClient {
    public string $clientName;
    public PrimaryContact $primaryContact;
    public string $id;
}
class PaylinkTxn {
    public ?Balance $balance;
    public ?Customer $customer;
    public ?TxnClient $client;
    public ?Matter $matter;
    public ?string $envelope;
    public ?string $surcharge;
    public string $status;
    public DateTime $latestActivity;
    public int $outstanding;
    public int $paid;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public ?Balance $balanceStatement;
    public ?DefaultDepositAccounts $defaultDepositAccounts;
    public ?string $externalId;
    public bool $isProcessing;
    public ?string $note;
    public ?array $paymentMethods; //This is a list of strings
    public bool $surchargeEnabled;
    public ?string $url;
    public ?string $webhookDetails;
}
