<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
echo json_encode([
    "success" => true,
    "configured" => true,
    "environment" => "production", 
    "public_key" => "APP_USR-5afce1ba-5244-42d4-939e-f9979851577",
    "domain" => $_SERVER['HTTP_HOST'] ?? "localhost"
]);
?>
