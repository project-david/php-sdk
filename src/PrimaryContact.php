<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class PrimaryContact   {
        public string $id;
        public DateTime $createdOn;
        public ?DateTime $updatedOn;
        public bool $deleted;
        public string $firstName;
        public string $lastName;
        public string $fullName;
        public ?string $email;
        public ?string $phone;
        public bool $isPrimaryContact;
}
