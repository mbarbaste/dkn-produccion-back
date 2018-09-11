<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function actualizaOfab($oid, $cantid) {


    $db = new db();
    $db = $db->connect();  
    
    $query = "UPDATE orden_fabricacion SET bizcocho = bizcocho + ".$cantid." WHERE id = ".$oid;

    $stmt = $db->prepare($query);
    $stmt->execute();

    // $query = "UPDATE orden_fabricacion SET formacion = formacion - ".$cantid." WHERE id = ".$oid;
       
    // $stmt = $db->prepare($query);
    // $stmt->execute();

    // $db = null;
}

function actualizaStockBizcocho($articulo, $cantidad, $rotura) {
    
        // Actualizo stocks bizcocho
        $cantidadTotal = $cantidad - $rotura;

        $query = "UPDATE articulos SET stock_bizcocho = stock_bizcocho + ".$cantidadTotal." WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();

        $db = $db->connect();            
        $stmt = $db->prepare($query);    
        $stmt->execute();

        $db = null;

        // Actualizo stock formacion
        $query = "UPDATE articulos SET stock_formacion = stock_formacion - ".$cantidad." WHERE articulo = '".$articulo."' LIMIT 1";
        
        $db = new db();
        $db = $db->connect();            
        $stmt = $db->prepare($query);
    
        $stmt->execute();

        $db = null;
    }


$app->get('/api/carga_bizcocho/{oid}', function(Request $request, Response $response, $args){
    
        $oid = $request->getAttribute('oid');
    
        $sql = "SELECT * FROM `carga_bizcocho` 
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


$app->post('/api/carga_bizcocho', function(Request $request, Response $response, $args){

    $articulo = $request->getParsedBody()['articulo'];
    $ofab_id = $request->getParsedBody()['ofab_id'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $rotura = $request->getParsedBody()['rotura'];
    $fecha = $request->getParsedBody()['fecha'];
    
    
    $query = "INSERT INTO carga_bizcocho  
                (articulo, cantidad, rotura, fecha, ofab_id) 
                VALUES 
                (:articulo, :cantidad, :rotura, :fecha, :ofab_id) ";   

    try {
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);

        $stmt->bindParam(':articulo', $articulo);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':rotura', $rotura);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':ofab_id', $ofab_id);

        $stmt->execute();

        $id = $db->lastInsertId();
        $db = null;

        if($id > 0) { 

            actualizaOfab($ofab_id, $cantidad);
            actualizaStockBizcocho($articulo, $cantidad,$rotura);

            $respuesta = array('status' => 'ok', 'Insert ID' => $id, 'Carga de Horno de Bizcocho Agregada' => $id.' '.$articulo);
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
