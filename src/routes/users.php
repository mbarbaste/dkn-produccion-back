<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/users/list', function(Request $request, Response $response){
    $sql = "SELECT username, display_name FROM users";

    try {
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $respuesta = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;   

    } catch(PDOException $e) {
        $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
    }
    header("Content-Type: application/json");
    echo json_encode($respuesta);
});

$app->post('/api/users/register', function(Request $request, Response $response, $args){

    $username = $request->getParsedBody()['username'];
    $password = $request->getParsedBody()['password'];
    $display_name = $request->getParsedBody()['displayName'];
    $password = sha1($password);
    
    $query = "INSERT INTO users 
                (username, password, display_name) 
                VALUES 
                (:username, :password, :display_name)";   

    try {
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':display_name', $display_name);

        $stmt->execute();

        $id = $db->lastInsertId();
        //$db = null;

        if($id > 0) { 
            $respuesta = array('status' => 'ok', 'Insert ID' => $id, 'User Added' => $username);
        }

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $respuesta = array('status' => 'failed', 'msg' => 'Ya existe el usuario '.$username );
        } else {
            $respuesta = array('status' => 'failed','msg' => $e->errorInfo[1]);
        }
    }

    header("Content-Type: application/json");
    echo json_encode($respuesta);
});


$app->post('/api/login', function(Request $request, Response $response, $args) {
    $username = $request->getParsedBody()['username'];
    $password = $request->getParsedBody()['password'];

    //$username = 'mbar';

    $query = "SELECT * FROM `users` WHERE username = '".$username."' LIMIT 1";    

    try {
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($query);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;

        if(empty($data)) {
            $respuesta = array('status' => 'failed', 'msg' => 'No Autorizado');
        }

        if($data){
            if (sha1($password) != $data[0]['password']) {          
                $data = null;
                $respuesta = array('status' => 'failed', 'msg' => 'No Autorizado');
            }
        }

         if($data) {
            $server = 'slimapp.app';
            //$msg = array('token' => token($username,$id,$server));
            //header("Content-Type: application/json");
            $token = token($data[0]['username'],$data[0]['id'], $server);
            $respuesta = array('status' => 'ok', 'id' => $data[0]['id'], 
                'username' => $data[0]['username'],
                'email' => $data[0]['email'],
                'level' => $data[0]['level'],
                'displayName' => $data[0]['display_name'],
                'token' => $token
            );
        }

        } catch(PDOException $e) {
         $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
    }

    header("Content-Type: application/json");
    echo json_encode($respuesta);

});

$app->post('/api/user/change-password', function(Request $request, Response $response, $args) {

    $username = $request->getParsedBody()['username'];
    $actualPassword = $request->getParsedBody()['actualPassword'];
    $newPassword = $request->getParsedBody()['newPassword'];
    $newPasswordConfirm = $request->getParsedBody()['newPasswordConfirm'];

    if($newPassword != $newPasswordConfirm) {
        $respuesta = array('status' => 'failed', 'msg' => 'Las Claves no coinciden');
        header("Content-Type: application/json");
        echo json_encode($respuesta);
        
    } else {

        $query = "SELECT * FROM `users` WHERE username = '".$username."' LIMIT 1";    
        
            try {
                $db = new db();
                $db = $db->connect();
        
                $stmt = $db->query($query);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
        
                if(empty($data)) {
                    $respuesta = array('status' => 'failed', 'msg' => 'No Autorizado. No existe Usuario');
                }
        
                if($data){
                    if (sha1($actualPassword) != $data[0]['password']) {          
                        $data = null;
                        $respuesta = array('status' => 'failed', 'msg' => 'No Autorizado');
                    } else {
                        // Cambio de Password
                        $query = "UPDATE users SET password = '".sha1($newPassword)."' WHERE id=".$data[0]['id']." LIMIT 1";
                        $db->query($query);
                        $respuesta = array('status' => 'ok', 'msg' => 'Cambio de Clave existosa');
                    }
                }      
                
        
        
            } catch(PDOException $e) {
                 $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
            }

            $db = null;
            header("Content-Type: application/json");
            echo json_encode($respuesta);


    }

    //$username = 'mbar';

   

});

