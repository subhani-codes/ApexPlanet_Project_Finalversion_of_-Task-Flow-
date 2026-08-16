<?php

function logActivity($pdo,$user_id,$action,$task){

    $stmt=$pdo->prepare(

        "INSERT INTO activity_logs

        (user_id,action,task_title)

        VALUES(?,?,?)"

    );

    $stmt->execute([

        $user_id,

        $action,

        $task

    ]);

}