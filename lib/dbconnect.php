<?php

$host='localhost';
$db='adise_quarto_db';

require_once "config_local.php"

$user=$DB_USER;
$pass=$DB_PASS;



if (gethostname()=='users.iee.ihu.gr'){
    $mysqli= new $mysqli($host,$user,$pass,$db,null,'/home/student/it/2017/it174881') ;

}else{
    $mysqli= new $mysqli($host,$user,$pass,$db);
}


if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL:  (" .  
    $mysqli->connect_errno . ") " . $mysqli->connect_error;

}


?>