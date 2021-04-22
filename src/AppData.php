<?php
namespace GravityLegal\GravityLegalAPI;

class Logo {
    public string $name;
    public string $s3Key;
    public string $type;
}

class AppData {
    public int $Id;
    public Logo $logo;
    public Currency $currency;
}