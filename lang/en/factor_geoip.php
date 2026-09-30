<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'IP geolocation adaptive MFA';
$string['heading_desc'] = 'Requires an emailed code when a login shows a risk signal: new device or browser, distant location, country change, impossible travel, VPN/proxy/Tor, failed passwords or unusual time.';

$string['mmdbpath'] = 'GeoLite2-City.mmdb path';
$string['mmdbpath_desc'] = 'Absolute server path to the MaxMind GeoLite2-City database file. Free download at maxmind.com/en/geolite2/signup.';

$string['distancekm'] = 'Distance threshold (km)';
$string['distancekm_desc'] = 'If the current login is further than this distance (km) from the last trusted location, the extra code is required.';

$string['codeexpiry'] = 'Code validity (minutes)';
$string['codeexpiry_desc'] = 'Minutes until the emailed code expires.';

$string['maxattempts'] = 'Maximum attempts';
$string['maxattempts_desc'] = 'Number of wrong attempts allowed before the code is invalidated.';

$string['challengefirstlogin'] = 'Challenge on first login';
$string['challengefirstlogin_desc'] = 'If enabled, a user\'s very first login (with no trusted location saved yet) already requires the emailed code. If disabled, the first login simply records the trusted location.';

$string['info'] = 'Asks for a code sent by email only when the login shows a risk signal.';
$string['logintitle'] = 'Confirm it is you';
$string['logindesc'] = 'Enter the verification code sent to your email.';
$string['loginoption'] = 'Receive a code by email';
$string['loginsubmit'] = 'Verify code';
$string['verificationcode'] = 'Verification code';
$string['checkemail_desc'] = 'We need to confirm this login ({$a}). A verification code was sent to your registered email address.';
$string['error:invalidcode'] = 'Invalid or expired code.';
$string['unknownlocation'] = 'unknown location';
$string['summarycondition'] = 'when the login shows a risk signal (new device, unusual location or time, VPN/Tor, failed passwords)';

$string['email_subject'] = 'Login verification code - {$a}';
$string['email_body'] = 'Hi {$a->fullname},

We detected a login to your account that needs confirmation.

Reason: {$a->reason}

IP: {$a->ip}
Approximate location: {$a->location}

If this was you, use the code below to continue (valid for {$a->minutes} minutes):

{$a->code}

If you do not recognise this access, ignore this email and consider changing your password.';

$string['privacy:metadata:factor_geoip_trusted'] = 'Each user\'s last trusted login location.';
$string['privacy:metadata:factor_geoip_trusted:userid'] = 'The user ID.';
$string['privacy:metadata:factor_geoip_trusted:ip'] = 'Source IP of the trusted login.';
$string['privacy:metadata:factor_geoip_trusted:latitude'] = 'Approximate latitude of the IP.';
$string['privacy:metadata:factor_geoip_trusted:longitude'] = 'Approximate longitude of the IP.';
$string['privacy:metadata:factor_geoip_trusted:country'] = 'Approximate country of the IP.';
$string['privacy:metadata:factor_geoip_trusted:city'] = 'Approximate city of the IP.';
$string['privacy:metadata:factor_geoip_trusted:timecreated'] = 'When the location was recorded.';
$string['privacy:metadata:factor_geoip_codes'] = 'Emailed verification codes issued for logins from unusual locations.';
$string['cleanuptask'] = 'Cleanup expired verification codes (geoip MFA)';
$string['checkdevice'] = 'Check device';
$string['checkdevice_desc'] = 'If enabled, logins from a browser/device the user has not verified before also require the emailed code. The first device of a user is trusted automatically unless "Challenge on first login" is enabled.';
$string['deviceexpiry'] = 'Trusted device validity (days)';
$string['deviceexpiry_desc'] = 'A trusted device that is not used for this many days must be verified again.';
$string['reason_newdevice'] = 'login from a new device';
$string['reason_distantlocation'] = 'login from an unusual location';
$string['privacy:metadata:factor_geoip_devices'] = 'Browsers/devices the user has verified as trusted.';
$string['privacy:metadata:factor_geoip_devices:tokenhash'] = 'Hash of the random token stored in the device cookie.';
$string['privacy:metadata:factor_geoip_devices:useragent'] = 'Browser user agent when the device was trusted.';
$string['privacy:metadata:factor_geoip_devices:timelastused'] = 'When the device was last used.';
$string['privacy:metadata:factor_geoip_lastseen'] = 'Location of each user\'s most recent login, used to detect impossible travel.';
$string['privacy:metadata:factor_geoip_lastseen:timeseen'] = 'When the login was seen.';

