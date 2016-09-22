<?php 
header("Content-Type: text/json");
session_start(); 
include 'config.php';    

##### Start Funktionsaufrufe ###########################################
//Funktionsaufruf - Directory immer mit endendem / angeben
deleteFilesFromDirectory($tmpDir);

//Datei(en) hochladen
uploadFiles($upload_folder,$filenames);

/// Dateien als Zip packen 
Zip($upload_folder, './'.$zipfile);

// und ZipArchiv sichern und verschieben
saveFile($zipfile, '/opt/datahub/'.$zipfile);
moveFile($zipfile, 'download/'.$zipfile);

// Dateien loeschen, Directory immer mit endendem / angeben
deleteFilesFromDirectory($upload_folder);

// EMail erstellen
generateEmail($downloadlink,$downloadlinkhtml,$from);

### TESTFunktionen ###
// CheckDebug
// checkDebug($downloadlink,$downloadlinkhtml);

// CheckAnswer
 //generateTestMsg($downloadlink,$downloadlinkhtml);
//doMsg("as");
//echo json_encode($response);

// Formularfelder checken 
//checkFormFields();  
##### Ende Funktionsaufrufe ############################################


##### Start Funktionsdefinitionen ######################################
// tmp vorab loeschen
function deleteFilesFromDirectory($ordnername){
	//überprüfen ob das Verzeichnis überhaupt existiert
	if (is_dir($ordnername)) {
		//Ordner öffnen zur weiteren Bearbeitung
		if ($dh = opendir($ordnername)) {
			//Schleife, bis alle Files im Verzeichnis ausgelesen wurden
			while (($file = readdir($dh)) !== false) {
				//Oft werden auch die Standardordner . und .. ausgelesen, diese sollen ignoriert werden
				if ($file!="." AND $file !="..") {
					//Files vom Server entfernen
					unlink("".$ordnername."".$file."");
				}
			}
			//geöffnetes Verzeichnis wieder schließen
			closedir($dh);
		}
	}
}



// Datei(en) hochladen 
function uploadFiles($upload_folder,$filenames) {
	global $error;
	if (DEBUG == 1) {
	
		$msg = "<p></p>";
		$msg .= "<pre>";
		$msg .=  "FILES:<br />";
		//print_r ($_FILES );
		$msg .= print_r ($_FILES, true);
		$msg .= "ENDE FILES:<br /><br /><br />";
		//doMsg($msg);
		print ($msg);


		if ($_FILES['file']['error'][0] == 4)
			echo "Not OK"; 
		else
			echo "ok";
		

		foreach($_FILES['file']['error'] as $key => $value) {
			doMsg("key: ".$key . " => value: " . $value."<br>");
			if ($value == 4)
				print "Wrong";
			else	
				print "Wright";
		}		
	
	}

	$i = 0;
	global $filenames;
	foreach ($_FILES as $file) {
		
		if ($_FILES['file']['error'][0] == 4) {
			$error = 1;
			doMsg("Keine Datei  zum Hochladen ausgew&auml;hlt.");
		} else {  
			$file_count = count($file['name']);
			
			if (DEBUG == 1)
				doMsg("<p>Anzahl: <b>".$file_count."</b></p>");
		  
			for ($i=0; $i<$file_count; $i++) {		
				//$filenames .= "Datei ".$i." :".$file['name'][$i];  	
				if (DEBUG == 1) {
					print "Durchlauf: ".$i."<br>";
					print "Filename: ".$file['name'][$i]."<br>";
					print "Filetmp_name: ".$file['tmp_name'][$i]."<br>";
					print "Filetype: ".$file['type'][$i]."<br>";
					print "Filesize: ".$file['size'][$i]."<br>";
					print "FileError: ".$file['error'][$i]."<br>";
				} else {
					$filenames[] = $file['name'][$i];  
				}
					
					

				//Überprüfung der Dateiendung
				$extension = strtolower(pathinfo($file['name'][$i], PATHINFO_EXTENSION));

				$not_allowed_extensions = array('asexe', 'asssh', 'asbat', 'asasinc');
				if(in_array($extension, $not_allowed_extensions)) {
					$error = 1;
					doMsg("<p>ERROR: ".$file['name'][$i]." hat eine ungültige Dateiendung.</p><hr>");
				} else {
				
					//Pfad zum Upload
					$new_path = $upload_folder.$file['name'][$i];
					if (DEBUG == 1) 
						doMsg($new_path);
					
					//Alles okay, verschiebe Datei an neuen Pfad
					move_uploaded_file($file['tmp_name'][$i], $new_path);
					if (DEBUG == 1) 
						doMsg('<p>Datei erfolgreich hochgeladen: <a href="'.$new_path.'">'.$new_path.'</a></p><hr>');
				}
			}
			//doMsg("Keine Datei  zum Hochladen ausgew&auml;hlt.".$filenames);
			//exit;
			return $filenames;
		}
		
	}	
}


