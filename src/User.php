<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;
use JsonMapper;

class User {
    public Customer $customer;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $deleted;
    public string $puserId;
    public string $firstName;
    public string $lastName;
    public string $email;
    public ?string $phoneNo;
    public ?string $initials;
    public ?string $subId;
    public ?string $type;
    public ?AppData $appData;
    public bool $isProfilePicChanged;
    public ?string $profileData;
    public ?string $filterData;
    public bool $sysGen;
    public ?string $roleId;
}

class GetUserResult {
    public User $result;
}
