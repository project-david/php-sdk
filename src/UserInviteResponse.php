<?php
namespace GravityLegal\GravityLegalAPI;

class UserInviteResponseResult {
    public array $records; // This is a list of InvitedUser
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class UserInviteResponse {
    public UserInviteResponseResult $result;
}
