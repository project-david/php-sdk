<?php
namespace GravityLegal\GravityLegalAPI;

class Country
{
    public string $numeric;
    public string $alpha2;
    public string $name;
    public string $emoji;
    public string $currency;
    public int $latitude;
    public int $longitude;
}

class BinData {
    public Number $number;
    public string $scheme;
    public string $type;
    public string $brand;
    public bool $prepaid;
    public Country $country;
}