$string['checkuseragent'] = 'Check browser/OS change';
$string['checkuseragent_desc'] = 'If enabled, a trusted device that now reports a different browser or operating system (versions are ignored) requires the emailed code.';

$string['heading_location'] = 'Country and impossible travel';
$string['checkcountry'] = 'Check country change';
$string['checkcountry_desc'] = 'If enabled, a login from a country different from the trusted location\'s country requires the code, regardless of the distance.';
$string['checktravel'] = 'Check impossible travel';
$string['checktravel_desc'] = 'If enabled, requires the code when the distance from the previous login divided by the elapsed time exceeds the maximum speed. Distances under 100 km are ignored (GeoIP inaccuracy).';
$string['travelmaxspeed'] = 'Maximum travel speed (km/h)';
$string['travelmaxspeed_desc'] = 'Speeds above this are considered impossible. 900 km/h is about the speed of a commercial airplane.';

$string['heading_anonymous'] = 'VPN, proxy and Tor';
$string['checkanonymous'] = 'Check VPN/proxy/Tor';
$string['checkanonymous_desc'] = 'If enabled, logins from Tor exit nodes (official list, updated every 6 hours by a scheduled task) and, if configured, from IPs flagged by the GeoIP2 Anonymous IP database require the code.';
$string['anonmmdbpath'] = 'GeoIP2-Anonymous-IP.mmdb path (optional)';
$string['anonmmdbpath_desc'] = 'Absolute server path to the MaxMind GeoIP2 Anonymous IP database (paid). Needed to detect commercial VPNs and proxies; without it only Tor is detected.';
$string['updatetortask'] = 'Update Tor exit node list (geoip MFA)';
$string['error:torupdate'] = 'Could not update the Tor exit node list: {$a}';

$string['heading_failedlogins'] = 'Failed passwords';
$string['checkfailedlogins'] = 'Check failed passwords';
$string['checkfailedlogins_desc'] = 'If enabled, requires the code when the password was typed wrong several times since the user\'s last successful login.';
$string['failedloginsthreshold'] = 'Failed password threshold';
$string['failedloginsthreshold_desc'] = 'Number of wrong passwords since the last successful login that triggers the code.';

$string['heading_time'] = 'Unusual time';
$string['checkhours'] = 'Check unusual time';
$string['checkhours_desc'] = 'If enabled, logins inside the time window or on the days below (in the user\'s timezone) require the code.';
$string['unusualhourstart'] = 'Unusual window start';
$string['unusualhourstart_desc'] = 'Start of the unusual time window (inclusive).';
$string['unusualhourend'] = 'Unusual window end';
$string['unusualhourend_desc'] = 'End of the unusual time window (exclusive). The window may cross midnight (e.g. 22:00 to 06:00). Same start and end disables the window.';
$string['unusualdays'] = 'Unusual days';
$string['unusualdays_desc'] = 'Logins on the selected weekdays always require the code.';

$string['reason_countrychange'] = 'login from a different country';
$string['reason_impossibletravel'] = 'impossible travel since the previous login';
$string['reason_anonymousip'] = 'login from a VPN, proxy or Tor';
$string['reason_failedlogins'] = 'several wrong passwords before this login';
$string['reason_unusualtime'] = 'login at an unusual time';
$string['reason_useragentchange'] = 'different browser or operating system';
