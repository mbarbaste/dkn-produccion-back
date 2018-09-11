<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function actualizaFormacion($oid, $cantid, $articulo) {

    // Actualizo ofab
    $query = "UPDATE orden_fabricacion SET formacion = formacion + ".$cantid." WHERE id = ".$oid;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    $stmt->execute();

    // Actualizo stock formacion en articulos
    $query = "UPDATE articulos SET stock_formacion = stock_formacion + ".$cantid." WHERE articulo = '".$articulo."' LIMIT 1";
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        $stmt->execute();



    $db = null;
}


$app->get('/api/carga_formacion/{oid}', function(Request $request, Response $response, $args){
    
        $oid = $request->getAttribute('oid');
    
        $sql = "SELECT * FROM `carga_formacion` 
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


$app->post('/api/carga_formacion', function(Request $request, Response $response, $args){

    $articulo = $request->getParsedBody()['articulo'];
    $ofab_id = $request->getParsedBody()['ofab_id'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $rotura = $request->getParsedBody()['rotura'];
    $fecha = $request->getParsedBody()['fecha'];
    
    
    $query = "INSERT INTO carga_formacion  
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

            actualizaFormacion($ofab_id, $cantidad, $articulo);

            $respuesta = array('status' => 'ok', 'Insert ID' => $id, 'Carga de Formacion Agregada' => $id.' '.$articulo);
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


$app->post('/api/cerrar_formacion', function(Request $request, Response $response, $args){
    
        $id = $request->getParsedBody()['id'];
        $fecha = $request->getParsedBody()['fecha'];
        $observaciones = $request->getParsedBody()['observaciones'];
        
        
        $query = "UPDATE orden_fabricacion SET 
                    formacion_cerrada = '1',
                    formacion_cerrada_fecha = :fecha,
                    observaciones = :observaciones  
                    WHERE id = :id LIMIT 1"; 

    // $respuesta = array('status' => 'test', 'msg' => $query);                    
                    
    
        try {
            $db = new db();
            $db = $db->connect();
                
            $stmt = $db->prepare($query);
    
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':observaciones', $observaciones);
    
            $stmt->execute();
    
            //$rows = $db->mysqli_affected_rows();
            $rows = 1;
            $db = null;
    
            if($rows > 0) { 
    
                $respuesta = array('status' => 'ok', 'msg' => 'Se ha cerrado carga de formación con exito');
            }
    
        } catch (PDOException $e) {

                $respuesta = array('status' => 'failed', 'msg' => $e->errorInfo[1], 'query' => $query);
        }
    
    
        header("Content-Type: application/json");
        echo json_encode($respuesta);
    });