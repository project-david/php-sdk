<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class SystemTokenRecordUser {
    public string $id;
    public array $userOrgs; //array of UserOrg
}
class SystemTokenRecord {
    public string $token;
    public SystemTokenRecordUser $user;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public string $name;
    public bool $active;
}
