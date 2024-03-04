<?php

if ($peticionAjax) {
    require_once "../modelos/loginModelo.php";
} else {
    require_once "./modelos/loginModelo.php";
}

class loginControlador extends loginModelo
{

    public function iniciar_sesion_controlador()
    {
        $usuario = mainModel::limpiar_cadena($_POST['usuario_log']);
        $clave = mainModel::limpiar_cadena($_POST['clave_log']);

        //comprobar campos vacios
        if ($usuario == "" || $clave == "") {

            echo '
        <script>
        Swal.fire({
            title: "Ocurrio un error",
            text:  "No se han incluido los datos necesarios", 
            type:  "error", 
            confirmButtonText: "Aceptar"
          });
        </script>';
            exit();
        }

        $clave = mainModel::encryption($clave);

        $datos_login = [
            "Usuario" => $usuario,
            "Clave" => $clave
        ];

        $datos_cuenta = loginModelo::iniciar_sesion_modelo($datos_login);

        if ($datos_cuenta->rowCount() == 1) {

            $row = $datos_cuenta->fetch();

            session_start(['name' => 'SPM']);

            $_SESSION['id_spm'] = $row['usuario_id'];
            $_SESSION['nombre_spm'] = $row['usuario_nombre'];
            $_SESSION['apellido_spm'] = $row['usuario_apellido'];
            $_SESSION['usuario_spm'] = $row['usuario_usuario'];
            $_SESSION['privilegio_spm'] = $row['usuario_privilegio'];
            $_SESSION['token_spm'] = md5(uniqid(mt_rand(), true));


            return header("Location: " . SERVERURL . "home/");
        } else {
            echo '
        <script>
        Swal.fire({
            title: "Ocurrio un error",
            text:  "El usuario o clave no son correctos", 
            type:  "error", 
            confirmButtonText: "Aceptar"
          });
        </script>';
        }
    }

    //controlador para forzar cierre de sesion

    public function forzar_cierre_sesion_controlador()
    {
        session_unset();
        session_destroy();
        if (true) {

            return "<script> window.location.href='" . SERVERURL . "login/';</script>";
        } else {
            return header("Location: " . SERVERURL . "home/");
        }
    }

    //controlador para cerrar sesion 
    public function cerrar_sesion_controlador(){
        session_start(['name'=>'SPM']);
        $usuario=mainModel::decryption($_POST['usuario']);

        if($usuario==$_SESSION['usuario_spm']){

            session_unset();
            session_destroy();
            $alerta=[

                "Alerta"=>"redireccionar",
                "URL"=>SERVERURL."login/"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No ha sido posible cerrar la sesion",
                "Tipo"=>"error"
            ];
            

        }
        echo json_encode($alerta);
    }
}
