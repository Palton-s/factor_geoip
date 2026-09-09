<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'IP geolocation adaptive MFA';
$string['heading_desc'] = 'Requires an emailed code when a login comes from a location far away from the user\'s last trusted location.';

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

$string['verificationcode'] = 'Verification code';
$string['checkemail_desc'] = 'We detected a login from an unusual location. A verification code was sent to your registered email address.';
$string['error:invalidcode'] = 'Invalid or expired code.';
$string['unknownlocation'] = 'unknown location';
$string['summarycondition'] = 'when the login comes from a location far from the usual one';

$string['email_subject'] = 'Login verification code - {$a}';
$string['email_body'] = 'Hi {$a->fullname},

We detected a login to your account from an unusual location:

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
