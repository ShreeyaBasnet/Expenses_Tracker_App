<?php
$conn = new mysqli(
    "maglev.proxy.rlwy.net",
    "root", 
    "lsNfxbtDHAVsZaAVGLPrMfiAfWEPpUYk",
    "railway",
    28723
);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
