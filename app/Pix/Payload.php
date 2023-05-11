<?php

namespace App\Pix;

class Payload
{

    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * Chave PIX
     * @var string
     */
    private $pixKey;

    /**
     * Descrição do Pagamento
     * @var string
     */
    private $description;

    /**
     * Nome do Titular da Conta
     * @var string
     */
    private $merchantName;

    /**
     * Cidade do Titular da Conta
     * @var string
     */
    private $merchantCity;

    /**
     * Id da Transação PIX
     * @var string
     */
    private $txid;

    /**
     * Valor da Transação
     * @var string
     */
    private $amount;

    /**
     * Método responsável por definir a chave pix
     * @param string Chave Pix
     */
    public function setPixKey($pixKey)
    {
        $this->pixKey = $pixKey;
        return $this;
    }

    /**
     * Método responsável por definir a descrição da transação
     * @param string Descrição
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Método responsável por definir o nome do destinatário do pix
     * @param string Nome do Destinatário
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * Método responsável por definir a cidade do destinatário do pix
     * @param string Cidade do Destinatário
     */
    public function setMerchantCity($merchantCity)
    {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /**
     * Método responsável por definir o id da transação pix
     * @param string Id da Transação
     */
    public function setTxid($txid)
    {
        $this->txid = $txid;
        return $this;
    }

    /**
     * Método responsável por definir o id da transação pix
     * @param float Valor da Transação
     */
    public function setAmount($amount)
    {
        $this->amount = number_format($amount, 2, '.', '');
        return $this;
    }

    /**
     * Método responsável por retornar o valor completo de um objeto do payload
     * @param string $id
     * @param string $value
     * @return string $id.$size.$value
     */
    private function getValue($id, $value)
    {
        $size = str_pad(strlen($value), 2, "0", STR_PAD_LEFT);
        return $id . $size . $value;
    }

    /**
     * Método responsável por retornar os valores completos das informações da conta
     * @return string 
     */
    public function getMerchantAccountInformation()
    {
        //Domínio do banco
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');

        //Chave PIX
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->pixKey);

        //Descrição da transação
        $description = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description);

        //Valor completo da conta
        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui . $key . $description);
    }

    /**
     * Método responsável por retornar os valores completos do campo adicional do PIX (TXID)
     */
    private function getAdditionalFieldDataTemplate()
    {
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txid);

        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload)
    {
        //Adiciona dados gerais ao payload
        $payload .= self::ID_CRC16 . '04';

        //Dados definidos pelo BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //Checksum
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16 . '04' . strtoupper(dechex($resultado));
    }

    /**
     * Médoto responsável por gerar o código completo do Payload do Pix
     */
    public function getPayload()
    {
        //Cria o payload
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01') .
            $this->getMerchantAccountInformation() .
            $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000') .
            $this->getValue(self::ID_TRANSACTION_CURRENCY, '986') .
            $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount) .
            $this->getValue(self::ID_COUNTRY_CODE, "BR") .
            $this->getValue(self::ID_MERCHANT_NAME, $this->merchantName) .
            $this->getValue(self::ID_MERCHANT_CITY, $this->merchantCity) .
            $this->getAdditionalFieldDataTemplate();

        return $payload.$this->getCRC16($payload);
    }
}
