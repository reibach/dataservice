<?php 


$to = "guenter@federa.de";
$subject = "test out ";
$message = "asdfas  f fas";
$from = "guenter@federa.de"; 
//$from = "testme@breitband-nds.de"; 
$header = 'From: '.$from. "\r\n" .
    'Reply-To: '.$from. "\r\n" .
    'X-Mailer: PHP/' . phpversion();


/*
Oct 19 08:45:13 localhost postfix/smtp[5158]: 6B7D31205DC: to=<guenter@federa.de>, relay=smtp.breitband-nds.de[138.201.134.250]:25, 
* delay=59152, delays=59141/0.01/1.2/10, dsn=4.1.8, status=deferred (host smtp.breitband-nds.de[138.201.134.250] said: 450 4.1.8 
* <www-data@datahub.bzn.local>: Sender address rejected: 
* Domain not found (in reply to RCPT TO command))
*/
$envelope_sender_address = '-ftestme@breitband-nds.de';


//mail('guenter@federa.de', 'Betreff', 'Nachricht', null,
   //'-ftestme@breitband-nds.de');

// EMail senden
sendMail($to,$subject,$message,$header,$envelope_sender_address);


// EMail versenden
function sendMail($to,$subject,$message,$header, $envelope_sender_address) {
	global $error;
	if (!isset($to))
		$to = "testme@breitband-nds.de"; //Mailadresse Empfaenger
	if (!isset($subject))
		$subject    = "BZN-Daten ";
	
	if (!isset($message)) {
		$message   = "Inhalt einer Mail zum Test von PHP ";
		$message   .= "mit den deutschen Sonderzeichen öäüß";
	}
	
	if (!isset($from))
		$from   = "testme@breitband-nds.de";

	//if (DEBUG == 1)
		//echo "E-Mail mit Umlauten wurde gesendet!";   
	
	if (@mail($to,$subject,$message,$header, $envelope_sender_address)) { 
		// $message =  "<pre>".$message.$filenames."</pre>";
		// doMsg($message);
	} else {
		$error = 1;
		$message .= "<p>EMail konnte nicht gesendet werden.</p>";
		doMsg($message);
	}
}

// phpinfo();
?>
