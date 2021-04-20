<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class CreateUserResponseResult {
    public string $firstName;
    public string $lastName;
    public User $invitedBy;
    public string $email;
    public string $status;
    public Org $org;
    public Role $role;
    public App $app;
    public ?string $website;
    public ?string $existingUserId;
    public ?DateTime $fulfilledOn;
    public ?string $emailMessageId;
    public ?string $mailStatus;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
}

class CreateUserResponse {
    public CreateUserResponseResult $result;
}
