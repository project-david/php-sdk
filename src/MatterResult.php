<?php
namespace GravityLegal\GravityLegalAPI;

class MatterResultResult {
    public array $records; // array of Matter
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class MatterResult {
    public MatterResultResult $result;
}
