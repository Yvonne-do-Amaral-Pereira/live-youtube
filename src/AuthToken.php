<?php

class AuthToken {
    private $clientFilename;
    private $authFilename;

    public function __construct($clientFilename, $authFilename)
    {
        $this->clientFilename = $clientFilename;
        $this->authFilename = $authFilename;
    }
    private function refreshToken($refreshToken)
    {
        $client = json_decode(file_get_contents($this->clientFilename));
        $http = new GuzzleHttp\Client([
            'base_uri' => 'https://accounts.google.com/o/oauth2/',
            'timeout' => 2.0,
        ]);

        $response = $http->request('POST', 'token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => $client->web->client_id,
                'client_secret' => $client->web->client_secret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ],
        ]);

        $newAuth = json_decode($response->getBody());
        if (! isset($newAuth->created)) {
            $newAuth->created = time();
        }
        if (! isset($newAuth->refresh_token)) {
            $newAuth->refresh_token = $refreshToken;
        }
        return $newAuth;
    }

    public function getAuthorization()
    {
        $auth = json_decode(file_get_contents($this->authFilename));
        if ($auth->created + $auth->expires_in < time() - 60) {
            $auth = $this->refreshToken($auth->refresh_token);
            file_put_contents($this->authFilename, json_encode($auth));
        }
        return "{$auth->token_type} {$auth->access_token}";
    }
}