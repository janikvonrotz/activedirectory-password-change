ActiveDirectory-Password-Change
===============================

![Screenshot](https://raw.github.com/janikvonrotz/ActiveDirectory-Password-Change/master/doc/screenshot.png)

* Install dependencies with [bower](https://github.com/bower/bower)
* Run a bower update in the project root
* Add an ActiveDirectory user which has the right to reset a user's password
* Set variables in `index.php`
	* `$ldapuser`
	* `$ldappwd`
	* `$ldaphost`
	* `$SecKey`
* Move the project on a webserver
	* Support for php ldap module must be enabled
	* Use a SSL certified connection when publishing the site to the internet
* Open the password change website like this `https://site.yourdomain.com/index.php?sec=[your secure key from $SecKey]`