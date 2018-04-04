<?php

namespace AlexeyMakarov\Smotreshka;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class SmotreshkaClient
{
    public function __construct(string $operator, string $node)
    {
        $this->base_uri = 'http://'.$operator.'.'.$node.'.lfstrm.tv/v2/';
    }

    /**
     * Create new account
     * @param string $email
     * @param string $username
     * @param string $password
     * @param array $purchases
     * @param array $info
     * @return string
     */
    public function accountCreate(string $email, string $username = '', string $password = '', array $purchases = [], array $info = []){

        $data = [
            'email' => $email,
        ];

        if($password !== '') {
            $data['password'] = $password;
        }
        if($username !== '') {
            $data['username'] = $username;
        }
        if(!empty($purchases)) {
            $data['purchases'] = $purchases;
        }
        if(!empty($info)) {
            $data['info'] = $info;
        }

        return $this->makeRequest('POST', 'accounts', $data);
    }

    /**
     * Get account information
     * @param string $id
     * @return string
     */
    public function accountInfo(string $id){
        return $this->makeRequest('GET', "accounts/{$id}");
    }

    /**
     * Update account information
     * @param string $id
     * @param array $info
     * @return string
     */
    public function accountUpdate(string $id, array $info){
        return $this->makeRequest('POST', "accounts/{$id}/update", ['info' => $info]);
    }

    /**
     * Reset account password
     * @param string $id
     * @param string $password
     * @return string
     */
    public function accountResetPassword(string $id, string $password = ''){
        $data = ($password !== '') ? ['password' => $password] : [];
        return $this->makeRequest('POST', "accounts/{$id}/reset-password", $data);
    }

    /**
     * Delete user account
     * @param string $id
     * @return string
     */
    public function accountDelete(string $id)
    {
        return $this->makeRequest('DELETE', "accounts/{$id}");
    }

    /**
     * List of account subscriptions
     * @param string $id
     * @return string
     */
    public function accountSubscriptions(string $id)
    {
        return $this->makeRequest('GET', "accounts/{$id}/subscriptions");
    }

    public function accountSubscriptionsUpdate(string $id, string $subscription_id, bool $valid)
    {
        $data = [
            'id' => $subscription_id,
            'valid' => $valid,
        ];

        return $this->makeRequest('POST', "accounts/{$id}/subscriptions", $data);
    }

    /**
     * List of available subscriptions
     * @return string
     */
    public function getListOfSubscriptions()
    {
        return $this->makeRequest('GET', "subscriptions");
    }

    /**
     * Get list of all accounts
     * @return string
     */
    public function accountGetAllList()
    {
        return $this->makeRequest('GET', "accounts");
    }

    private function makeRequest(string $method, string $url, array $data = []): string
    {
        try {
            $client = new Client([
                'base_uri' => $this->base_uri
            ]);

            $request = new Request($method, $url, ['Content-Type' => 'application/json'], \json_encode($data));

            $response = $client->send($request, ['timeout' => 5]);

            return (string) $response->getBody()->getContents();

        } catch (BadResponseException $e) {
            $responseBodyAsString = $e->getResponse()->getBody()->getContents();
            \json_decode($responseBodyAsString);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $responseBodyAsString;
            }
            return \json_encode([
                'error' => $e->getResponse()->getReasonPhrase(),
            ]);
        }
    }
}