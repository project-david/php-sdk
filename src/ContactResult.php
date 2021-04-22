<?php
namespace GravityLegal\GravityLegalAPI;

class ContactResultResult {
    public array $records; // This is a list of Contact objects
    public int $totalCount ;
    public int $pageNo ;
    public int $pageSize ;
}

class ContactResult    {
        public ContactResultResult $result ;
}
