<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Matter {
    public Client $client;
    public ?Client $secondClient;
    public int $outstanding;
    public ?Balance $balance;
    public int $paid;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public ?AppData $appData;
    public ?string $creatorId;
    public string $displayId;
    public ?string $externalId;
    public int $hourlyRate;
    public int $inprocess;
    public DateTime $latestActivity;
    public ?string $leadAttorneyId;
    public string $name;
    public string $status;
}
