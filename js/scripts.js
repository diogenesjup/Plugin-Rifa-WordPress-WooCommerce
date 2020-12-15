/**
*  ------------------------------------------------------------------------------------------------
*
*
*   SELECIONAR COTA DA RIFA
*
*
*  ------------------------------------------------------------------------------------------------
*/
function selecionarCotaRifa(numeroCota){
    
    // MOSTRAR A JANELA DE CARREGANDO
	document.getElementById("colunaDois").innerHTML = `<img src="https://trevopremiado.com/wp-content/plugins/plugin-rifa/images/loading.gif" style="width:32px;height:auto;" />`;
   
    console.log("ESSA É A COTA SELECIONADA: "+numeroCota);

    document.getElementById("modalRifa").style.bottom = "0px";

      var html = "";
      var dadosCheckout = "";

      var checkboxes = document.getElementsByName("cotas");
	  var checkboxesChecked = [];
	  totalCheched = 0;
	  // loop over them all
	  for (var i=0; i<checkboxes.length; i++) {
	     // And stick the checked ones onto an array...
	     if (checkboxes[i].checked) {
	     	totalCheched++;
	        checkboxesChecked.push(checkboxes[i]);
	        html = html + `<span id="cotaSpan${checkboxes[i].value}" onclick="removerSelecaoCota(${checkboxes[i].value})">${checkboxes[i].value}</span>`;
            dadosCheckout = dadosCheckout+checkboxes[i].value+",";
	     }
	  }

	  //console.log(html);
	  document.getElementById("colunaUm").innerHTML = html;

	  localStorage.setItem("dadosCheckout",dadosCheckout);

	  var idDoProduto = document.getElementById("idDoProdutoInput").value;
      
      // ADICIONAR AO CARRINHO APÓS O PROCESSAMENTO
	  salvarCarrinho(idDoProduto,totalCheched);

}

/**
*  ------------------------------------------------------------------------------------------------
*
*
*   REMOVER COTA DA RIFA
*
*
*  ------------------------------------------------------------------------------------------------
*/
function removerSelecaoCota(cotaValue){

	console.log("REMOVENDO COTA DO USUARIO: "+cotaValue);

	var cota = document.getElementById("cotaSpan"+cotaValue);
    cota.remove();

    document.getElementById("cota"+cotaValue).checked = false;

}



/**
*  ------------------------------------------------------------------------------------------------
*
*
*   AJAX SALVAR CARRINHO
*
*
*  ------------------------------------------------------------------------------------------------
*/
function salvarCarrinho(idProduto,quantidade){

	console.log("ID DO PRODUTO: "+idProduto);
	console.log("QUANTIDADE: "+quantidade);
    
    var ajaxurl = "https://trevopremiado.com/wp-admin/admin-ajax.php";
  
                      let xhr = new XMLHttpRequest();
                       
                      xhr.open('POST', ajaxurl,true);
                      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                      var params = 'action=salvar_carrinho&id='+idProduto+"&qtd="+quantidade;
                      
                      // INICIO AJAX VANILLA
                      xhr.onreadystatechange = () => {

                        if(xhr.readyState == 4) {

                          if(xhr.status == 200) {

                            console.log(xhr.responseText);  
                            console.log(JSON.parse(xhr.responseText));      

                            document.getElementById("colunaDois").innerHTML = `

                                 <h3>
                                     Total das ${quantidade} cotas: <b>R$ ${xhr.responseText}</b> <a href="https://trevopremiado.com/finalizar-compra" title="Finalizar compra">Finalizar compra</a>
                                 </h3>

                            `;
                           
                          }else{
                            
                            console.log("SEM SUCESSO CALL AJAX ADD TO CART()");
                            console.log(xhr.responseText);

                          }

                        }
                    }; // FINAL AJAX VANILLA

                    /* EXECUTA */
                    xhr.send(params);
}



   setTimeout(function(){ 
      
      document.getElementById("billing_cotasescolhidas").value = localStorage.getItem("dadosCheckout");

   }, 3000);	

   






