<?php
declare(strict_types=1);

namespace App\Service\PaymentOption;

use App\Service\PaymentOption\Form\BankAccountData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class IbanValidationService
{
    private const API_BASE_URL = 'https://openiban.com/validate/';
    private const API_QUERY_PARAMS = '?validateBankCode=true&getBIC=true';

    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function validateIban(string $iban): ?BankAccountData
    {
        $ibanInformation = $this->extractIbanInformationByApi($iban);
        if ($ibanInformation){
            $bankData = $ibanInformation['bankData'];
            $bankAccountData = new BankAccountData();
            $bankAccountData->setBankName($bankData['name']);
            $bankAccountData->setIban($ibanInformation['iban']);
            $bankAccountData->setBic($bankData['bic']);
            $bankAccountData->setEnabled(true);
            $bankAccountData->setPreferred(false);
            return $bankAccountData;
        }
        return null;
    }

    public function extractIbanInformationByApi(string $iban): ?array
    {
        $url = self::API_BASE_URL . $iban;

        try {
            $options = [
                'query' => ['validateBankCode' => true, 'getBIC' => true]
            ];

            $response = $this->httpClient->request('GET', $url, $options);

            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true);
        } catch (GuzzleException $e) {
            return null;
        }
    }
}
