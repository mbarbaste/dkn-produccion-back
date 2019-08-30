<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function revRevisacion($pid, $oid, $primera, $segunda, $quinta, $dte, $articulo) {

    $status = "proceso";

    $cantidad = $primera + $segunda + $quinta + $dte;

    // Actualizo ofab
    $query = "UPDATE orden_fabricacion SET revisacion = revisacion - ".$cantidad.", horno_alta = horno_alta + ".$cantidad. " WHERE id = ".$oid;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    if($stmt->execute()) {
        $status = "ok";
    } else {
        $status = "falla";
    }

    $db = null;

    // Actualizo stock revisacion en articulos
    $query = "UPDATE articulos SET 
                        stock_horno_alta = stock_horno_alta + ".($cantidad).", 
                        stock_revisacion_1 = stock_revisacion_1 - ".$primera.",
                        stock_revisacion_2 = stock_revisacion_2 - ".$segunda.", 
                        stock_revisacion_5 = stock_revisacion_5 - ".$quinta.",
                        stock_revisacion_dte = stock_revisacion_dte - ".$dte."  WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }

        $db = null;

    // Elimino registro de tabla carga_bizcocho
    $query = "DELETE FROM `carga_revisacion` WHERE `carga_revisacion`.`id` = ".$pid;
    
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }    
        
    $db = null;

    return $status;
}

$app->post('/api/revertir_revisacion', function(Request $request, Response $response, $args) {

    $parteId = $request->getParsedBody()['parteId'];
    $ordenId = $request->getParsedBody()['ordenId'];
    $articulo = $request->getParsedBody()['articulo'];
    $fecha = $request->getParsedBody()['fecha'];
    $primera = $request->getParsedBody()['primera'];
    $segunda = $request->getParsedBody()['segunda'];
    $quinta = $request->getParsedBody()['quinta'];
    $dte = $request->getParsedBody()['dte'];
    $horno = $request->getParsedBody()['horno'];

    if(revRevisacion($parteId, $ordenId, $primera, $segunda, $quinta, $dte, $articulo) == 'ok') {
        $respuesta = array('status' => 'ok', 'data' => $request->getParsedBody());
    } else {
        $respuesta = array('status' => 'falla', 'data' => $request->getParsedBody());
    }
    

    header("Content-Type: application/json");
    echo json_encode($respuesta);
    //echo json_encode($request->getParsedBody());
 });