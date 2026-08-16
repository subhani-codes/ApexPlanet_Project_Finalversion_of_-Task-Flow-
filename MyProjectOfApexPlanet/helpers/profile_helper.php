<?php

function completionRate($completed,$total){

    if($total==0){

        return 0;

    }

    return round(

        ($completed/$total)*100

    );

}