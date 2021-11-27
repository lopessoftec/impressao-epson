<?php
require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;

$connector = new FilePrintConnector("/dev/usb/lp1");

/* Information for the receipt */
$data = json_decode(file_get_contents('php://input'), true);

// var_dump($data['sale_filter']);
// die();

$items = [];
$total = 0;
foreach ($data['sale_filter'] as $value) {

    $items[] = new descricaoProduto($value['barcode'], $value['product_name']);
    $items[] = new itensVenda($value['amount'], $value['sale_detail_value'], $value['final_value']);
    $total += $value['final_value'];
}

$items_payment = [];
var_dump($data['formas_pagamento_filter']);
die();
foreach ($data['formas_pagamento_filter'] as $value) {

    $items_payment[] = new formaPagamento($value['description'], $value['payment_sale_value']);
}

// var_dump($items);
// die();

// $total = new item('Total', $total, true);

/* Start the printer */
// $logo = EscposImage::load("escpos-php.png", false);
$printer = new Printer($connector);

/* Print top logo */
// $printer->setJustification(Printer::JUSTIFY_CENTER);
// $printer->graphics($logo);

/* Name of shop */
// $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
// $printer->text("ExampleMart Ltd.\n");
// $printer->selectPrintMode();

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text(date('d/m/Y - h:i:s') . "\n");
$printer->feed();

/* Title of receipt */
$printer->setEmphasis(true);
$printer->text("Descrição\n");
$printer->setEmphasis(false);

/* Items */
$printer->setJustification(Printer::JUSTIFY_LEFT);
// $printer->setEmphasis(true);
// $printer->text(new item('', '$'));
// $printer->setEmphasis(false);

// $printer->setTextSize(1, 1);
foreach ($items as $item) {
    $printer->text($item);
}

foreach ($items_payment as $item_payment) {
    $printer->text($item_payment);
}

// $printer->setEmphasis(true);
// $printer->text($subtotal);
// $printer->setEmphasis(false);
// $printer->feed();

/* Tax and total */
// $printer->text($tax);
$printer->feed(2);
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text(new formaPagamento('Total', $total));
$printer->selectPrintMode();

// $total = new item('Total', $total, true);

/* Footer */
$printer->feed(2);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("Muito obrigado, volte sempre!\n");
$printer->feed(2);

/* Cut the receipt and open the cash drawer */
$printer->cut();
$printer->pulse();

$printer->close();

class descricaoProduto
{
    private $codigo;
    private $nome;
    private $dollarSign;

    public function __construct($codigo = '', $nome = '', $dollarSign = false)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->dollarSign = $dollarSign;
    }

    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this->dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }

        $left = str_pad($this->codigo . ' - ' . $this->nome, $leftCols);

        return "$left\n";
    }
}

/* A wrapper to do organise item names & prices into columns */
class itensVenda
{
    private $quantidade;
    private $preco;
    private $dollarSign;

    public function __construct($quantidade = '', $preco = '', $precoSubtotal, $dollarSign = false)
    {
        $this->quantidade = $quantidade;
        $this->preco = $preco;
        $this->precoSubtotal = $precoSubtotal;
        $this->dollarSign = $dollarSign;
    }

    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this->dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this->quantidade . ' * ' . $this->preco, $leftCols);

        $sign = ($this->dollarSign ? 'R$ ' : '');
        $right = str_pad($sign . $this->precoSubtotal, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}

class formaPagamento
{
    private $formaPagamento;
    private $preco;
    private $dollarSign;

    public function __construct($formaPagamento = '', $preco = '', $dollarSign = false)
    {
        $this->formaPagamento = $formaPagamento;
        $this->preco = $preco;
        $this->dollarSign = $dollarSign;
    }

    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this->dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this->formaPagamento, $leftCols);

        $sign = ($this->dollarSign ? 'R$ ' : '');
        $right = str_pad($sign . $this->preco, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}
