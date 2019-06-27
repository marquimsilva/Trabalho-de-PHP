
window.onload = function() {
	
	document.getElementById("foto").onchange = function () {
		var reader = new FileReader();

		reader.onload = function (e) {
			document.getElementById("image").src = e.target.result; 
		};
		
		reader.readAsDataURL(this.files[0]);
	};

	
	document.getElementById("btnLimpar").onclick = function () {
		restauraForm();
	};
}

function restauraForm() {
	
	document.getElementById('image').src 		= '';
	
	document.getElementById('id').value  		= "-1";
	document.getElementById('nomeFoto').value  	= "";
	
	document.getElementById('btnLimpar').value 	= "Limpar";
	document.getElementById('btnSalvar').value 	= "Salvar";
}

function inputOn( obj ) {
	obj.style.backgroundColor = "#ffffff";
}


function inputOff( obj ) {
	obj.style.backgroundColor = "#7e83a2";
}


function carregarLista() {
	var xhttp;
	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
      		document.getElementById('lista').innerHTML = this.responseText;
    	} else {
    		document.getElementById('lista').innerHTML = "Erro na execucao do Ajax";
    	}
  	};
  	xhttp.open("POST", "crud.php", true);
  	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  	xhttp.send("action=lista");
}


function carregarCliente( obj ) {
	var xhttp;
	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
      		var resultado = JSON.parse( this.responseText );
      	
      		document.getElementById('id').value 		= resultado[0].id;
      		document.getElementById('nome').value 		= resultado[0].nome;
      		document.getElementById('email').value 		= resultado[0].email;
      		document.getElementById('telefone').value 	= resultado[0].telefone;
      		document.getElementById('image').src 		= "/crud/imagens/"+resultado[0].foto;
      		document.getElementById('nomeFoto').value 	= resultado[0].foto;
      		
      		document.getElementById('nome').focus();
      		
      		document.getElementById('btnLimpar').value 	 = "Voltar";
      		
      		document.getElementById('btnSalvar').value 	 = "Atualizar";
    	} else {
    		document.getElementById('msg-php').innerHTML = "Erro na execucao do Ajax";
    	}
  	};
  	xhttp.open("POST", "crud.php", true);
  	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  	xhttp.send("action=buscar&id="+obj);
}


function excluirRegistro( obj ) {
	if ( confirm("Clique em OK para confirmar a operação.") ) {
		var xhttp;
		xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
	    	if (this.readyState == 4 && this.status == 200) {
	      		
	      		document.getElementById('msg-php').innerHTML = this.responseText;
	      		document.getElementById('msg-php').classList.remove("no-display");
	      		document.getElementById('msg-php').classList.add("msg-php");
	      		hideMsg();
	  			
	  			carregarLista();
	    	} else {
	    		document.getElementById('msg-php').innerHTML = "Erro na execucao do Ajax";
	    	}
	  	};
	  	xhttp.open("POST", "crud.php", true);
	  	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  	xhttp.send("action=excluir&id="+obj);
  	}
}


function salvarForm() {
	var xhttp;
	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
    		
    		restauraForm();
    		document.getElementById('frmCrud').reset();
    		
      		document.getElementById('msg-php').innerHTML = this.responseText;
      		document.getElementById('msg-php').classList.remove("no-display");
      		document.getElementById('msg-php').classList.add("msg-php");
      		hideMsg();
  			
  			carregarLista();
    	} else {
    		document.getElementById('msg-php').innerHTML = "Erro na execucao do Ajax";
    	}
  	};
  	
  	var formData = new FormData();
  	formData.append("id", document.getElementById("id").value);
  	formData.append("nome", document.getElementById("nome").value);
  	formData.append("email", document.getElementById("email").value);
  	formData.append("telefone", document.getElementById("telefone").value);
  	formData.append("foto", document.getElementById("foto").files[0]);
  	formData.append("nomeFoto", document.getElementById("nomeFoto").value);

  	xhttp.open("POST", "crud.php?action=salvar", true);
  	xhttp.send( formData );
}


function hideMsg() {
	setTimeout(function() {
      	document.getElementById('msg-php').classList.add("no-display"); 
    }, 5000);
}
