moodle-auth-bspdpolicy
==================
Supports Moodle 2.0 up to Moodle 2.6

A Moodle Auth plugin that handles password expiry

Introduction
For a customer of UP learning, a special Authentication Module has been developed to handle password expiry in Moodle environments.
This plugin can be configured to force users to modify their password at a set interval (in days).

Installing
Place the bspdpolicy folder into the auth folder. Follow the normal installation procedure as you would for any plugin.

Configuration
After installation the authentication module has to be enabled and configured.
Users - Authentication - Manage Authentication will now list the added module as: 'Password Expiration check'

On the 'Settings' page there are two configurable options:
1.	The amount of days for password expiration
2.	The URL to redirect to when the password has expired (ie Moodle Password change page)

Choose enable to activate the plugin, optionally configure the Expirationdays to a value to your desire (in days).
Also configure the page where users should be redirected when their password has expired. To use the default change password page (recommended) enter https://yourdomainname.com/login/change_password.php

Remark: Before the plugin is fully active, its settings need to be saved once, after this users will be tracked.
The module will apply to all logged in users, regardless of the authentication plugin order.
For this reason the plugin will enforce a password change for every user logging in after activating the plugin directly, and not after the first interval.

Workings
This module stores a password change date in the mdl_user_preferences table in the following format:
- userid
- auth_bspdpolicy_date
- timestamp (in unixtime)

When you login, the plugin will check the timestamp that is stored in the database, and if the value there is older than the password expiration interval, it will insert force_passwordchange = 1 into your user preferences, triggering the Moodle password change.