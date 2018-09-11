<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function actualizaOfabBlanco($oid, $cantid) {

    $query = "UPDATE orden_fabricacion SET blanco = blanco + ".$cantid." WHERE id = ".$oid;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    $stmt->execute();
    $db = null;

}

/* function descargaOfabBlanco($oid, $cantid) {
    
        $query = "UPDATE orden_fabricacion SET bizcocho = bizcocho - ".$cantid." WHERE id = ".$oid;
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        $stmt->execute();
        $db = null;
    
    } */

function descargaStockBizcocho($articulo, $cantidad, $rotura) {

        $cantidadTotal = $cantidad + $rotura;
    
        $db = new db();
        $db = $db->connect();
        
        $query = "UPDATE articulos SET stock_bizcocho = stock_bizcocho - ".$cantidadTotal." WHERE articulo = '".$articulo."' LIMIT 1";
        $stmt = $db->prepare($query);    
        $stmt->execute();

        $query = "UPDATE articulos SET stock_blanco = stock_blanco + ".$cantidad." WHERE articulo = '".$articulo."' LIMIT 1";
        $stmt = $db->prepare($query);    
        $stmt->execute();


        $db = null;
    }

$app->get('/api/carga_horno_alta/{oid}', function(Request $request, Response $response, $args){
    
        $oid = $request->getAttribute('oid');
    
        $sql = "SELECT * FROM `carga_horno_alta` 
                WHERE ofab_id = '".$oid."'";
    
        // echo json_encode(array('sql' => $sql));
    
        try {
            $db = new db();
            $db = $db->connect();
    
            $stmt = $db->query($sql);
            $respuesta = $stmt->fetchAll(PDO::FETCH_OBJ);           
    
        } catch(PDOException $e) {
            $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
        }
        $db = null;
        header("Content-Type: application/json");
        echo json_encode($respuesta);
    });     

$app->post('/api/carga_horno_alta', function(Request $request, Response $response, $args){

    $articulo = $request->getParsedBody()['articulo'];
    $ofab_id = $request->getParsedBody()['ofab_id'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $rotura = $request->getParsedBody()['rotura'];
    $fecha = $request->getParsedBody()['fecha'];
    $horno = $request->getParsedBody()['horno'];
    
    
    $query = "INSERT INTO carga_horno_alta  
                (articulo, cantidad, rotura, fecha, ofab_id, horno) 
                VALUES 
                (:articulo, :cantidad, :rotura, :fecha, :ofab_id, :horno) ";   

    try {
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);

        $stmt->bindParam(':articulo', $articulo);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':rotura', $rotura);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':ofab_id', $ofab_id);
        $stmt->bindParam(':horno', $horno);

        $stmt->execute();

        $id = $db->lastInsertId();
        $db = null;

        if($id > 0) { 

            $salidaBizcocho = $cantidad + $rotura;

            actualizaOfabBlanco($ofab_id, $cantidad);           
            // descargaStockBizcocho($articulo, $salidaBizcocho);
            descargaStockBizcocho($articulo, $cantidad, $rotura);

            $respuesta = array('status' => 'ok', 'Insert ID' => $id, 'Carga de Horno de Alta Agregada' => $id.' '.$articulo);
        }

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $respuesta = array('status' => 'failed', 'msg' => 'Ya existe la orden '.$id );
        } else {
            $respuesta = array('status' => 'failed','error' => $e->errorInfo[1]);
        }
    }

    header("Content-Type: application/json");
    echo json_encode($respuesta);
});
