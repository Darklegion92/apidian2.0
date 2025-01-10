<?php

namespace App\Traits;

trait ConfiguresMailServer
{
    protected function configureMailServer($user)
    {
        if ($user->validate_mail_server()) {
            \Config::set('mail.host', $user->mail_host);
            \Config::set('mail.port', $user->mail_port);
            \Config::set('mail.username', $user->mail_username);
            \Config::set('mail.password', $user->mail_password);
            \Config::set('mail.encryption', $user->mail_encryption);
            
            if ($user->mail_from_address != '' && $user->mail_from_name != '') {
                \Config::set('mail.from.address', $user->mail_from_address);
                \Config::set('mail.from.name', $user->mail_from_name);
            }
        }
    }
}