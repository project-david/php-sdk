<?php
namespace GravityLegal\GravityLegalAPI\Facades;

use Illuminate\Support\Facades\Facade;

class GravityLegal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'gravity-legal';
    }
}