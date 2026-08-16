<?php

function getToday()
{
    return date('Y-m-d');
}

function getYesterday()
{
    return date('Y-m-d', strtotime('-1 day'));
}
