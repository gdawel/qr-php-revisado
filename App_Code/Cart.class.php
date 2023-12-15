<?php
include_once 'SqlHelper.class.php';
include_once 'User.class.php';
include_once 'Inscricao.class.php';
include_once dirname(__FILE__).'/../Includes/PagSeguroLibrary/PagSeguroLibrary.php';

class Cart
{
    var $error;
    var $paymentRequest;
    var $credentials;
    var $code;
    var $customErrorMessages = array(
        10001 => "Email ? obrigat?rio.",
        10002 => "Token ? obrigat?rio.",
        10003 => "Email inv?lido.",
        11001 => "Email do Recebedor ? obrigat?rio.",
        11002 => "Tamanho inv?lido do Email do Recebedor.",
        11003 => "Email do Recebedor ? inv?lido.",
        11004 => "Moeda ? obrigat?ria.",
        11009 => "Email inv?lido.",
        11010 => "Email inv?lido.",
        11011 => "Nome inv?lido.",
        11012 => "Nome inv?lido.",
        11013 => "DDD inv?lido.",
        11014 => "Telefone inv?lido.",
        11017 => "CEP inv?lido.",
        11018 => "Endere?o inv?lido.",
        11019 => "N?mero do endere?o inv?lido.",
        11020 => "Complemento do endere?o inv?lido.",
        11021 => "Bairro inv?lido.",
        11022 => "Cidade inv?lida.",
        11023 => "UF inv?lida.",
        11157 => "CPF inv?lido.",
    );

    function __construct()
    {
        $this->paymentRequest = new PaymentRequest();
        $this->paymentRequest->setCurrency("BRL");
        $this->paymentRequest->setShippingType(1);
        $this->paymentRequest->setMaxAge(60*10); //60*60*24*1 = 1d
        
        $this->getCredentials();
    }
    
    function getCredentials() {
        /*// Informando as credenciais  
        $credentials = new AccountCredentials(  
            'contato@sobrare.com.br',   
            '95112EE828D94278BD394E91C4388F20'  
        ); */
        
        /* Obtendo credenciais definidas no arquivo de configura??o */  
        $this->credentials = PagSeguroConfig::getAccountCredentials();
        return true;
        //return $credentials; 
    }
    
    function addItem($id, $description, $amount, $quantity = 1) {
        $this->paymentRequest->addItem($id, $description, $quantity, $amount);  
    }
    
    function setSender($responsavel) {
        $responsavel->uf = strtoupper($responsavel->uf); 
        
        $this->paymentRequest->setSender(  
            $responsavel->nome,
            $responsavel->email,
            substr($responsavel->telefonecomercial, 1, 2),   
            str_replace('-', '', substr($responsavel->telefonecomercial, 5, 9))
        ); 
        
        $this->paymentRequest->setShippingAddress(  
            str_replace('-', '', $responsavel->cep),   
            $responsavel->endereco,       
            $responsavel->numero,       
            $responsavel->complemento,       
            $responsavel->bairro,      
            $responsavel->cidade,      
            $responsavel->uf,     
            'BRA'     
        ); 
    }
        
    function setReference($inscricao) {
        //set my ref
        $this->paymentRequest->setReference("C$inscricao->id");
        
        //set redirect url
        if ($inscricao->cursoid == 19) //especial para o cursoId = 19: Forum
            $url = "http://www.forumderesiliencia.com/confirmacao.html?c=$inscricao->id";
        else {
            $url = "http://www.sobrare.com.br/cursos_inscricao.php?a=retorno&c=$inscricao->id";
            //$url = $this->paymentRequest->setRedirectURL("http://www.morsan.com.br/sobrare/cursos_inscricao.php?a=retorno&c=$inscricao->id");
        }
        $this->paymentRequest->setRedirectURL($url); 
    }
    
