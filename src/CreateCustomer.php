<?php
namespace GravityLegal\GravityLegalAPI;

class CreateCustomer
{
    public string $name;
    public string $partner;
    public AppData $appData;
    public bool $promptToCreateSPM;
    public NotifPrefs $notifPrefs;
}
