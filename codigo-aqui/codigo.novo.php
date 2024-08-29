<?php
// arquivo: vendas.php

declare(strict_types=1);

header("Content-Type: application/json");
session_start();

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida se o usuário está logado
    if (!isset($_SESSION['ID'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Usuário não autenticado']);
        exit;
    }

    $data = date('Y-m-d');
    $pagamentofeito = 'PENDENTE';
    $ticket = uniqid();
    $cd_user = $_SESSION['ID'];

    // Verifica se o carrinho existe na sessão
    if (empty($_SESSION['carrinho'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Carrinho vazio']);
        exit;
    }

    // Inicializa array para armazenar erros
    $errors = [];

    // Inicia a transação
    $cn->beginTransaction();

    try {
        // Itera sobre os itens do carrinho
        foreach ($_SESSION['carrinho'] as $cd => $qnt) {
            $consulta = $cn->prepare("SELECT vl_produto FROM tbl_produtos WHERE id_produto = :id_produto");
            $consulta->bindParam(':id_produto', $cd, PDO::PARAM_INT);
            $consulta->execute();
            $exibe = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$exibe) {
                $errors[] = "Produto com ID $cd não encontrado";
                continue;
            }

            $preco = (float)$exibe['vl_produto'];

            $inserir = $cn->prepare("INSERT INTO tbl_vendas (nm_ticket, id_cliente, id_produto, qtd_produto, vl_produto, data_venda, pagamentofeito) 
                                     VALUES (:ticket, :id_cliente, :id_produto, :qtd_produto, :vl_produto, :data_venda, :pagamentofeito)");

            $inserir->bindParam(':ticket', $ticket);
            $inserir->bindParam(':id_cliente', $cd_user);
            $inserir->bindParam(':id_produto', $cd);
            $inserir->bindParam(':qtd_produto', $qnt);
            $inserir->bindParam(':vl_produto', $preco);
            $inserir->bindParam(':data_venda', $data);
            $inserir->bindParam(':pagamentofeito', $pagamentofeito);

            $inserir->execute();
        }

        // Se houver erros, faz rollback
        if (!empty($errors)) {
            $cn->rollBack();
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
        } else {
            // Commita a transação se tudo estiver certo
            $cn->commit();
            http_response_code(201); // Created
            echo json_encode(['success' => 'Venda realizada com sucesso', 'ticket' => $ticket]);
        }

    } catch (PDOException $e) {
        // Em caso de erro, faz rollback
        $cn->rollBack();
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Erro ao processar a venda', 'details' => $e->getMessage()]);
    } catch (Exception $e) {
        // Trata qualquer outra exceção
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Erro inesperado', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método não permitido']);
}
?>
