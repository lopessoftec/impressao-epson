<?php


class Token
{
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public function getToken()
    {
        $_ENV = parse_ini_file('.env');

        $url = $_ENV['ENDPOINT_LOGIN_TOKEN'];
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['email' => $_ENV['EMAIL'], 'password' => $_ENV['PASSWORD']]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);

        curl_close($ch);

        return json_decode($data)->access_token;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
