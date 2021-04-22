<?php
namespace GravityLegal\GravityLegalAPI;

class CreatePaylink {
    public string $client;
    public string $customer;
    public ?string $matter;
    public ?DefaultDepositAccounts $defaultDepositAccounts;
    public ?string $memo;
    public ?string $externalId;
    public Operating $operating;
    public ?array $paymentMethods; // This is a list of strings
    public bool $surchargeEnabled;
    public Trust $trust;
}
