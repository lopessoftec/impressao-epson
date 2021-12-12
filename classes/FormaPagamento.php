<?php
class FormaPagamento
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
        $right = str_pad($sign . number_format($this->preco, 2, ',', '.'), $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}
