<?php
use \MapasCulturais\i;

return [
    /*
    Define o [transport](https://symfony.com/doc/current/mailer.html#using-built-in-transports) do Symfony\mailer

    Além dos _transports_ Built-in, estão instalados por padrão os abaixo listados:
    
    - [Amazon SES](https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Mailer/Bridge/Amazon/README.md)
    - [Mailchimp Mandrill](https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Mailer/Bridge/Mailchimp/README.md)
    - [Mailgun](https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Mailer/Bridge/Mailgun/README.md)
    - [SendGrid](https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Mailer/Bridge/Sendgrid/README.md)

    exemplos :
    ```
    'mailer.transport' => 'smtp://user:pass@smtp.example.com:25'
    'mailer.transport' => 'sendgrid+smtp://KEY@default'
    'mailer.transport' => 'sendgrid+api://KEY@default'
    ```

    Se suas credenciais possuem caracteres especiais, você deve _URL-encode_ elas. 
    Por exemplo, `ses+smtp://ABC1234:abc+12/345@default` deve ser configurada como `ses+smtp://ABC1234:abc%2B12%2F345@default`


    */
    'mailer.transport'  => env('MAILER_TRANSPORT'),
    'mailer.from'       => env('MAILER_FROM', 'suporte@mapasculturais.org'),
    'mailer.alwaysTo'   => env('MAILER_ALWAYSTO', false),
    'mailer.bcc'        => env('MAILER_BCC', ''),
    'mailer.replyTo'    => env('MAILER_REPLYTO', '')
];