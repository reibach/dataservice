

Logik der Formularüberprüfung vom Server auf den Client übertragen, 
wird nun mittels HTML5 erledigt. Dies sollte für den Normalfall 
ausreichen, andernfalls wird eine Zugangsbeschränkung (UserReg, 
.htaccess) eingeführt.


2DO
  * tmp vorab löschen
  * IP-Sperre / Captcha gegen Missbrauch



Cronjob zum Löschen der Zipfiles nach Zeitraum (Bsp):
49 11 * * * /usr/bin/find /var/www/html/dataservice/download/ -name '*.zip*' -ctime +10  -exec rm -f  {} \;   

