<?php
class ItensVenda
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
