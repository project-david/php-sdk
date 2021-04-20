<?php
namespace GravityLegal\GravityLegalAPI;

use DateTime;

class SystemTokenCreationResponseResult  {
    public string $name;
    public User $user;
    public string $token;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $active;
}

class SystemTokenCreationResponse
{
    public SystemTokenCreationResponseResult $result;
}