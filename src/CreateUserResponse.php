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
    public function GetCreatedUser(): User {
        $createdUser = new User();
        $createdUser->id = $this->id;
        $createdUser->createdOn = $this->createdOn;
        $createdUser->email = $this->email;
        $createdUser->firstName = $this->firstName;
        $createdUser->lastName = $this->lastName;
        $createdUser->updatedOn = $this->updatedOn;
        $createdUser->roleId = $this->role->id;
        $createdUser->appData = $this->app->appData;
        return $createdUser;
    }
}

class CreateUserResponse {
    public CreateUserResponseResult $result;
}
