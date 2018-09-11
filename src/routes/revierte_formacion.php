<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function revFormacion($pid, $oid, $cantid, $rotura, $articulo) {

    $status = "proceso";


    // Actualizo ofab
    $query = "UPDATE orden_fabricacion SET formacion = formacion - ".$cantid."  WHERE id = ".$oid;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    if($stmt->execute()) {
        $status = "ok";
    } else {
        $status = "falla";
    }

    $db = null;

    // Actualizo stock formacion en articulos
    $query = "UPDATE articulos SET stock_formacion = stock_formacion - ".$cantid." WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }

        $db = null;

    // Elimino registro de tabla carga_formacion
    $query = "DELETE FROM `carga_formacion` WHERE `carga_formacion`.`id` = ".$pid;
    
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

$app->post('/api/revertir_formacion', function(Request $request, Response $response, $args) {

    $parteId = $request->getParsedBody()['parteId'];
    $ordenId = $request->getParsedBody()['ordenId'];

    $articulo = $request->getParsedBody()['articulo'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $rotura = $request->getParsedBody()['rotura'];

    if(revFormacion($parteId, $ordenId, $cantidad, $rotura, $articulo) == 'ok') {
         $respuesta = array('status' => 'ok', 'data' => $request->getParsedBody());
     } else {
         $respuesta = array('status' => 'falla', 'data' => $request->getParsedBody());
     }
    
    //$respuesta = array('status' => 'ok', 'data' => $request->getParsedBody());
    header("Content-Type: application/json");
    echo json_encode($respuesta);
 });

