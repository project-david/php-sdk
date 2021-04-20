<?php
namespace GravityLegal\GravityLegalAPI;

class PUserResponseResult    {
    public array $records; // array of User
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class PUserResponse {
    public PUserResponseResult $result;
}
