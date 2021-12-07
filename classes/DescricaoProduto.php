<?php

class DescricaoProduto
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
