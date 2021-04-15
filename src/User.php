<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class User {
    public Customer $customer;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public bool $deleted;
    public string $puserId;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $phoneNo;
    public string $initials;
    public string $subId;
    public string $type;
    public AppData $appData;
    public bool $isProfilePicChanged;
    public string $profileData;
    public string $filterData;
    public bool $sysGen;
    public string $roleId;
}

class CreateUser {
    /// <summary>
    /// First name of the person being invited
    /// </summary>
    public string $firstName;
    /// <summary>
    /// Last name of the person being invited
    /// </summary>
    public string $lastName;
    /// <summary>
    /// puserid Guid of the person who is sending the invitation
    /// It will be most likely a person with Admin role at the partner company
    /// enrolling the customer
    /// </summary>
    public string $invitedBy; //puserid Guid
    /// <summary>
    /// Email address of the person being invited
    /// </summary>
    public string $email; //email address of the person 
    /// <summary>
    /// Status should be set to "OUTSTANDING"
    /// </summary>
    public string $status;
    /// <summary>
    /// Organization Id of the customer with whome the person being invited would be associated with
    /// </summary>
    public string $org;
    /// <summary>
    /// The role the invited person would assume once s/he logs on
    /// The options are user and admin
    /// </summary>
    public string $role;
    /// <summary>
    /// The app name for the partner.
    /// For Soluno it is "soluno"
    /// </summary>
    public string $app;
    /// <summary>
    /// The destination web site where the person will go to login
    /// For the sandbox environment it is
    /// "https://app.sandbox.gravity-legal.com"
    /// </summary>
    public string $website;
}
class CreateUserResponseResult {
    public string $firstName;
    public string $lastName;
    public User $invitedBy;
    public string $email;
    public string $status;
    public Org $org;
    public Role $role;
    public App $app;
    public string $website;
    public string $existingUserId;
    public DateTime $fulfilledOn;
    public string $emailMessageId;
    public string $mailStatus;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
}

class CreateUserResponse {
    public CreateUserResponseResult $result;
}
class App {
    public string $id;
    public string $title;
    public string $uiId;
    public string $origin;
    public AppData $appData;
}

class Org {
    public array $apps; // This is a list of App
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public bool $deleted;
    public string $name;
    public string $origin;
    public string $type;
    public AppData $appData;
}

class Role {
    public string $id;
    public string $name;
    public string $description;
    public string $origin;
    public array $ops; // This is a list of strings
    public array $entities; // This is a list of strings
    public AppData $appData;
}

class InvitedUser {
    public App $app;
    public Org $org;
    public string $fullName;
    public Role $role;
    public DateTime $updatedOn;
    public string $id;
    public DateTime $createdOn;
    public string $existingUserId;
    public string $status;
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $website;
    public DateTime $fulfilledOn;
    public string $emailMessageId;
    public string $mailStatus;
}

class UserInviteResponseResult {
    public array $records; // This is a list of InvitedUser
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class UserInviteResponse {
    public UserInviteResponseResult $result;
}
class UserResultResult {
    public array $records; // This is a list of User
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}
class UserResult {
    public UserResultResult $result;
}
class GetUserResult {
    public User $result;
}
