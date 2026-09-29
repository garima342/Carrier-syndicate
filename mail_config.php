<?php
// ------------------------------------------------------------------
// Gmail SMTP credentials for sending OTP emails.
//
// 1. Use a real Gmail address you control.
// 2. You CANNOT use your normal Gmail password here — Google blocks
//    that for third-party apps. You need an "App Password" instead:
//      a. Go to https://myaccount.google.com/security
//      b. Turn on 2-Step Verification (required before App Passwords
//         will even show up as an option).
//      c. Go to https://myaccount.google.com/apppasswords
//      d. Create a new app password (name it e.g. "InternHub OTP").
//      e. Google gives you a 16-character code like: abcd efgh ijkl mnop
//         Paste it below WITHOUT spaces.
//
// Keep this file out of version control / public folders in production
// (add it to .gitignore) since it holds a live credential.
// ------------------------------------------------------------------

define("MAIL_USERNAME", "stannes.17355@gmail.com");
define("MAIL_PASSWORD", "ezfcfgjjwaprococ");