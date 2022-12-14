<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" type="text/css" href="../styles/pix.css">
</head>
<body>
<?php
 include_once('../includesfront/header.php');
 require_once '../vendor/autoload.php';
 unset($_SESSION['carrinho']);
 isset($_SESSION['pagamento_pix']);
 $infos_compra_query = mysqli_query($conexao, "select * from tb_movimento as mov inner join tb_cliente as cli inner join tb_endereco as ende on mov.cod_cliente = {$_SESSION['id_cliente']} and cli.cod_cliente = {$_SESSION['id_cliente']} and ende.cod_cliente = {$_SESSION['id_cliente']} order by cod_movimento desc limit 1;");
 $infos_compra = mysqli_fetch_assoc($infos_compra_query);

 MercadoPago\SDK::setAccessToken("APP_USR-4824210125034889-112015-2c8ba47b6654e5bef3ec032ac5931f36-442749057");
 $payment = new MercadoPago\Payment();

 $payment->transaction_amount = $infos_compra['mov_valor_total'];
 $payment->description = "";
 $payment->payment_method_id = "pix";
 $payment->payer = array(
     "email" => $infos_compra['cli_email'],
     "first_name" => $infos_compra['cli_nome'],
     "identification" => array(
         "type" => "CPF",
         "number" => $infos_compra['cli_cpf'],
      ),
     "address"=>  array(
         "zip_code" => $infos_compra['end_cep'],
         "street_name" => $infos_compra['end_logradouro'],
         "street_number" => $infos_compra['end_numero'],
         "neighborhood" => $infos_compra['end_bairro'],
         "city" => $infos_compra['end_cidade'],
         "federal_unit" => $infos_compra['end_estado'],
      )
   );
   $payment->save();

   if($payment->point_of_interaction->transaction_data->qr_code_base64 === null){
    echo("<script>alert('cpf invalido, entre nas suas configurações para alterar')</script>");
    mysqli_query($conexao, "delete from tb_movimento where cod_cliente = {$infos_compra['cod_cliente']} and compra_status = 'pendente'");
    echo ("<script>document.location='index.php'</script>");
   }
   $query_id_pagamento = mysqli_query($conexao, "update tb_movimento set id_compra = '$payment->id' where cod_movimento = {$infos_compra['cod_movimento']}");
?>
<div class="container_pagamento">
   <h1 class="titulo_pagamento">Pedido Cadastrado</h1>
   <h2 class="subtitulo_pagamento">Leia o Código QR para concluir seu pagamento</h2>
   <div class="qrcode">
<?php
 echo "<img style='width:250px;' src='data:image/png;base64, ".$payment->point_of_interaction->transaction_data->qr_code_base64."'>";
 //echo "<pre>", print_r($payment),"</pre>";
   echo  $payment->transaction_amount;
 echo $payment->id;
?>
        <input type="text" name="qrcode" id="qrcode" class="input_qrcode" value="<?php echo($payment->point_of_interaction->transaction_data->qr_code)?>">
        <button class="botao_copiar" onclick="copiar_texto()">Clique aqui para copiar o código</button>
    </div>
   <div class="tutorial">
        <h1 class="titulo_tutorial">Como fazer pagamentos com Pix:</h1>
            <ol class="conteudo_tutorial">1) Acesse o aplicativo do seu banco</ol>
            <ol class="conteudo_tutorial">2) Procure por pagamentos por Pix</ol>
            <ol class="conteudo_tutorial">3) Procure a opção Pagar QR Code</ol>
            <ol class="conteudo_tutorial">4) Aproxime a câmera do celular no QR Code ao lado</ol>
        <h1 class="titulo_tutorial">Como fazer pagamentos com Internet Banking:</h1>
        <ol class="conteudo_tutorial">1) Copie o código ao lado</ol>
        <ol class="conteudo_tutorial">2) Acesse o Internet Banking ou aplicativo do seu banco</ol>
        <ol class="conteudo_tutorial">3) Procure por pagamentos por Pix</ol>
        <ol class="conteudo_tutorial">4) Procure a opção Copia e Cola e cole o código</ol>
        <a class="botao_concluir" href="teste-brick.php?id=<?php echo($payment->id);?>">Clique aqui quando finalizar o pix para concluir o pagamento</a>  
   </div>
</div>
</body>
<script>
    function copiar_texto() {
        let textoCopiado = document.getElementById("qrcode");
        textoCopiado.select();
        textoCopiado.setSelectionRange(0, 99999)
        document.execCommand("copy");
    }
</script>