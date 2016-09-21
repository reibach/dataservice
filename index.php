<?php
session_start();
include 'config.php';    
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Datei-Upload</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="jquery.form.js"></script>
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
			<h2>Nennen Sie uns ihren Namen, Ihre EMail-Adresse und w&auml;hlen Sie Ihren Ansprechpartner. </h2>
        <section>
            <form action="upload.php" method="post" enctype="multipart/form-data" id="upload_form">
            <!--
                Dieses Feld ist wichtig. PHP benötigt dies für die Zuordnung.
                Der Wert (Value) ist für uns später wichtig um auf die globale $_SESSION zuzugreifen
            -->
                <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="test">      
                <div>
                    <label for="username">Ihr Name: <br />
                     <input type="text" id="username" name="username" required></label>
               </div>
                 <div>
                    <label for="useremail">Ihre EMail-Adresse:<br />
                    <input id="useremail" type="email" name="useremail" required></label>
                </div>
               <div>
                    <label for="kontakt">Gew&uuml;nschter Ansprechpartner:</label><br />
					<select id="kontakt" name="kontakt"> 
					<?php
					foreach ($team as $key => $value) {
						echo '<option value="'.$value.'">'.$key.'</option>';
					}
					?>
					</select>	
                </div>
                <div>
					<label for="comments">Kurze Info:<br />					
					<textarea id="comments" name="comments" COLS=40 ROWS=6></TEXTAREA></label>
                </div>
                
				<div>					
                    <label for="multiple">Bitte wählen Sie eine oder mehrere Dateien aus:<br /><br />
                <input type="file" multiple="multiple" name="file[]" required /></label>
				</div>
                
                <div>
                    <br /><br /><input name="upload_start" type="submit" value="Hochladen">
                    <input name="abbrechen" type="button" value="Abbrechen" id="abbrechen">
                </div>
            </form>
        </section>

        <section>
            <h2>Fortschrittsanzeige:</h2>
            <div>
                <progress max="1" value="0" id="fortschritt"></progress>
                <p id="fortschritt_txt"></p>
            </div>
        </section>
    </article>

    <script>

        var intervalID = 0;        
        
        $(document).ready(function(e) {
            $('#upload_form').submit(function(e) {				
				var form = $('#upload_form');
				var data = form.serialize();

                intervalID = setInterval(function() {
                    $.getJSON('fortschritt.php', function(data){

                        if(data)
                        {
                            $('#fortschritt').val(data.bytes_processed / data.content_length);
                            $('#fortschritt_txt').html('Fortschritt '+ Math.round((data.bytes_processed / data.content_length)*100) + '%');
                        }
                    });
                }, 1000); //Zeitintervall auf 1s setzen
					                            
				$('#upload_form').ajaxSubmit({
												dataType:'json',
										
												success:function(data) 
												//success:function() 
												{
													$('#fortschritt').val('1');
													$('#fortschritt_txt').html('100% hochgeladen. Besten Dank.');
													clearInterval(intervalID);
													//setTimeout(function(){ alert("Hello"); }, 3000);
													
													var data1 = $.param("DATA1" + data);
													
													//console.log("DATA1" + data1);
													
													var data2  = $('form').serialize();
													//console.log("DATA2" + data2);
													
													var regex = /[?&]([^=#]+)=([^&#]*)/g,
													url = data2,
													params = {},
													match;
													while(match = regex.exec(url)) {
														params[match[1]] = match[2];
													}
													
													$("#placeholder").html(
														 "<br />" + data	
													
													);
													$('#upload_form')[0].reset();
													
												},  
															
                                                error:    function()
                                                {
                                                    $('#fortschritt').val('1');
                                                    $('#fortschritt_txt').html('Ein Fehler ist aufgetreten, bitte versuchen Sie es noch einmal.');
                                                    clearInterval(intervalID);
                                                }
                                            });
                e.preventDefault(); //Event Abbrechen

            });

            $('#abbrechen').click(function(e) {
                $.ajax("fortschritt.php?cancel=true");
                $('#fortschritt').val('1');
                $('#fortschritt_txt').html('Upload abgebrochen');

                clearInterval(intervalID);
            });
        });

    </script>
    <div id="placeholder"></div>
</body>
</html>