    function register() {
        try {
            $url = $this->paymentRequest->register($this->credentials);
            
            //get transaction code
            $v = explode("code=", $url);            
            $this->code = $v[1];
            
            //return
            return $url;
        
        } catch (PagSeguroServiceException $e) {              
            foreach ($e->getErrors($e) as $key => $error) { 
                $code = $error->getCode();

                if (isset($this->customErrorMessages[$code]))
                    $errorMessage = $this->customErrorMessages[$code];
                else
                    $errorMessage = $error->getMessage();

                $ps_errors[] = "[$code] $errorMessage";
            }

            if (isset($ps_errors))
                $this->error = join('<br />', $ps_errors);
            else 
                $this->error = '['.$e->getHttpStatus()->getStatus().'] '.$e->getHttpStatus()->getType();;
                
            return false;
            
        } catch (ErrorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    } 
    
    function logRetornoPagSeguro() {
        //Plain POST
        $query_string = "";
        $code = "N/C";
        if ($_POST) {
          $kv = array();
          foreach ($_POST as $key => $value) {
            $kv[] = "$key=$value";
          }
          $query_string = join("\n ", $kv);  
        }
        
        
        $sql = new SqlHelper();
        $sql->command = "INSERT INTO pagseguro_log (log_type, log_date, description, transaction) VALUES (
                            'Log', 
                            now(), ".
                            $sql->escape_string($query_string, true).", ".
                            "NULL)";
        
        $sql->execute();
    }
    
    function checkTransactionStatus() {
        /* Informando as credenciais  */    
        $credentials = PagSeguroConfig::getAccountCredentials();  
		 
        /* Tipo da transacao */
        echo "Fetching type...<br />"; 
        $type = isset($_POST['notificationType']) ? $_POST['notificationType'] : 'Not Identified';        
               
        /* C?digo da notifica??o recebida */
        echo "Fetching code...<br />";  
        $code = isset($_POST['notificationCode']) ? $_POST['notificationCode'] : 'Not Identified';  
        
        echo "-  Type: $type<br /> 
              -  Code: $code<br />";
          
        //New Sql Helper
        $sql = new SqlHelper();
                  
        /* Verificando tipo de notifica??o recebida */  
        if ($type == 'transaction') {  
            /* Obtendo o objeto Transaction a partir do c?digo de notifica??o */  
            $transaction = NotificationService::checkTransaction(  
                $credentials,  
                $code // c?digo de notifica??o  
            );  
            
            if ($transaction) {
                echo "Updating status...<br />";
                $status = $transaction->getStatus()->getValue();
                $reference = $transaction->getReference();
                $inscricaoId = substr($reference, 1); //C????
                $transaction_code = $transaction->getCode();
                echo "-  Transaction: $transaction_code<br />
                      -  Reference: $reference<br />
                      -  Status: $status<br />";
                        
                //update database
                $sql->command = "UPDATE cursos_inscricoes SET 
                                    StatusId = ".$sql->escape_string($status, true).",
                                    PagSeguroCode = ".$sql->escape_string($code, true).", 
                                    PagSeguroTransaction = ".$sql->escape_string($transaction_code, true)." 
                                WHERE InscricaoId = ".$sql->escape_string($inscricaoId, true);
                
                echo $sql->command;
                echo "<br />";
                
                if ($sql->execute()) $result = "Status Updated";
                else $result = "Failed updating status";
                echo "$result!<br />";
                
                //Log it
                $sql->command = "INSERT INTO pagseguro_log (log_type, log_date, description, transaction, reference, statusId) VALUES (
                                    '$result',
                                    now(), ".
                                    $sql->escape_string("Code: $code | TransactionCode: $transaction_code", true).", ".
                                    $sql->escape_string($transaction_code, true).", ".
                                    $sql->escape_string($reference, true).", ".
                                    $sql->escape_string($status, true).
                                    ")";
                
                $sql->execute();
                
                //send notifications
                $inscricoes = new Inscricoes();
                $inscricoes->sendNotificationOnStatusChange($inscricaoId);
                
            } else {
                echo "Transaction not found!<br />";
                $sql->command = "INSERT INTO pagseguro_log (log_type, log_date, description, transaction, reference, statusId) VALUES (
                                    'Transaction Not Found',
                                    now(), ".
                                    $sql->escape_string("Code: $code | TransactionCode: $transaction_code", true).", ".
                                    $sql->escape_string($transaction_code, true).", ".
                                    $sql->escape_string($reference, true).", ".
                                    $sql->escape_string($status, true).
                                    ")";
                
                $sql->execute();
            }
        
        }  else {
            if (!isset($transaction_code)) $transaction_code = 'N/A';
            
            echo "Not a transaction type!<br />";
            $sql->command = "INSERT INTO pagseguro_log (log_type, log_date, description, transaction) VALUES (
                                'Not a Transaction', 
                                now(), ".
                                $sql->escape_string("Code: $code | Type: $type", true).", ".
                                $sql->escape_string($transaction_code, true).")";
            
            $sql->execute();    
        }
    } //checkTransactionStatus
}
?>