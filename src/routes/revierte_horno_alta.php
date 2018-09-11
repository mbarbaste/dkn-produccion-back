<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function revHornoAlta($pid, $oid, $cantid, $rotura, $articulo) {

    $status = "proceso";

    // Actualizo ofab
    $query = "UPDATE orden_fabricacion SET horno_alta = horno_alta - ".$cantid." WHERE id = ".$oid;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    if($stmt->execute()) {
        $status = "ok";
    } else {
        $status = "falla";
    }

    // Actualizo stock blanco - horno de alta en articulos
    $query = "UPDATE articulos SET stock_horno_alta = stock_horno_alta - ".($cantid+$rotura)." WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }

    // Actualizo stock bizcocho en articulos
    $query = "UPDATE articulos SET stock_bizcocho = stock_bizcocho + ".($cantid+$rotura)." WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }        


    // Elimino registro de tabla carga_bizcocho
    $query = "DELETE FROM `carga_horno_alta` WHERE `carga_horno_alta`.`id` = ".$pid;
    
    
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

$app->post('/api/revertir_horno_alta', function(Request $request, Response $response, $args) {

    $parteId = $request->getParsedBody()['parteId'];
    $ordenId = $request->getParsedBody()['ordenId'];

    $articulo = $request->getParsedBody()['articulo'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $rotura = $request->getParsedBody()['rotura'];

    if(revHornoAlta($parteId, $ordenId, $cantidad, $rotura, $articulo) == 'ok') {
        $respuesta = array('status' => 'ok', 'data' => $request->getParsedBody());
    } else {
        $respuesta = array('status' => 'falla', 'data' => $request->getParsedBody());
    }
    

    header("Content-Type: application/json");
    echo json_encode($respuesta);
 });