// ZIPArchive rekursive aus allen Dateien eines Verzeichnisses erstellen
function Zip($source, $destination) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

 
// Datei sichern
function saveFile($from, $to){
		global $error;     	
   //file_exists() - Existenz der Datei prüfen
   if(!file_exists($to)){
      //copy() - Datei kopieren
      if (!copy($from, $to)) {
		$error = 1;
		
        $msg = "<p>Ein Fehler ist aufgetreten, die Daei(en)konnten nicht gespeichert werden. Bitte versuchen Sie es noch einmal.</p>"; 
        $msg .= "Sollte das Problem wiederholt bestehen, so schreiben Sie uns bitte eine kurze Nachricht.<br>\n";
		doMsg($msg);
      }
   }
}  


// Datei verschieben
function moveFile($from, $to){
	global $error;
   //file_exists() - Existenz der Datei prüfen
   if(!file_exists($to)) {
		//copy() - Datei kopieren
		if (!copy($from, $to)) {
			$error = 1;
			doMsg("failed to copy $file...<br>\n");
		} else {
			//unlink() - Datei löschen
			unlink($from);
		}
	}
}  




//EMail zusammenbauen
function generateEmail($downloadlink,$downloadlinkhtml,$from) {
	global $team;
	global $error;
	global $filenames;
	
	$filenamesHtml = "";
	$filenamesEmail = "";
	
	foreach ($filenames as &$value) {
		$filenamesHtml .= "<br>".$value;
		$filenamesEmail .= "\r\n".$value;
	}
	
	// Username
	$username = htmlspecialchars($_POST["username"]);

	// Username
	$useremail = htmlspecialchars($_POST["useremail"]);

	$comments  = htmlspecialchars($_POST["comments"]);

	// EMailAdresse des Ansprechpartners
	$kontakt  = htmlspecialchars($_POST["kontakt"]);
	
	$email_address = "";
	

	foreach ($team as $key => $value) {
		if ($key == $kontakt) {
			$email_address = $value;	
			//break;
		}
	}

	$msg = "<p>Folgende Daten wurden gesendet:</p>";
	$msg .= "Name: ".$username;
	$msg .= "<br />EMail: ".$useremail;
	$msg .= "<br />TeamEmail: ".$email_address;
	$msg .= "<br />Ansprechpartner: ".$kontakt;
	$msg .= "<br />from: ".$from;

	$msg .= "<p>Nachricht: ".$comments."</p>";
	$msg .= "<br />Folgende Dateien wurden hochgeladen und als Zip-Archiv zusammengepackt:";
	$msg .= "<br />".$filenamesHtml;
	$msg .= "<br /><br />TextLink zum Herunterladen:";
	$msg .= "<br />".$downloadlink;
	$msg .= "<br /><br />HtmlLink zum Herunterladen:";
	$msg .= "<br />".$downloadlinkhtml;
	$msg .= "<p>Bitte beachten: Dieser Link ist nur begrenzte Zeit abrufbar</p>";


	$msgEmail = "Folgende Daten wurden gesendet:\r\n\r\n";
	$msgEmail .= "Name: ".$username."\r\n";
	$msgEmail .= "from: ".$from."\r\n";
	$msgEmail .= "EMail: ".$useremail."\r\n";
	$msgEmail .= "Ansprechpartner: ".$kontakt.$email_address."\r\n";
	$msgEmail .= "Nachricht: ".$comments."\r\n\r\n";
	$msgEmail .= "Folgende Dateien wurden hochgeladen und als Zip-Archiv zusammengepackt: \r\n";
	$msgEmail .= $filenamesEmail."\r\n\r\n";
	$msgEmail .= "TextLink zum Herunterladen: \r\n";
	$msgEmail .= $downloadlink."\r\n\r\n";
	$msgEmail .= "HtmlLink zum Herunterladen: \r\n";
	$msgEmail .= $downloadlinkhtml;
	$msgEmail .= "\r\n\r\n";
	$msgEmail .= "Bitte beachten: Dieser Link ist nur begrenzte Zeit abrufbar\r\n";

	if (DEBUG == 1) 
		doMsg($msgEmail);

	//if (DEBUG == 1) 
				//doMsg('Hello ' . htmlspecialchars($_POST["kontakt"]) . '!');


	$subject = "Daten BZN";	
	
	// EMail senden
	sendMail($email_address,$subject,$msgEmail,$from);
	
	
	// Antwort auf der Website ausgeben
	
	doMsg($msg);


}	

