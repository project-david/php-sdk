<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Contact {
    public Client $client;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public string $firstName;
    public ?string $lastName;
    public string $email;
    public ?string $phone;
    public bool $isPrimaryContact;
}
