<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Payment {
    public string $id;
    public Customer $customer;
    public Client $client;
    public ?Matter $matter;
    public Paylink $paylink;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $deleted;
    public int $amount;
    public int $amountProcessed;
    public string $processor;
    public array $splits; // Array of Split
}
