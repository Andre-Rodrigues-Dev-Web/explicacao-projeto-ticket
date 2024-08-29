<?php
// iniciando a sessão, pois precisamos pegar o cd do usuario logado para salvar na tabela de vendas no campo cd_cliiente
session_start();  
include 'conexao.php';
$data = date('Y-m-d');  // variavel que vai pegar a data do dia (ano mes dia -padrão do mysql)
$pagamentofeito = 'PENDENTE';  
$ticket = uniqid();  // gerando um ticket com função uniqid();  gera um id unico    
$cd_user = $_SESSION['ID'];  //recebendo o codigo do usuário logado, nesta pagina o usuario ja esta logado pois, em do carrinho de compra
//// criando um loop para sessão carrinho q recebe o $cd e a quantidade
foreach ($_SESSION['carrinho'] as $cd => $qnt)  {
    $consulta = $cn->query("SELECT vl_produto FROM tbl_produtos WHERE id_produto='$cd'");
    $exibe = $consulta->fetch(PDO::FETCH_ASSOC);
    $preco = $exibe['vl_produto'];
 $inserir = $cn->query("INSERT INTO tbl_vendas(nm_ticket,id_cliente,id_produto,qtd_produto,vl_produto,data_venda,pagamentofeito) VALUES
 ('$ticket','$cd_user','$cd','$qnt','$preco','$data','$pagamentofeito')");
}
include 'fim.php';
?>