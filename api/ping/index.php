<?php
$inputJSON = file_get_contents('php://input');
$stats = json_decode($inputJSON, TRUE); //convert JSON into array

echo json_encode($stats);
