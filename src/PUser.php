<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;


class PUser {
    public string $email;
    public array $userOrgs; // array of UserOrg
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public string $firstName;
    public string $lastName;
    public ?string $phoneNo;
    public string $initials;
    public ?string $subId;
    public ?string $type;
    public ?AppData $appData;
    public bool $isProfilePicChanged;
    public ?string $profileData;
}