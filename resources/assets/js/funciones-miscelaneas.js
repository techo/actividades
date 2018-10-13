window.addEventListener('load', function() {
	

	/// para que un [enter] en el formulario de login
	/// dispare el proceso de login original
	document.querySelectorAll('#frmLogin input').forEach(
		function(input) {
			input.addEventListener('keydown', function(e) {
				if(e.keyCode==13) {
					document.querySelector('#btnLogin').click();
				}
			});
		}
	);
	/////////////////////////////////////////////////



});