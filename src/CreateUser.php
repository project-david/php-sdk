<?php
namespace GravityLegal\GravityLegalAPI;

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
    public ?string $website;
}
