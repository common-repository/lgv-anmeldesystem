=== lgv-anmeldesystem ===
Contributors: jkalmbach
Tags: anmeldung, registration
Requires at least: 4.6
Tested up to: 6.4
Stable tag: trunk
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Das LGV-Anmeldesystem ist ein Anmeldetool, bei dem sich eine Person anmelden kann und diese Anmeldung optional
später auch jederzeit durch ein E-Mail Link ändern oder löschen kann.

Feature-Übersicht
* Jede Person wird durch eine eindeutige E-Mail identifiziert. Eine Person kann sich für eine Veranstaltung nur einmal anmelden.
* Das LGV-Anmeldesystem besteht aus einem Forntend, welches der Benutzer sieht und einem Backend, welches dem angemeldeten Benutzer, Redaktuer oder Admin vorbehalten ist.
* Die Anmeldung enthält Standard-Felder wie Vorname, Name, Straße, PLZ, Ort, Telefon und E-Mail, kann aber auch über weitere Felder ergänzt werden.
* Als Ergänzungen können aktuell Textfelder, Checkboxen, Auswahllisten und Zahlen verwendet werden.
* Die Anzahl der Anmeldungen kann begrenzt werden.
* Ist die Grenze der Anmeldunger erreicht, so kann eine Warteliste erstellt werden.
* Es ist möglich, dass eine Person auch noch zusätzliche Personen anmelden kann.
* Veranstaltungen können gruppiert werden
* Alle Anmeldungen können als CSV-Datei exportiert werden.


== Installation ==

Dieser Abschnitt beschreibt, wie das Plugin installiert werden muss.

1. Laden sie die Dateien nach `/wp-content/plugins/lgv-anmeldesystem` oder installieren die das Plugin direkt über die WordPress plugins Seite.
1. Aktivieren Sie das Plugin in der 'Plugins' Seite von WordPress
1. Gehen Sie nach `Settings->LGV-Anmeldungen` um die weitere Konfiguration vorzunehmen
1. Wählen Sie `Globale Einstellungen berarbeiten` und ändern sie die Daten entsprechend
1. Erstellen Sie eine Seite und binden Sie das Plugin ein, indem sie `[lgv-anmeldesystem]` als Inhalt verwenden

== Frequently Asked Questions ==

= Die gesendeten E-Mails kommen nicht immer an

Das eingebaute `mail` Programm versendet die E-Mails direkt an den Empfänger.
Dies wird von einigen Anbietern nicht zugelassen (z.B. Arcor).
Es wird empfohlen das Plugin [`WP Mail SMTP`](https://wordpress.org/plugins/wp-mail-smtp/) zu installieren. 
Damit können dann die E-Mail über einen normalen Account versendet werden. Dazu müssen dann die Zugangsdaten eingegeben werden.

= Link geht immer auf die Hauptseite und nicht zur Anmeldung

Das passiert immer, wenn sie das `[lgv-anmeldesystem]` nicht auf der Hauptseite platziert haben, sondern auf einer Unterseite.
Damit die Links korrekt funktionieren, müssen sie den "PageName" in den globalen Einstellungen dieses Plugins auf den korrekten Pfad setzen (z.B. "/different-page").

== Screenshots ==

1. Beispiel einer Registrierungs-Anmeldeseite
2. Beispiel des Backends

== Changelog ==

= 1.0 =
* First public release

= 1.1 =
* Vorname und Nachname können nun mit einem regex geprüft werden mit freiem Fehlertext
* Bool Werte können nun als "erforderlich" markiert werden
* Hinweistexte (hint) haben jetzt einen separaten Style (kursiv)
* Allgemeingültige Urls sind nun im Text möglich; Format: [text](url)

= 1.4 =
* Unterstützung für Bezahlungen (Bar und SEPA)

= 1.5 =
* Unterstützt andere Startseiten als die Hauptseite (nur wenn Sie nicht den "einfachen" Permalink verwenden!) / Bitte setze PageName entsprechend in den globalen Einstellungen  (z.B. "/extra-seite")
* Unterstützt Farb-Tags; Beispiele: [color:#FF00FF)]Text[/color] or [color:red]Text[/color]

= 1.6 =
* Einige Default-Texte sind jetzt in den Einstellungen und nicht mehr im Code

= 1.7 =
* Kleine Korrektur im Backend / unterstützt WP5.3

= 1.8 =
* Warnung entfernt, wenn mehrere Namen erlaubt sind aber keine angegeben wurden

= 1.9 =
* Kompatibel mit WP 5.4

= 1.10 =
* Kompatibel mit WP 5.5

= 1.11 =
* Default E-Mail Adressen entfernt

= 1.12 =
* OptZip/Street/City erlaubt nun <=0 (nicht anzeigen) / 0 (anzeigen und erforderlich) / >= 0 (anzeigen und optional)
* Kompatibel mit WP 5.6

= 1.13 =
* Kompatibel mit WP 5.7

= 1.14 =
* Kompatibel mit WP 5.8

= 1.15 =
* "PersonHeader" Konfigurationseintrag hinzugefügt (um einen optionalen Titel über den Personendaten hinzuzufügen)

= 1.16 =
* "Kopieren" button im Backend hinzugefügt

= 1.17 =
* Kleine Fehlerbehebung im UI des Backends

= 1.18 =
* Kompatibel mit WP 6.0

= 1.19 =
* Erlaubt nur die merhfache Registrierung zu einer Veranstaltung mit der selbe E-Mail Adresse (MultiEMail=1)

= 1.20 =
* Kompatibel mit WP 6.2

= 1.21 =
* Support WP 6.3 / 6.4
* Update "[isbb-teilnahmebedingungen]"
