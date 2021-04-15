<?php
namespace GravityLegal\GravityLegalAPI;

class UserResult {
    public UserResultResult $result;
}
class UserResultResult {
    public array $records; // This is a list of User
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

