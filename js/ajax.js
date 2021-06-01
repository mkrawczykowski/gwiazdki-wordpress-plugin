jQuery(document).ready(function($) {
	
 
	if ($('.gwiazdki-ocena').length){
	var postIDattr = $('main#main.site-main article').attr('id');
	var postIDcleared = postIDattr.replace("post-", "");	
		function wczytajGwiazdki(gwiazdkowaOcena){
			console.log('wczytajGwiazdki: start');
				$.post(
					PT_Ajax.ajaxurl,
					{
						// wp ajax action
				    	action: 'ocenaGwiazdkowaAjax',
						gwiazdkowaOcena: gwiazdkowaOcena,
						IDPosta: postIDcleared,

						// send the nonce along with the request
						nextNonce: PT_Ajax.nextNonce
					},
						function (response) {
							odp = JSON.parse( response );
							
							var odpSrednia = parseFloat(odp.sumaOcen/odp.iloscOcen).toFixed(2).replace(/([0-9]+(\.[0-9]+[1-9])?)(\.?0+$)/,'$1');
							console.log('odp.type: ' + odp.type);
							
							if (odp.type == 'ok'){
								$('.gwiazdki-ocena p.gwiazdki-oceny span').innerHTML = odpSrednia;
								$('.gwiazdki-ocena p.gwiazdki-oceny span').html(odpSrednia + '/5');
								$('.gwiazdki-ocena').toggleClass('gwiazdki-aktywne');
								$('.gwiazdki-ocena').toggleClass('gwiazdki-nieaktywne');
								
								var ocena = odpSrednia.split('.');
								var przedKropka = ocena[0];
								var poKropce = ocena[1];
								console.log(przedKropka);
								console.log(poKropce);								
								$('.gwiazdki-ocena').html(odp.rysujGwiazdki);
								$('.gwiazdki-ocena .glos-oddany').html('Dzięki za oddanie głosu!');
								$('.gwiazdki-ocena .glos-oddany').removeClass('niewidoczny');
							}
							
							if (odp.type == 'nok'){
								$('.gwiazdki-ocena .glos-oddany').html('Już cześniej zarejestrowaliśmy twój głos.');
								$('.gwiazdki-ocena .glos-oddany').removeClass('niewidoczny');
							}
						} 
				);	
		}
		
		//wczytajGwiazdki();
		
		$(document).on('click', '.gwiazdki-aktywne ul.gwiazdki-lista li', function () {
			var klikKlasyOcena = $(this).attr('class');
			var klikOcena = klikKlasyOcena.replace('gwiazdka-', '');	
			console.log('klikOcena: ' + klikOcena);
			wczytajGwiazdki(klikOcena);
			return false;
		});
		
		$(document).on('click', '.gwiazdki-nieaktywne ul.gwiazdki-lista li', function () {
			$('.gwiazdki-ocena .glos-oddany').html('Już cześniej zarejestrowaliśmy twój głos.');
			$('.gwiazdki-ocena .glos-oddany').removeClass('niewidoczny');
		});

	}
		
		
});