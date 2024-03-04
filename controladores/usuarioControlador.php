<?php

if($peticionAjax){
    require_once "../modelos/usuarioModelo.php";
}else{
    require_once "./modelos/usuarioModelo.php";
}

class usuarioControlador extends usuarioModelo{

    public function agregar_usuario_controlador(){

        $dni=mainModel::limpiar_cadena($_POST['usuario_dni_reg']);
        $nombre=mainModel::limpiar_cadena($_POST['usuario_nombre_reg']);
		$apellido=mainModel::limpiar_cadena($_POST['usuario_apellido_reg']);
		$telefono=mainModel::limpiar_cadena($_POST['usuario_telefono_reg']);
		$direccion=mainModel::limpiar_cadena($_POST['usuario_direccion_reg']);

		$usuario=mainModel::limpiar_cadena($_POST['usuario_usuario_reg']);
		$email=mainModel::limpiar_cadena($_POST['usuario_email_reg']);
		$clave1=mainModel::limpiar_cadena($_POST['usuario_clave_1_reg']);
		$clave2=mainModel::limpiar_cadena($_POST['usuario_clave_2_reg']);


		$privilegio=mainModel::limpiar_cadena($_POST['usuario_privilegio_reg']);

        /*== comprobar campos vacios ==*/
			if($dni=="" || $nombre=="" || $apellido=="" || $usuario=="" || $clave1=="" || $clave2==""){
				
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No has llenado todos los campos que son obligatorios",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			/*== Comprobando DNI ==*/
			$check_dni=mainModel::ejecutar_consulta_simple("SELECT usuario_dni FROM usuario WHERE usuario_dni='$dni'");
			if($check_dni->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El DNI ingresado ya se encuentra registrado en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			/*== Comprobando usuario ==*/
			$check_user=mainModel::ejecutar_consulta_simple("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$usuario'");
			if($check_user->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE DE USUARIO ingresado ya se encuentra registrado en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}
			/*== Comprobando claves ==*/
			if($clave1!=$clave2){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Las claves que acaba de ingresar no coinciden",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}else{
				$clave=mainModel::encryption($clave1);
			}


			$datos_usuario_reg=[
				"DNI"=>$dni,
				"Nombre"=>$nombre,
				"Apellido"=>$apellido,
				"Telefono"=>$telefono,
				"Direccion"=>$direccion,
				"Email"=>$email,
				"Usuario"=>$usuario,
				"Clave"=>$clave,
				"Estado"=>"Activa",
				"Privilegio"=>$privilegio
			];

			$agregar_usuario=usuarioModelo::agregar_usuario_modelo($datos_usuario_reg);

			if($agregar_usuario->rowCount()==1){
				$alerta=[
					"Alerta"=>"limpiar",
					"Titulo"=>"usuario registrado",
					"Texto"=>"Los datos del usuario han sido registrados con exito",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido registrar el usuario",
					"Tipo"=>"error"
				];
			}
			echo json_encode($alerta);
    }//fin controlador crear usuario

	public function paginador_usuario_controlador($pagina, $registros, $privilegio, $id, $url, $busqueda){

		$pagina=mainModel::limpiar_cadena($pagina);
		$registros=mainModel::limpiar_cadena($registros);
		$privilegio=mainModel::limpiar_cadena($privilegio);
		$id=mainModel::limpiar_cadena($id);
		$url=mainModel::limpiar_cadena($url);
		$url = SERVERURL.$url."/";
		$busqueda=mainModel::limpiar_cadena($busqueda);

		$tabla="";

		$pagina= (isset($pagina) && $pagina>0) ? (int)$pagina : 1 ;
		$inicio=($pagina>0) ? (($pagina*$registros)-$registros) :0 ;

		if(isset($busqueda) && $busqueda!=""){

			$consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM usuario WHERE ((usuario_id!='$id' AND usuario_id!='1') AND (usuario_dni LIKE '%$busqueda%' OR usuario_nombre LIKE '%$busqueda%' OR usuario_apellido LIKE '%$busqueda%'))
			ORDER BY usuario_nombre ASC LIMIT $inicio, $registros";
		}else{
			$consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM usuario WHERE usuario_id!='$id' AND usuario_id!='1'
			ORDER BY usuario_nombre ASC LIMIT $inicio, $registros";

		}
		$conexion = mainModel::conectar();

		$datos = $conexion->query($consulta);
		$datos = $datos->fetchAll();

		$total = $conexion->query("SELECT FOUND_ROWS()");
		$total = (int) $total->fetchColumn();

		$Npaginas=ceil($total/$registros);

		$tabla.='<div class="table-responsive">
		<table class="table table-dark table-sm">
			<thead>
				<tr class="text-center roboto-medium">
					<th>#</th>
					<th>DNI</th>
					<th>NOMBRE</th>
					<th>APELLIDO</th>
					<th>TELÉFONO</th>
					<th>USUARIO</th>
					<th>EMAIL</th>
					<th>ACTUALIZAR</th>
					<th>ELIMINAR</th>
				</tr>
			</thead>
			<tbody>';

			if($total>=1 && $pagina<=$Npaginas){

				$contador=$inicio+1;
				$reg_inicio=$inicio+1;
				foreach($datos as $rows){

					$tabla.='
					<tr class="text-center" >
					<td>'.$contador.'</td>
					<td>'.$rows['usuario_dni'].'</td>
					<td>'.$rows['usuario_nombre'].'</td>
					<td>'.$rows['usuario_apellido'].'</td>
					<td>'.$rows['usuario_telefono'].'</td>
					<td>'.$rows['usuario_usuario'].'</td>
					<td>'.$rows['usuario_email'].'</td>
					<td>
						<a href="'.SERVERURL.'user-update/'.mainModel::encryption($rows['usuario_id']).'/" class="btn btn-success">
							<i class="fas fa-sync-alt"></i>	
						</a>
					</td>
					<td>
						<form class="FormularioAjax" action="'.SERVERURL.'ajax/usuarioAjax.php" method="POST" data-form="delete" autocomplete="off">
						<input type="hidden" name="usuario_id_del" value="'.mainModel::encryption($rows['usuario_id']).'">
							<button type="submit" class="btn btn-warning">
								<i class="far fa-trash-alt"></i>
							</button>
						</form>
					</td>
				</tr>';
				$contador++;

				}
				$reg_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='<tr class="text-center" ><td colspan="9">
					<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm"> Haga click para recargar listado</a>
					</td></tr>';

				}else{
					$tabla.='<tr class="text-center" ><td colspan="9">No hay registros en el sistema</td></tr>';

				}
			}
			$tabla.=' </tbody></table></div>';

			if($total>=1){

				$tabla.='<p class="text-right"> Mostrando usuario '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
			}
			if($total>=1 && $pagina<=$Npaginas){

				$tabla.=mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
			}

			return $tabla;
	}
}