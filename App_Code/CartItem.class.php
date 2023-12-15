<?php
class CartItem
{
    var $pacote, $qtde;

    function __construct($pacote, $qtde)
    {
        $this->pacote = $pacote;
        $this->qtde = $qtde;
    }

    function addProduto($produtoId)
    {
        if ($this->pacote->produtos[$produtoId])
            $this->pacote->produtos[$produtoId]->selected = true;
    }

    function removeProduto($produtoId)
    {
        if ($this->pacote->produtos[$produtoId])
            $this->pacote->produtos[$produtoId]->selected = false;
    }

    function clearSelectedProdutos()
    {
        foreach ($this->pacote->produtos as $p) {
            if (!$p->obrigatorio) $p->selected = false;
        }
    }

    function getValor()
    {
        $valor = 0;
        foreach ($this->pacote->produtos as $p) {
            if ($p->selected || $p->obrigatorio) {
                if ($p->porpacote)
                    $valor = $valor + ($p->preco);
                else
                    $valor = $valor + ($p->preco * $this->qtde);
            }
        }
        return $valor;
    }
}
?>