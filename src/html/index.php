<?php
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
 $amount= intval($_POST['amount']);
header ("Access-Control-Allow-Origin: *");
header ("Access-Control-Expose-Headers: Content-Length, X-JSON");
header ("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header ("Access-Control-Allow-Headers: *");


require_once '../vendor/autoload.php';

use Transbank\Webpay\Configuration;
use Transbank\Webpay\Webpay;
?>

<!-- <h1>Ejemplos Webpay - Transaccion Normal</h1> -->

<?php
// function processInput($data) {
//         $data = trim($data);
//         $data = stripslashes($data);
//         $data = htmlspecialchars($data);
//         return strval($data);
//   }

 // $amount = $_POST['amount'];
/** Configuracion parametros de la clase Webpay */
$sample_baseurl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$configuration = Configuration::forTestingWebpayPlusNormal();

/** Creacion Objeto Webpay */
$webpay = new Webpay($configuration);

$action = isset($_GET["action"]) ? $_GET["action"] : 'init';
 $amount= $_GET['amount'];
$post_array = false;
 // $amount = $_POST['amount'];
switch ($action) {

    default:


        $tx_step = "Init";

        /** Monto de la transacción */
        // $amount = 9990;
        // $amount = 
           // $amount = $_POST['amount'];
// echo $amount;
        /** Orden de compra de la tienda */
        $buyOrder = rand();

        /** Código comercio de la tienda entregado por Transbank */
        $sessionId = uniqid();

        /** URL de retorno */
        $urlReturn = $sample_baseurl."?action=getResult";

        /** URL Final */
	// $urlFinal  = $sample_baseurl."?action=end";

    $urlFinal  = $sample_baseurl."?action=end";

        $request = array(
            "amount"    =>  $amount,
            "buyOrder"  => $buyOrder,
            "sessionId" => $sessionId,
            "urlReturn" => $urlReturn,
            "urlFinal"  => $urlFinal,
        );

        /** Iniciamos Transaccion */
        $result = $webpay->getNormalTransaction()->initTransaction($amount, $buyOrder, $sessionId, $urlReturn, $urlFinal);

        /** Verificamos respuesta de inicio en webpay */
        if (!empty($result->token) && isset($result->token)) {
            $message = "conectando con tbk...";
            $token = $result->token;
            $next_page = $result->url;
        } else {
            // header('Location: http://localhost:4200/cart?buyOrder=buyOrder');
            $message = "webpay no disponible";
        }

        $button_name = "dale &raquo;";




        break;

    case "getResult":

        $tx_step = "Get Result";

        if (!isset($_POST["token_ws"]))
            break;

        /** Token de la transacción */
        $token = filter_input(INPUT_POST, 'token_ws');

        $request = array(
            "token" => filter_input(INPUT_POST, 'token_ws')
        );

        /** Rescatamos resultado y datos de la transaccion */
        $result = $webpay->getNormalTransaction()->getTransactionResult($token);

        /** Verificamos resultado  de transacción */
        if ($result->detailOutput->responseCode === 0) {

            /** propiedad de HTML5 (web storage), que permite almacenar datos en nuestro navegador web */
            echo '<script>window.localStorage.clear();</script>';
            echo '<script>localStorage.setItem("authorizationCode", '.$result->detailOutput->authorizationCode.')</script>';
            echo '<script>localStorage.setItem("amount", '.$result->detailOutput->amount.')</script>';
            echo '<script>localStorage.setItem("buyOrder", '.$result->buyOrder.')</script>';



            $message = "Pago ACEPTADO por webpay (se deben guardatos para mostrar voucher)";






            $next_page = $result->urlRedirection;
                        //API URL
$url = 'https://db.lameseria.cl:3029/api/sale';

//create a new cURL resource
$ch = curl_init($url);
//setup request to send json via POST
$data = array(
    'amount' =>$result->detailOutput->amount,
    'buyOrder'=> $result->detailOutput->buyOrder,
    'status' => 'pending',
    'date'=>$result->transactionDate 
);
$payload = json_encode(array($data));

//attach encoded JSON string to the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

//set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

//return response instead of outputting
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//execute the POST request
$result = curl_exec($ch);

//close cURL resource
curl_close($ch);


        } else {
            $message = "Pago RECHAZADO por webpay - " . utf8_decode($result->detailOutput->responseDescription);
            $next_page = '';
        }

        $button_name = "pa alla &raquo;";

        break;

    case "end":

        $post_array = true;

        $tx_step = "End";
        $request = "";
        $result = $_POST;

        $message = "Transacion Finalizada";
        $next_page = $sample_baseurl."?action=nullify";
        $button_name = "Anular Transacci&oacute;n &raquo;";

        break;


    case "nullify":

        $tx_step = "nullify";

        $request = $_POST;

        /** Codigo de Comercio */
        $commercecode = null;

        /** Código de autorización de la transacción que se requiere anular */
        $authorizationCode = filter_input(INPUT_POST, 'authorizationCode');

        /** Monto autorizado de la transacción que se requiere anular */
        $amount =  filter_input(INPUT_POST, 'amount');

        /** Orden de compra de la transacción que se requiere anular */
        $buyOrder =  filter_input(INPUT_POST, 'buyOrder');

        /** Monto que se desea anular de la transacción */
        $nullifyAmount = 200;

        $request = array(
            "authorizationCode" => $authorizationCode, // Código de autorización
            "authorizedAmount" => $amount, // Monto autorizado
            "buyOrder" => $buyOrder, // Orden de compra
            "nullifyAmount" => $nullifyAmount, // idsession local
            "commercecode" => $configuration->getCommerceCode(), // idsession local
        );

        $result = $webpay->getNullifyTransaction()->nullify($authorizationCode, $amount, $buyOrder, $nullifyAmount, $commercecode);

        /** Verificamos resultado  de transacción */
        if (!isset($result->authorizationCode)) {
            // header('Location: http://localhost:4200/cart?buyOrder='.$buyOrder);
            $message = "webpay no disponible";
        } else {
            $message = "Transaci&oacute;n Finalizada";
        }

        $next_page = '';

        break;
}

// echo "<h2>Step: " . $tx_step . "</h2>";

if (!isset($request) || !isset($result) || !isset($message) || !isset($next_page)) {

    $result = "Ocurri&oacute; un error al procesar tu solicitud";
    echo "<div style = 'background-color:lightgrey;'><h3>result</h3>$result;</div><br/><br/>";
    echo "<a href='../..'>&laquo; volver a index</a>";
    die;
}

/* Respuesta de Salida - Vista WEB */
?>

<!-- <div style="background-color:lightyellow;">
	<h3>request</h3>
	<?php  var_dump($request); ?>
</div>
<div style="background-color:lightgrey;">
	<h3>result</h3>
	<?php  var_dump($result); ?>
</div> -->
<p><samp><?php  echo $message; ?></samp></p>

<?php if (strlen($next_page) && $post_array) { ?>

        <form name="tForm2" id="tForm2" action="http://localhost:4200" >
            <input type="hidden" name="authorizationCode" id="authorizationCode" value="">
            <input type="hidden" name="amount" id="amount" value="">
            <input type="hidden" name="buyOrder" id="buyOrder" value="">
            <input type="submit" value="<?php echo $button_name; ?>">
            
        </form>

        <script>

            var authorizationCode = localStorage.getItem('authorizationCode');
            document.getElementById("authorizationCode").value = authorizationCode;

            var amount = localStorage.getItem('amount');
            document.getElementById("amount").value = amount;

            var buyOrder = localStorage.getItem('buyOrder');
            document.getElementById("buyOrder").value = buyOrder;
 document.getElementById('tForm2').submit();
            // localStorage.clear();
             
        </script>

<?php } elseif (strlen($next_page)) { ?>
    <form name="tForm" id="tForm" action="<?php echo $next_page; ?>" method="post">

    <input type="hidden" name="token_ws" value="<?php echo ($token); ?>">
    <!-- <input type="submit" value="<?php echo $button_name; ?>"> -->
</form>
<script type="text/javascript">
     document.getElementById('tForm').submit();
</script>
<?php } ?>

<br>
<!-- <a href="../..">&laquo; volver a index</a> -->
