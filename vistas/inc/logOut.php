<script>

let btn_salir=document.querySelector(".btn-exit-system");

btn_salir.addEventListener('click', function(e){
e.preventDefault();

Swal.fire({
			title: '¿Quieres salir del sistema?',
			text: "La sesion actual se cerrara",
			type: 'question',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Cerrar sesion!',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.value) {
				let url='<?php echo SERVERURL; ?>ajax/loginAjax.php';
                let usuario='<?php echo $lc->encryption($_SESSION['usuario_spm']);?>'; 

                let datos = new FormData();
                datos.append("usuario",usuario);

                fetch(url,{
                    method: 'POST',
                    body: datos
                }).then(respuesta => respuesta.json()).then(respuesta => {

                return alertas_ajax(respuesta);
});
			}
		});
});

</script>