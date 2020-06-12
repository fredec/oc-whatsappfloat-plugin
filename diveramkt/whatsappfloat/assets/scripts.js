	// $(document).ready(function(){


		// .clique_modal_float
		// modal_visivel

		// $(document).ready(function(){
		// 	$('.botoes_floats .clique_modal_float').click(function(e){
		// 		e.preventDefault();
		// 		alert('teste');
		// 	})
		// })

		function limpar_campos(){
			$('.botoes_floats .input_label input').val('');
		}

		var interval_whatsapp_floats='';
		interval_whatsapp_floats=setInterval(function(){
			if(document.querySelectorAll('.botoes_floats').length){
				clearInterval(interval_whatsapp_floats);
				ativar_scripts();
			}
		}, 1);

		function ativar_scripts(){

			if(document.querySelectorAll('input[name="titulo_da_pagina"]').length > 0){
				for (input of document.querySelectorAll('input[name="titulo_da_pagina"]')) {
					input.value=document.title;
				}
			}
			if(document.querySelectorAll('input[name="url_da_pagina"]').length > 0){
				for (input of document.querySelectorAll('input[name="url_da_pagina"]')) {
					input.value=document.location.href;	
				}	
			}
			

			var Vcenter_float=document.querySelectorAll('.botoes_floats .link_botao_whatsappfloat .Vcenter');
			var Vcenter_height_float=0;
			function altura_botoes_mobile(){
				if(Vcenter_float.length && window.innerWidth <= 600){
					for (cur of Vcenter_float) {
						if(cur.offsetHeight > Vcenter_height_float) Vcenter_height_float=cur.offsetHeight;
					}
					for (cur of Vcenter_float) { cur.style.height=Vcenter_height_float+'px'; }
				}else{
					for (cur of Vcenter_float) { cur.style.height='auto'; }
				}
		}
		if(Vcenter_float.length){
			altura_botoes_mobile();
		}


		for (bots of document.querySelectorAll('.botoes_floats .clique_modal_float')) {
			bots.onclick = function(e){
				e.preventDefault();

				if(document.querySelector(this.getAttribute('data-form')+'.modal_visivel') == null){
					document.querySelector(this.getAttribute('data-form')).classList.add("modal_visivel");
				}else{
					document.querySelector(this.getAttribute('data-form')).classList.remove("modal_visivel");
				}

				// if(bots.querySelector('.modal_form_float.modal_visivel') == null){
				// 	this.closest('.link_botao_whatsappfloat').querySelector('.modal_form_float').classList.add("modal_visivel");
				// 	// bots.querySelector('.modal_form_float').classList.add("modal_visivel");
				// }else{
				// 	this.closest('.link_botao_whatsappfloat').querySelector('.modal_form_float').classList.remove("modal_visivel");
				// 	// bots.querySelector('.modal_form_float').classList.remove("modal_visivel");
				// }
			};
		}

		for (bots of document.querySelectorAll('.botoes_floats .modal_form_float .fa-close')) {
			bots.onclick = function(e){
				e.preventDefault();
				this.closest('.modal_form_float').classList.remove('modal_visivel');
			}
		}
		for (bots of document.querySelectorAll('.botoes_floats .modal_form_float .fundo')) {
			bots.onclick = function(e){
				e.preventDefault();
				this.closest('.modal_form_float').classList.remove('modal_visivel');
			}
		}

		window.onload = function(){

			function botoes_float_abaixo(){
				if(window.innerWidth <= 600) document.querySelector('html').style.paddingBottom=(document.querySelector('.botoes_floats').offsetHeight)+'px';
				else document.querySelector('html').style.paddingBottom=0;
			}
			botoes_float_abaixo();

			if(document.querySelector('.box_mensagem') != null){
				var d = new Date();
				var balao_mensagem=d.getFullYear()+'-'+d.getMonth()+'-'+d.getDate();

				var balao_mensagemveri=JSON.parse(localStorage.getItem('balao_mensagem_whatsapp')) || [];
				if(balao_mensagemveri != balao_mensagem) mostrar_balao();

				document.querySelector('.box_mensagem .link_fechar').onclick = function(e){
					e.preventDefault();
					this.closest('div').classList.remove('mostrar_alvo');

					localStorage.setItem('balao_mensagem_whatsapp',JSON.stringify(balao_mensagem));
				}

				function mostrar_balao(){
					setTimeout(function(){
						document.querySelector('.box_mensagem').classList.add('mostrar_alvo');
					}, 1000);
				}
			}

			function hasClass(elem, className) {
				return new RegExp(' ' + className + ' ').test(' ' + elem.className + ' ');
			}

			var interval_botoes_mobile='';
			function botoes_mobile(){
				if(document.querySelectorAll('.botoes_floats .xslateral') != null){
					clearInterval(interval_botoes_mobile);
					interval_botoes_mobile=setTimeout(function(){
						if(bots_lateral == undefined) var bots_lateral=0;
						if(height_div == undefined) var height_div=0;

						height_div=0;

						for (cur of document.querySelectorAll('.botoes_floats .botoes_ > div.xs12')) {
							height_div+=cur.offsetHeight;
						}

						pos=0;
						for (cur of document.querySelectorAll('.botoes_floats .botoes_ > div.xs6')) {
							if(pos%2 == 0) height_div+=cur.offsetHeight;
							pos++;
						}

						bots_lateral=document.querySelectorAll('.botoes_floats .xslateral');
				// height_div=document.querySelector('.botoes_floats').clientHeight;
				// document.querySelector('.botoes_floats').style.border='solid blue 1px';

				if(bots_lateral != null){
						// alert(bots_lateral.length);
						if(cur == undefined) var cur='';
						for (var i = bots_lateral.length-1; i >= 0; i--) {
						// for (key in bots_lateral) {
							cur=bots_lateral[i];

							if(window.innerWidth <= 600 && !hasClass(cur, 'lateral_active')){
								cur.classList.add('lateral_active');
								cur.style.bottom=height_div+'px';
								height_div+=cur.offsetHeight;
							}else if(window.innerWidth > 600 && hasClass(cur, 'lateral_active')){
								cur.classList.remove('lateral_active');
								cur.style.bottom='0';
							}

						// if(hasClass(cur, 'lateral_active')){
							// cur.style.bottom=height_div+'px';
							// alert(height_div);
						// }
					}
				}
			}, 100);
				}
			}
			botoes_mobile();

			window.onresize = function (oEvent) {
				botoes_float_abaixo();
				botoes_mobile();
				altura_botoes_mobile();
			}

		}

	}
	// })