<?php

$host   = "localhost";
$user   = "root";
$pass   = "";
$db     = "bd_crud";


$a = $_REQUEST["action"];


switch ( $a ) {
    case "lista":
        carregarLista(); break;
    case "salvar":
        salvarForm(); break;
    case "excluir":
        excluirForm(); break;
    case "buscar":
        carregarCliente(); break;
}


function carregarLista() {
   
    global $host, $user, $pass, $db;
    $mysqli = new mysqli( $host, $user, $pass, $db );
    if ( $mysqli->connect_errno ) { printf("Connect failed: %s\n", $mysqli->connect_error); exit(); }
    
    $sql = "SELECT * FROM cliente ORDER BY id DESC";
    if (!$res = $mysqli->query( $sql )) {
        echo "Erro ao executar SQL<br>";
        echo "Query: ".$sql."<br>";
        echo "Errno: ".$mysqli->errno."<br>";
        echo "Error: ".$mysqli->error."<br>";
        $res->close();
        exit;
    }
    
    if ($res->num_rows === 0) {
        echo "Não a nada cadastrado";
        $res->close();
        exit;
    }
    
    $saida = "<table>";
    while ($d = mysqli_fetch_array($res, MYSQLI_BOTH)) {
        $saida  = $saida. "<tr>"
                . "  <td style='width:25%'><img class=thumb src='/crud/imagens/".$d['foto']."' /></td>"
                . "  <td>"
                . "      <p class=plus>".$d['nome']."</p>"
                . "      <p>".$d['email']."</p>"
                . "      <p>".$d['telefone']."</p>"
                . "  </td>"
                . "  <td style='width:25%'><input type=button class=button value=Editar onClick='carregarCliente(\"".$d['id']."\");'></td>"
                . "  <td style='width:10%'><input type=button class='button delete' value=X onClick='excluirRegistro(\"".$d['id']."\");'></td>"
                . "</tr>";
    }
    $saida = $saida. "</table>";

    echo $saida;
    $res->close();
    $mysqli->close();
}


function carregarCliente() {
   
    if ( ! isset( $_POST ) || empty( $_POST ) ) {
        echo "Dados do formulário não chegaram no PHP.";
        exit;
    }
    
    if ( isset( $_POST["id"] ) && is_numeric( $_POST["id"] ) ) {
        $id = (int) $_POST["id"];

      
        global $host, $user, $pass, $db;
        $mysqli = new mysqli( $host, $user, $pass, $db );
        if ( $mysqli->connect_errno ) { printf("Connect failed: %s\n", $mysqli->connect_error); exit(); }
        
        $stmt = $mysqli->prepare("SELECT * FROM cliente WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $parameters[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);
        while ($stmt->fetch()) {
            foreach($row as $key => $val) {
                $x[$key] = $val;
            }
            $results[] = $x;
        }
        
        echo json_encode( $results );

        $mysqli->close();
    } else {
        echo "ID nao encontrado.";
    }
}


function salvarForm() {
    
    if ( ! isset( $_POST ) || empty( $_POST ) ) {
        echo "Dados do formulário não chegaram no PHP.";
        exit;
    }
    
    $id         = (int) $_POST["id"];
    $nome       = $_POST["nome"];
    $email      = $_POST["email"];
    $telefone   = $_POST["telefone"];
    $foto       = isset( $_FILES['foto'] ) ? $_FILES['foto'] : null;
    $nome_imagem= $_POST["nomeFoto"];
   
    $v = validarForm( $id, $nome, $email, $telefone, $foto );
    if ($v != null) {
        echo "Problema encontrado:<br>".$v;
        exit;
    }
  
    if (! empty( $foto ) ) {
        $imagem_tmp   = $foto['tmp_name'];
        $nome_imagem  = $foto['name']; //basename($foto['name']);
        $diretorio    = $_SERVER['DOCUMENT_ROOT'].'/crud/imagens/';
        $envia_imagem = $diretorio.$nome_imagem;

        if (! move_uploaded_file( $imagem_tmp, $envia_imagem ) ) {
            echo 'Erro ao enviar arquivo de imagem.';
              exit;
        }
    }

    global $host, $user, $pass, $db;
    $mysqli = new mysqli( $host, $user, $pass, $db );
    if ( $mysqli->connect_errno ) { printf("Connect failed: %s\n", $mysqli->connect_error); exit(); }
    
    $sql = null;
    if ( $id > 1 ) {
        $sql = "UPDATE cliente SET nome=?, email=?, telefone=?, foto=? WHERE id=".$id;
    } else {
        $sql = "INSERT INTO cliente (nome, email, telefone, foto) VALUES (?, ?, ?, ?)";
    }
    
    $stmt = $mysqli->prepare( $sql );
    $stmt->bind_param('ssis', $nome, $email, $telefone, $nome_imagem); 
    $stmt->execute();
    
    if ( $id > 1 ) {
        if ( $stmt->affected_rows > 0 ) {
            echo "Cliente atualizado com sucesso!";
        } else {
            echo "Não houve necessidade de atualizar os dados, nenhum valor foi modificado.";
        }
    
    } else {
        if ( $stmt->affected_rows > 0 ) {
            echo "Cliente cadastrado com sucesso!";
        } else {
            echo "Error: ".$stmt;
            exit;
        }
    }

    $mysqli->close();
}


function excluirForm() {
    
    if ( ! isset( $_POST ) || empty( $_POST ) ) {
        echo "Dados do formulário não chegaram no PHP.";
        exit;
    }
    
    if ( isset( $_POST["id"] ) && is_numeric( $_POST["id"] ) ) {
        $id = (int) $_POST["id"];

        
        global $host, $user, $pass, $db;
        $mysqli = new mysqli( $host, $user, $pass, $db );
        if ( $mysqli->connect_errno ) { printf("Connect failed: %s\n", $mysqli->connect_error); exit(); }
        
        $stmt = $mysqli->prepare("DELETE FROM cliente WHERE id=?");
        $stmt->bind_param('i', $id); 
        $stmt->execute();
        
        if ( $stmt->affected_rows > 0 ) {
            echo "Registro deletado com sucesso!";
        } else {
            echo "Error: ".$stmt;
            exit;
        }
        $mysqli->close();
    } else {
        echo "ID invalido para delete.";
    }
}


function validarForm( $id, $nome, $email, $telefone, $foto ) {
    
    if ( $nome == null || trim( $nome ) == "" ) {
        return "Campo Nome deve ser preenchido.";
    }
    
    if ( $email == null || trim( $email ) == "" ) {
        return "Campo Email deve ser preenchido.";
    }
    
    if ( $telefone == null || trim( $telefone ) == "" ) {
        return "Campo Telefone deve ser preenchido.";
    }
    
    if ( empty( $foto ) ) {
        return "A Foto deve ser preenchida.";
    }

    return null;
}
