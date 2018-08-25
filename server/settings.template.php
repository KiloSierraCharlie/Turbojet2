<?php

return array(
    'DATABASE' => array(
        'dev' => array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => '',
            'user'      => '',
            'password'  => '',
            'charset'   => 'utf8'
        ),
        'prod' => array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => '',
            'user'      => '',
            'password'  => '',
            'charset'   => 'utf8'
        )
    ),
    'SMTP' => array(
        'username' => '', // gmail username
        'password' => '' // gmail password
    ),
    'DEBUG_MAIL_RECIPIENT' => 'kevin.bouhadana@gmail.com',
    // 'DEBUG_MAIL_RECIPIENT' => 'fjjsus@gmail.com',
    'CORS' => array(
        'dev' => array(
            'allowOrigin' => 'http://localhost:8080' // Space separated set of allowed domains. Defaults to all
            // 'allowMethods' => 'GET,POST,PUT,DELETE,OPTIONS',
            // 'allowHeaders' => 'ApiKey,AuthToken'
        ),
        'prod' => array(
            'allowOrigin' => '*'  // Space separated set of allowed domains. Defaults to all
            // 'allowMethods' => 'GET,POST,PUT,DELETE,OPTIONS',
            // 'allowHeaders' => 'ApiKey,AuthToken'
        )
    ),
    'APIKEY' => 'rpF88UUHiheBBYiC1KCbTsZDkuP1kQvq',
    'RECAPTCHA_SECRET' => '6LeS8k8UAAAAAJH1Yr_lPcjjG4n2lnHo2Tbnq1FP',
    'DEBUG' => true, // Silex debug mode
    'ENV' => 'dev' // dev or prod
);
