<?php
session_start();
header('Content-type: application/json');
/*
* Upload-Progress Prefix auslesen und mit Wert aus
* dem hidden Feld unseres Formulars ergänzen
*/
$progress_name = ini_get("session.upload_progress.prefix")."test";

/*
* Wenn Upload abgebrochen werden soll
*/
if(isset($_GET['cancel']) && $_GET['cancel'] == "true")
{
    /*
    * Bricht den Upload ab
    */
    $_SESSION[$progress_name]['cancel_upload'] = true;

    return;
}

if(isset($_SESSION[$progress_name]))
{
    /*
    * array in JSON umwandeln und zurück geben
    */
    echo json_encode($_SESSION[$progress_name]);
}
?>
