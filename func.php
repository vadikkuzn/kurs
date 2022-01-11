<?php

include 'bd.php';


function firstUserAuth($user_id){
    global $pdo;
    $sql = $pdo->prepare("SELECT COUNT(1) `userID` FROM `users` WHERE `userID` = '{$user_id}'");
    $sql->execute();
    return $sql->fetchColumn();
}

//CREATE user
function createUser($user_id){
    global $pdo;

    $sql = ("INSERT INTO `users`(`userID`, `flag`) VALUES (?,?)"); // ? - это неуязвимость к sql инъекциям
    $query = $pdo->prepare($sql);
    $query->execute([(string)$user_id, 'notCreate']);
}


function getAll(){
    global $pdo;
    $sql = $pdo->prepare("SELECT * FROM `event`");
    $sql->execute();
    return $sql->fetchAll();
}


function getMyList($user_id){
    global $pdo;
    $sql = $pdo->prepare("SELECT * FROM `event` WHERE `creator_id` = '{$user_id}'");
    $sql->execute();
    return $sql->fetchAll();

}


function getFlag($user_id){
    global $pdo;
    $sql = $pdo->prepare("SELECT `flag` FROM `users` WHERE `userID` = '{$user_id}'");
    $sql->execute();
    return $sql->fetchAll();

}

//CREATE event
function createEvent($user_id, $text){
    global $pdo;
    $sql = ("INSERT INTO `event`(`name`,`description`,`creator_id`,`date`) VALUES(?,?,?,?)");
    $query = $pdo->prepare($sql);
    $query->execute([$text,"",$user_id,'0000-00-00 00:00:00']);
}




function selectEvent($delete_number){
    global $pdo;
    $sqll = $pdo->prepare("SELECT `creator_id` FROM `event` WHERE `id`= {$delete_number}");
    $sqll->execute();
    return $sqll->fetchAll();
}


//DELETE
function deleteEvent($delete_number){
    global $pdo;
    $sql = "DELETE FROM `event` WHERE `id` =?";
    $query = $pdo->prepare($sql);
    $query->execute([$delete_number]);
}


function getUpdateID($user_id){
    global $pdo;
    $sql = $pdo->prepare("SELECT `update_id` FROM `users` WHERE `userID` = '{$user_id}'");
    $sql->execute();
    return $sql->fetchAll();

}

//update
function createDescription($user_id, $text){
    global $pdo;
    $sql = "UPDATE `event` SET `description`= '{$text}' WHERE `id`=(SELECT max(`id`) FROM `event` WHERE `creator_id`='{$user_id}')";
    $query = $pdo->prepare($sql);
    return $query->execute([]);
}

function createDate($user_id, $text){
    global $pdo;
    $sql = "UPDATE `event` SET `date`= '{$text}' WHERE `id`=(SELECT max(`id`) FROM `event` WHERE `creator_id`='{$user_id}')";
    $query = $pdo->prepare($sql);
    return $query->execute([]);
}


function checkForUpdate($user_id, $text){
    global $pdo;
    $sql = $pdo->prepare("SELECT COUNT(1) FROM `event` WHERE (`creator_id` = '{$user_id}' AND `id` = '{$text}')");
    $sql->execute();
    return $sql->fetchColumn();
}

function setUpdateID($user_id, $text){
    global $pdo;
    $sql = "UPDATE `users` SET `update_id`= '{$text}' WHERE `userID`= '{$user_id}'";
    $query = $pdo->prepare($sql);
    $query->execute();
}

//UPDATES users FLAGS
function updateFlag($user_id, $flag){
    global $pdo;
    $sqll = "UPDATE `users` SET `flag`=? WHERE `userID`='{$user_id}'";
    $querys = $pdo->prepare($sqll);
    return $querys->execute([$flag]);

}



//UPDATES event
function updateEvent($field,$text, $upd_id){
    global $pdo;
    $sqll = "UPDATE `event` SET `{$field}`= '{$text}' WHERE `id`= '{$upd_id}'";
    $querys = $pdo->prepare($sqll);
    return $querys->execute();

}

function updateFlagNull($user_id){
    global $pdo;
    $sqll = "UPDATE `users` SET `flag`='notCreate', `update_id`= null WHERE `userID`='{$user_id}'";
    $querys = $pdo->prepare($sqll);
    return $querys->execute();

}

?>




















