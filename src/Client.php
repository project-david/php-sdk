<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class PrimaryContact   {
        public string $id;
        public DateTime $createdOn;
        public DateTime $updatedOn;
        public bool $deleted;
        public string $firstName;
        public string $lastName;
        public string $fullName;
        public string $email;
        public string $phone;
        public bool $isPrimaryContact;
}
class Client {
    public ?string $creatorId;
    public Customer $customer;
    public array $contacts; // This is an array of Contact objects
    public int $outstanding;
    public PrimaryContact $primaryContact;
    public Balance $balance;
    public Balance $balanceStatement;
    public int $totalRevenue;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public bool $deleted;
    public ?AppData $appData;
    public ?DefaultDepositAccounts $depositAccounts;
    public string $clientName;
    public string $email;
    public ?string $externalId;
    public string $firstName;
    public string $lastName;
    public DateTime $latestActivity;
    public string $namespace;
    public ?string $phone;
    public string $status;
    public ?string $surCharge;
}

