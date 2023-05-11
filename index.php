<?php

require __DIR__."/vendor/autoload.php";

use App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;


//Instância principal do Payload
$payload = (new Payload)->setPixKey("")//Chave pix do Destinatário
->setDescription("")//Descrição da Transação
->setMerchantName("")//Nome do Destinatário
->setMerchantCity("")//Localização do Destinatário
->setAmount("1.00")//Valor da Transação
->setTxid("***");//ID da Transação

//Codigo do pagamento PIX
$payloadQrCode = $payload->getPayload();

//Gera o qrcode
$qrcode = new QrCode($payloadQrCode);

//Cria imagem do qrcode
$image = (new Output\Png)->output($qrcode,400);

header("Content-Type: image/png");
echo $image;