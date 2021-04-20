<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class InvitedUser {
    public App $app;
    public Org $org;
    public string $fullName;
    public Role $role;
    public ?DateTime $updatedOn;
    public string $id;
    public DateTime $createdOn;
    public ?string $existingUserId;
    public string $status;
    public string $email;
    public string $firstName;
    public string $lastName;
    public ?string $website;
    public ?DateTime $fulfilledOn;
    public ?string $emailMessageId;
    public ?string $mailStatus;
}
