<?php
    session_start();
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Datei-Upload</title>
    <style>
        div    { padding:5px;    }
        progress { margin:10px 0 10px 0; }
    </style>
    <link rel="stylesheet" href="style.css" type="text/css">
</head>

<body>
    <article>
        <header>
            <h1>Datei-Upload</h1>
        </header>
			<h2>Hier k&ouml;nnen Sie uns eine oder mehrere  Dateien zukommen lassen.</h2> 
			<h2>Nennen Sie uns nur kurz ihren Namen und w&auml;hlen Sie Ihren Ansprechpartner. </h2>
        <section>
            <form action="checkform.php" method="post" enctype="multipart/form-data" id="upload_form">
            <!--
                Dieses Feld ist wichtig. PHP benötigt dies für die Zuordnung.
                Der Wert (Value) ist für uns später wichtig um auf die globale $_SESSION zuzugreifen
            -->
                <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="test">
        
        
                <div>
                    <label for="Ihr Name">Ihr Name:</label>
                    <input id="username" required>
                </div>
                <div>
                    <label for="Ihre EMailadresse">Ihre EMail-Adresse:</label><br>
                    <input id="useremail" type="email" required>
                </div>
                <div>
                    <label for="kontakt">Gew&uuml;nschter Ansprechpartner:</label><br>
					<select name="kontakt" size="1" required> 
						<option value="P101">Info</option> 
						<option value="P101">Info</option> 
						<!-- // 
						<option value="P102">Hiller</option> 
						<option value="P103">Leffrang</option> 
						<option value="P101">Konnemann</option> 
						<option value="P101">Beyersdorff</option> 
						//-->
					</select>	
                </div>
                <div>
					<label for="comments">Kurze Info:</label><br>					
					<textarea id="comments" required COLS=40 ROWS=6></TEXTAREA>
                </div>
                <div>					
                    <label for="multiple">Eine oder mehrere Dateien auswählen :</label>
                <input type="file" multiple="multiple" name="file[]" required />
                </div>
                <div>
                    <input name="upload_start" type="submit" value="Hochladen">
                    <input name="abbrechen" type="button" value="Abbrechen" id="abbrechen">
                </div>
            </form>
        </section>
    </article>
</body>
</html>
