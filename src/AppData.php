<?php
namespace GravityLegal\GravityLegalAPI;

class Currency {
    public string $value;
    public string $label;
}

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