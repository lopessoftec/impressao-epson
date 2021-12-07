<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

include 'classes/DescricaoProduto.php';
include 'classes/ItensVenda.php';
include 'classes/FormaPagamento.php';
include 'classes/Token.php';

date_default_timezone_set('America/Fortaleza');

$_ENV = parse_ini_file('.env');

try {
    $connector = new FilePrintConnector("/dev/usb/lp1");
} catch (Exception $e) {
    echo "Permissão negada de acesso a porta da impressora, dê permissão a ela!";
    exit;
}

$token = Token::getInstance();
$token_acesso = $token->getToken();

$authorization = "Authorization: Bearer $token_acesso";

$url = $_ENV['ENDPOINT_VENDA_CUPOM'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$data_json = curl_exec($ch);

curl_close($ch);

$data = json_decode($data_json);

if ($data->status) {

    $items = [];
    $total = 0;
    foreach ($data->sale_filter as $value) {

        $items[] = new DescricaoProduto($value->barcode, $value->product_name);
        $items[] = new ItensVenda($value->amount, $value->sale_detail_value, $value->final_value);
        $total += $value->final_value;
    }

    $items_payment = [];

    foreach ($data->formas_pagamento_filter as $value) {

        $items_payment[] = new formaPagamento($value->description, $value->payment_sale_value);
    }

    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text(date('d/m/Y - h:i:s') . "\n");
    $printer->feed();

    /* Title of receipt */
    $printer->setEmphasis(true);
    $printer->text("Descrição\n");
    $printer->setEmphasis(false);

    /* Items */
    $printer->setJustification(Printer::JUSTIFY_LEFT);

    foreach ($items as $item) {
        $printer->text($item);
    }

    $printer->setJustification(Printer::JUSTIFY_CENTER);

    $printer->feed();
    $printer->setEmphasis(true);
    $printer->text("Forma de pagamento\n");
    $printer->setEmphasis(false);

    $printer->setJustification(Printer::JUSTIFY_LEFT);

    foreach ($items_payment as $item_payment) {
        $printer->text($item_payment);
    }

    /* Tax and total */
    $printer->feed();
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
    $printer->text(new formaPagamento('Total', $total, true));
    $printer->selectPrintMode();

    /* Footer */
    $printer->feed();
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Muito obrigado, volte sempre!\n");
    $printer->feed();

    /* Cut the receipt and open the cash drawer */
    $printer->cut();
    $printer->pulse();

    $printer->close();

    return 0;
}

return 0;
