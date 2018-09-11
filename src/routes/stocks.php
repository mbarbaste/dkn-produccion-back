<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/stocks/{articulo}', function(Request $request, Response $response, $args){
    
        $articulo = $request->getAttribute('articulo');

        if(strlen($articulo) == 6) {
            $sql = "SELECT * FROM articulos WHERE articulo ='".$articulo."'";
        } else {
    
        $sql = "SELECT * FROM `articulos` 
                WHERE articulo LIKE '".$articulo."%' 
                AND (
                stock_formacion > 0
                OR stock_bizcocho > 0 
                OR stock_horno_alta > 0 
                OR stock_revisacion_1 > 0 
                OR stock_revisacion_2 > 0 
                OR stock_revisacion_5 > 0)";
        }
        //echo json_encode(array('sql' => $sql));
    
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

    $app->post('/api/stocks', function(Request $request, Response $response, $args){
        
            $id = $request->getParsedBody()['id'];
            $stock_formacion = $request->getParsedBody()['stock_formacion'];
            $stock_bizcocho = $request->getParsedBody()['stock_bizcocho'];
            $stock_horno_alta = $request->getParsedBody()['stock_horno_alta'];
            $stock_revisacion_1 = $request->getParsedBody()['stock_revisacion_1'];
            $stock_revisacion_2 = $request->getParsedBody()['stock_revisacion_2'];
            $stock_revisacion_5 = $request->getParsedBody()['stock_revisacion_5'];
            
            
            $query = "UPDATE articulos SET 
                        stock_formacion = :stock_formacion,
                        stock_bizcocho = :stock_bizcocho,
                        stock_horno_alta = :stock_horno_alta,
                        stock_revisacion_1 = :stock_revisacion_1, 
                        stock_revisacion_2 = :stock_revisacion_2, 
                        stock_revisacion_5 = :stock_revisacion_5
                        WHERE id = :id LIMIT 1 ";
        
            try {
                $db = new db();
                $db = $db->connect();
                    
                $stmt = $db->prepare($query);
        
                $stmt->bindParam(':stock_formacion', $stock_formacion);
                $stmt->bindParam(':stock_bizcocho', $stock_bizcocho);
                $stmt->bindParam(':stock_horno_alta', $stock_horno_alta);
                $stmt->bindParam(':stock_revisacion_1', $stock_revisacion_1);
                $stmt->bindParam(':stock_revisacion_2', $stock_revisacion_2);
                $stmt->bindParam(':stock_revisacion_5', $stock_revisacion_5);


                $stmt->bindParam(':id', $id);
        
                $stmt->execute();
        
                $rows = $stmt->rowCount();
                $db = null;
        
                if($rows > 0) { 
                    $respuesta = array('status' => 'ok', 'msg' => 'Se modificó el stock del artículo '.$articulo);
                }
        
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $respuesta = array('status' => 'failed', 'msg' => 'Ya existe la orden '.$id );
                } else {
                    $respuesta = array('status' => 'failed','msg' => $e->errorInfo);
                }
            }
        
            header("Content-Type: application/json");
            echo json_encode($respuesta);
        });