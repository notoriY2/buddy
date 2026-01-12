<?php
  $hostname = "127.0.0.1";
  $username = "root";
  $password = "";
  $dbname = "buddy";

  $conn = mysqli_connect($hostname, $username, $password, $dbname);//By rsa@9795
  if(!$conn){
    echo "Database connection error".mysqli_connect_error();
  }
?>
