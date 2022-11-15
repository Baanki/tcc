<?php
    include_once('../includesfront/header.php');
    if(empty($_SESSION['login_cliente'])){
        echo "<script>document.location='index.php'</script>";
    } else{
        $id_cliente = $_SESSION['id_cliente'];
    };
    
    $query = mysqli_query($conexao, 'select * from tb_produto where cod_produto = $');  
    $total = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" type="text/css" href="../styles/carrinho.css">
</head>

<body>
    <h1 class="title_carrinho">Carrinho</h1>
    <div class="carrinho_container">
        <table class="carrinho_tabela">
            <tr class="carrinho_linha">
                <th class="carrinho_coluna">Produto</th>
                <th class="carrinho_coluna">Nome</th>
                <th class="carrinho_coluna"  style="padding: 0px">Quantidade</th>
                <th class="carrinho_coluna">Preço</th>
                <th class="carrinho_coluna">Subtotal</th>
            </tr>
<?php
if(isset($_POST['botao_comprar'])){
    $tamanho = mysqli_real_escape_string($conexao, $_POST['checkbox_tamanho']);
    $qtd = mysqli_real_escape_string($conexao, $_POST['qtd_comprar']);
    $qtd = intval($qtd);

    if(isset($_GET['adicionar'])){
        $id_produto = (int) $_GET['adicionar'];
        $query = mysqli_query($conexao, "select * from tb_produto where cod_produto = $id_produto");
        $teste = mysqli_num_rows($query);
        $produto = mysqli_fetch_assoc($query);
        $total = 0;
        if($teste > 0){
            if(isset($_SESSION[$id_produto])){
                $_SESSION['carrinho'][$id_produto]['quantidade']=1;
            }else{
                $_SESSION['carrinho'][$id_produto] = array('cod_produto'=>$produto['cod_produto'],'imagem'=>$produto['prod_imagem'],'quantidade'=>$qtd, 'nome'=>$produto['prod_nome'],'tamanho'=>$tamanho,'preco'=>$produto['prod_preco'], 'subtotal'=>$qtd*$produto['prod_preco']);
            }
            
        }else{
            echo("O produto não existe");
        };
    };   
    foreach ($_SESSION['carrinho'] as $key => $value){
        echo '<tr><td class="carrinho_coluna"><img id="imagem_carrinho" src="'.$value['imagem'].'"?></td>
                  <td class="carrinho_coluna" style="padding: 0px">'.$value['nome'].'<br> Tamanho: '.$value['tamanho'].'</td>
                    
                  <td class="carrinho_coluna">'.$value['quantidade'].'</td>
                  <td class="carrinho_coluna">'.$value['preco'].'</td>
                  <td class="carrinho_coluna">R$'.$value['subtotal'].'</td></tr>';
                  $total += $value['subtotal'];          
    };
}
    ?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="carrinho_coluna"><h2 class="total">Total</h2><br>R$<?php echo $total?></td>
            </tr>
        </table>
        <div class="continuar_finalizar">
            <a href="index.php" class="botao_continuar_finalizar">CONTINUAR COMPRANDO</a>
            <form method="POST">
                <button type="submit" name="finalizar_compra" class="botao_continuar_finalizar">FINALIZAR COMPRA</button>
            </form>
        </div>
    </div>
    <?php
        $hoje = date('d/m/y');
        
        $total_compra = 0;
        echo ("<br>");
        foreach ($_SESSION['carrinho'] as $key => $value){
            $total_compra += $value['subtotal'];
        }
        if(isset($_POST['finalizar_compra'])){
                
                $query = "insert into tb_movimento(mov_data,mov_valor_total,cod_cliente,cod_empresa) values ('$hoje','$total_compra','$id_cliente',1)";
                mysqli_query($conexao, $query);
                
                $_SESSION['cod_movimento'] = $fetch['cod_movimento'];
                echo "<script>document.location='finalizar.php'</script>";
            };
 
        
    ?>
</body>
<?php
//unset($_SESSION['carrinho']);
include_once('../includesfront/footer.php');   

?>