// EMail versenden
function sendMail($to,$subject,$message,$from) {
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

	$headers   = array();
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/plain; charset=iso-8859-1";
	$headers[] = "From: {$from}";
	// falls Bcc benötigt wird
	$headers[] = "Bcc: Resale <mittler@breitband-niedersachsen.de>";
	$headers[] = "Reply-To: {$from}";
	$headers[] = "Subject: {$subject}";
	$headers[] = "X-Mailer: PHP/".phpversion();

	if (DEBUG == 1)
		echo "E-Mail mit Umlauten wurde gesendet!";   
	
	if (@mail($to, $subject, $message,implode("\r\n",$headers))) { 
		// $message =  "<pre>".$message.$filenames."</pre>";
		// doMsg($message);
	} else {
		$error = 1;
		$message .= "<p>EMail konnte nicht gesendet werden.</p>";
		doMsg($message);
	}
}

function checkDebug($downloadlink,$downloadlinkhtml) {
	
	if (DEBUG == 1) {
		$msg = "<h3>DEBUGMODUS</h3>";
		generateTestMsg($downloadlink,$downloadlinkhtml);
	} else {	
	 doMsg();
	//exit;
}
}	
 

//Nachricht ausgeben und bei Fehler Programm stoppen
function doMsg($msg) {
		global $error;
				
		// $msg .= "<br><a href=\"https://82.198.194.112:4433/download/".$zipfile."\">Download-BZN-Daten</a>";;
		//$msg .= "<p><a href='index.php'><b>AAAZur&uuml;ck zum Formular</b></a></p>";
		echo json_encode($msg);
		if ($error == 1)
			exit;
}


//TestMsg zusammenbauen
function generateTestMsg($downloadlink,$downloadlinkhtml) {
	global $error;

	// Username
	$username = htmlspecialchars($_POST["username"]);

	// Username
	$useremail = htmlspecialchars($_POST["useremail"]);

	// Ansprechpartner
	$kontakt  = htmlspecialchars($_POST["kontakt"]);
	
	// Nachricht
	$comments  = htmlspecialchars($_POST["comments"]);

	$msg .= "Folgende Daten wurden gesendet:\r\n\r\n";
	$msg .= "<br>Name: ".$username."\r\n";
	$msg .= "<br>EMail: ".$useremail."\r\n";
	$msg .= "<br>Ansprechpartner: ".$kontakt."\r\n";
	$msg .= "<p>Nachricht: ".$comments."</p>\r\n";
	$msg .= "<br>TextLink zum Herunterladen: <br>\r\n";
	$msg .= $downloadlink."\r\n";
	$msg .= "\r\n\r\n";
	$msg .= "<br>HtmlLink zum Herunterladen: <br>\r\n";
	$msg .= $downloadlinkhtml;
	$msg .= "<p>\r\n\r\n";
	$msg .= "Bitte beachten: Dieser Link ist nur begrenzte Zeit abrufbar</p>\r\n";
	
	
	echo json_encode($msg);

}	


function checkFormFields () {
	global $error;
	foreach($_POST as $key => $value) 
	{
		if(isset($value) && !empty($value))	{
			if ($key == 'useremail') {
				if (DEBUG == 1)
				doMsg("key: ".$key . " => value: " . $value."<br>");
				checkMail($value);
			}
		} else {	
			$error = 1;		
			if ($key == 'username')
				$fieldName = "Name";
			if ($key == 'useremail')
				$fieldName = "EMail";
			if ($key == 'kontakt')
				$fieldName = "Ansprechpartner";
			if ($key == 'comments')
				$fieldName = "Info";
			doMsg ("Ung&uuml;ltige Eingabe f&uuml;r das Formular-Feld:<b> ".$fieldName." </b><br>"); 	
		}
	}
}  


// Formularfeld EMail überprüfen
function checkMail($useremail) {                  
	global $error;                     
	if(!preg_match( '/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/' , $useremail))  {
		$error = 1;
		doMsg ("Ung&uuml;ltige Mailadresse<a href='index.php'><b>Zur&uuml;ck zum Formular</b><a><br>"); 				
	}
}
##### Ende Funktionsdefinitionen #######################################


##### Start Reste ######################################################
//if(isset($_FILES['error']) && $_FILES['error'] == UPLOAD_ERR_EXTENSION) { 
	//echo json_encode(array('err' => "Upload wurde abgebrochen"));
//} else {
	// echo(json_encode($_FILES));
	
	// echo json_encode($_POST); 
	//echo "YOUMETOO";
	//echo(json_encode($_POST['username']));
	
	// echo "Useremail: ".$_POST['useremail'];
	//echo(json_encode($_POST['kontakt']));
	//echo(json_encode($_POST['comments']));

//}
 
 
 // echo '{ "comments": "' . $_POST['comments'] . '" }';   
 //echo '{ "files": "' .json_encode($_FILES). '" }';   

//doMsg($response);        
//echo json_encode($response); 
##### Ende Reste #######################################################
 

?>
