<?php
namespace GravityLegal\GravityLegalAPI;

class Role {
    public string $id;
    public string $name;
    public string $description;
    public string $origin;
    public array $ops; // This is a list of strings
    public array $entities; // This is a list of strings
    public ?AppData $appData;
}
