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

The "lgv-anmeldesystem is a registration tool, where people can register itself and optional modify or delete the registration at a later time by using the provided link in the 
confirmation email.

Feature overview
* Every person is identified through theit email. For one event you can only register once
* The "lgv-anmeldesystem" consists or a frontend, which uses see and a backend which is restricted to the logged in user, editor and admins
* The registration formular has standard fields like first name, surename, street, ZIP, city, telephone and email, it can also be extened with other fields
* As extensions you can use text fields, checkboxes, selection lists and numbers
* The number of registration can be restricted
* Is the registration limit reached, a waiting list can be created
* Also it is possible, that additional people can be added to a registration
* You can group events
* All registrations can be exported as CSV file


== Installation ==

The section describes how the plugin must be installed.

1. Load the files to `/wp-content/plugins/lgv-anmeldesystem` or install the plugin directly via the WordPress plugins page.
1. Activate the pluginvia the 'Plugins' page from WordPress
1. Go to `Settings->LGV-Anmeldungen` to adjust the configuration
1. Go to `Globale Einstellungen berarbeiten` and change the values accordingly
1. Create a page and embed the plugin by adding `[lgv-anmeldesystem]` as content of the page

== Frequently Asked Questions ==

= The send emails are not always received

The included `mail` program directly sends the email to the receiver.
Many provider do not allow such an email (e.g. Arcor).
It is suggested, that you install the plugin [`WP Mail SMTP`](https://wordpress.org/plugins/wp-mail-smtp/). 
With this plugin you can send emails via your normal email account. For this you must enter the details into the plugin or your code.

= Link always goes to the main page and not to the registration

This happens, if you have placed the `[lgv-anmeldesystem]` into a different page then the main page.
To make the links work correctly, you have to set the "PageName" in the global settings of this plugin to the correct path (e.g. "/different-page").

== Screenshots ==

1. Example af an registration formular
2. Example of the backend

== Changelog ==

= 1.0 =
* First public release

= 1.1 =
* First name and last name regex now possible with custom error message
* Bool values can now be set to "required"
* Hint text has now a seperate style (italic)
* General urls are now possible; format: [text](url)

= 1.4 =
* Added support for Payment (cash and sepa)

= 1.5 =
* Added support for other start page as main page (only if you do not use the "simple" perma links!) / please set PageName accordingly in global settings (e.g. "/extra-seite")
* Added support for color Tag; example: [color:#FF00FF)]Text[/color] or [color:red]Text[/color]

= 1.6 =
* Some default texts are now in the settings instead of the code

= 1.7 =
* Small warning correction in backend / support for WP5.3

= 1.8 =
* Removed warning if multiple names are allowed and no additional name was given

= 1.9 =
* Compatibility with WP 5.4

= 1.10 =
* Compatibility with WP 5.5

= 1.11 =
* Removed default email addresses

= 1.12 =
* OptZip/Street/City allows now <=0 (do not show) / 0 (show and required) / >= 0 (show, not required)
* Support WP 5.6

= 1.13 =
* Support WP 5.7

= 1.14 =
* Support WP 5.8

= 1.15 =
* Added "PersonHeader" config field (to display an optional header above the persons data)

= 1.16 =
* Added "Copy" button in backend

= 1.17 =
* Small bugfix in backend UI

= 1.18 =
* Support WP 6.0

= 1.19 =
* Allows multiple registration for the same event with the same email address (MultiEMail=1)

= 1.20 =
* Support WP 6.2

= 1.21 =
* Support WP 6.3 / 6.4
* Update "[isbb-teilnahmebedingungen]"
