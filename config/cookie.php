<?php

return [
    // The name of the cookie to be used when it's not specified by users
    'defaultName' => 'gdpr-cookie-notice',
    // The duration of the cookie, in seconds (31557600 seconds = 365.25 days * 24 hours/day * 60 minutes/hour * 60 seconds/minute)
    'duration' => 31557600,
    // The cookie path (if empty, we'll use the concrete5 installation directory)
    'path' => '',
    // The cookie domain (if empty, we'll use the current domain)
    'domain' => '',
];
