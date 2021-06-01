<?php
/*
Plugin Name: Gwiazdki
Plugin URI: https://stronyireszta.pl
Description: Gwiazdki do oceniania wpisów
Author: Stronyireszta.pl
Version: 1.0
*/

/*
function ajax_enqueue_script() {
	wp_enqueue_script( 'zapiszInputyAjax', get_stylesheet_directory_uri() . '/js/skrypty-ajax.js' );
	wp_localize_script( 'zapiszInputyAjax', 'PT_Ajax', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
		)
	);
}

add_action( 'wp_enqueue_scripts', 'ajax_enqueue_script' );
*/


add_action( 'wp_ajax_ocenaGwiazdkowaAjax', 'ocenaGwiazdkowaAjaxFunkcja' );
add_action( 'wp_ajax_nopriv_ocenaGwiazdkowaAjax', 'ocenaGwiazdkowaAjaxFunkcja' );


//Dodaj wtyczkową obsługę AJAX
function ajax_enqueue_script() {
    wp_enqueue_script( 'ocenaGwiazdkowaAjax',  plugin_dir_url( __FILE__ ) . 'js/ajax.js' );
	wp_localize_script( 'ocenaGwiazdkowaAjax', 'PT_Ajax', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ajax_enqueue_script' );




function ocenaGwiazdkowaAjaxFunkcja() {
	// check nonce 
	$nonce = $_POST['nextNonce'];
	$gwiazdkowaOcena = $_POST['gwiazdkowaOcena'];
	$IDPosta = $_POST['IDPosta'];
	
	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}
	
	if ($gwiazdkowaOcena){
		if (!is_numeric($gwiazdkowaOcena)){
			$result = json_encode(
            array(
                'type' => 'nok',
                'text' => 'ło panie, kto panu to tak sp&^%$#$%ł?'
		));
		die($result);
		}	
	}
	
	
	if (!$gwiazdkowaOcena){ 
	//gdy ajaxowe zapytanie przychodzi bez oceny użytkownika, czyli zaraz po wczytaniu się strony	
	
		$iloscOcenJest = get_field('ilosc_ocen_gwiazdkowych', $IDPosta);
		$sumaOcenJest = get_field('suma_ocen_gwiazdkowych', $IDPosta);
		
		$rysujGwiazdkiOut;

		$iloscOcenWyswietl = get_field('ilosc_ocen_gwiazdkowych', $IDPosta);
		$sumaOcenWyswietl = get_field('suma_ocen_gwiazdkowych', $IDPosta);
		
		if (!$iloscOcenWyswietl){
			$iloscOcenWyswietl = 0;
		}
		
		if (!$sumaOcenWyswietl){
			$sumaOcenWyswietl = 0;
		}
		
		if ($iloscOcenWyswietl != 0 && $sumaOcenWyswietl != 0){
			$sredniaOcenaWyswietl = round($sumaOcenWyswietl/$iloscOcenWyswietl,2);
			$pos = strpos($sredniaOcenaWyswietl, '.');
		} else {
			$sredniaOcenaWyswietl = 0;
			$pos = '';
		}

		if ($pos) {
			$podzielonaOcena = explode(".", $sredniaOcenaWyswietl, 2);
			$przedKropka = $podzielonaOcena[0];
			$poKropce = $podzielonaOcena[1];
		} else {
			$przedKropka = $sredniaOcenaWyswietl;
			$poKropce = 0;
		}

		$ulGwiazdki = '<ul class="gwiazdki-lista">'; 
				
				for ($i=1; $i<6; $i++){
					if ($przedKropka >= $i){
						$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pelna"></a></li>';
					} elseif ($przedKropka+1 == $i){
						if ($poKropce <= 50 && $poKropce > 0){
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pol"></a></li>';
						} elseif ($poKropce == 0){
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pusta"></a></li>';
						} else {
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pelna"></a></li>';
						}			
					} else {
						$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pusta"></a></li>';
					}
					
				}
		$ulGwiazdki .= '</ul>';
		
		$ulGwiazdki .= '<p class="gwiazdki-oceny"><span>';

		if ($sredniaOcenaWyswietl != 0){
			$ulGwiazdki .=  $sredniaOcenaWyswietl . '/5'; 
		} else {
			$ulGwiazdki .=  'na razie brak ocen';
		}

		$ulGwiazdki .= '</span></p>';

		$ulGwiazdki .= '<p class="glos-oddany niewidoczny">Już cześniej zarejestrowaliśmy twój głos.</p>';
		
		$rysujGwiazdkiOut['ulGwiazdki'] = $ulGwiazdki;
		$rysujGwiazdkiOut['sredniaOcenaWyswietl'] = $sredniaOcenaWyswietl;
		
		
		
		
		
		$result = json_encode(
            array(
                'type' => 'ok',
                'sumaOcen' => $sumaOcenJest,
				'iloscOcen' => $iloscOcenJest,
				'IDPosta' => $IDPosta,
				'rysujGwiazdki' => $rysujGwiazdkiOut['ulGwiazdki']
		));
		
		die($result);
		
	} else {
	//gdy ajaxowe zapytanie przychodzi po ocenieniu artykułu przez użytkownika
		
		$arrayPosty = array();
		
		if (isset($_COOKIE['stronyireszta_ocena'])) {
			
			$ciastkoPosty = $_COOKIE['stronyireszta_ocena'];
			$arrayPosty = explode(',', $ciastkoPosty);
			
			$czyJestPost = false;
			
			foreach ($arrayPosty as $arrayIDPosta) {
				if ($arrayIDPosta == $IDPosta) $czyJestPost = true;
			}
			
			if ($czyJestPost == true) {
				$result = json_encode(
					array(
						'type' => 'nok',
						'text' => 'cookie'
				));
				
				die($result);			
			}
			
		}		
		
		if (is_numeric($gwiazdkowaOcena)) {
			array_push($arrayPosty, $IDPosta);
			$postyDoCiastka = implode(',', $arrayPosty);
			setcookie( "stronyireszta_ocena", $postyDoCiastka, time()+31556926, '/' ); 
			$iloscOcenJest = get_field('ilosc_ocen_gwiazdkowych', $IDPosta);
			$sumaOcenJest = get_field('suma_ocen_gwiazdkowych', $IDPosta);
		
			$iloscOcenJest++;
			$sumaOcenJest += $gwiazdkowaOcena;
		
			update_field('ilosc_ocen_gwiazdkowych', $iloscOcenJest, $IDPosta);
			update_field('suma_ocen_gwiazdkowych', $sumaOcenJest, $IDPosta);
			
			
			
			$result = json_encode(
				array(
					'type' => 'ok',
					'sumaOcen' => $sumaOcenJest,
					'iloscOcen' => $iloscOcenJest,
					'path' => $pathCiastko, 
					'IDPosta' => $IDPosta,
					'ciastkoPosty' => $ciastkoPosty,
					'arrayPosty' => $arrayPosty,
					'postyDoCiastka' => $postyDoCiastka
			));
	
		}
		
		die($result);

	}
	
	// TU RYSUJ GWIAZDKI ()


}





add_action( 'generate_after_content', 'testgty', 110 );

function testgty(){
		$IDPosta = get_the_ID();
		$czyWlaczoneOceny = get_field('czy_gwiazdki', $rty);
		//echo $czyWlaczoneOceny;
		if ($czyWlaczoneOceny){
	
	
		if (isset($_COOKIE['stronyireszta_ocena'])) {
			$gwiazdkiActivePassive = 'gwiazdki-nieaktywne';
			
			$ciastkoPosty = $_COOKIE['stronyireszta_ocena'];
			$arrayPosty = explode(',', $ciastkoPosty);
			
			$czyJestPost = false;
			
			foreach ($arrayPosty as $arrayIDPosta) {
				if ($arrayIDPosta == $IDPosta) $czyJestPost = true;
			}
			
			if ($czyJestPost == true) {
				$gwiazdkiActivePassive = 'gwiazdki-nieaktywne';			
			} else {
				$gwiazdkiActivePassive = 'gwiazdki-aktywne';
			}
			
		} else {
			$gwiazdkiActivePassive = 'gwiazdki-aktywne';
		}

		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		

	/*
		var_dump($_COOKIE['stronyireszta_ocena']);
	*/

	//$gwiazdkiActivePassive = 'gwiazdki-aktywne';

	
	

	?>
	<div class="gwiazdki-ocena <?php echo $gwiazdkiActivePassive; ?>">

		<!-- tu wyciąłem gwiazdki, potem mznów wkleiłem z funkcji AJAXowej -->
		<?php
		$iloscOcenJest = get_field('ilosc_ocen_gwiazdkowych', $IDPosta);
		$sumaOcenJest = get_field('suma_ocen_gwiazdkowych', $IDPosta);
		
		//$rysujGwiazdkiOut;

		$iloscOcenWyswietl = get_field('ilosc_ocen_gwiazdkowych', $IDPosta);
		$sumaOcenWyswietl = get_field('suma_ocen_gwiazdkowych', $IDPosta);
		
		if (!$iloscOcenWyswietl){
			$iloscOcenWyswietl = 0;
		}
		
		if (!$sumaOcenWyswietl){
			$sumaOcenWyswietl = 0;
		}
		
		if ($iloscOcenWyswietl != 0 && $sumaOcenWyswietl != 0){
			$sredniaOcenaWyswietl = round($sumaOcenWyswietl/$iloscOcenWyswietl,2);
			$pos = strpos($sredniaOcenaWyswietl, '.');
		} else {
			$sredniaOcenaWyswietl = 0;
			$pos = '';
		}

		if ($pos) {
			$podzielonaOcena = explode(".", $sredniaOcenaWyswietl, 2);
			$przedKropka = $podzielonaOcena[0];
			$poKropce = $podzielonaOcena[1];
		} else {
			$przedKropka = $sredniaOcenaWyswietl;
			$poKropce = 0;
		}

		$ulGwiazdki = '<ul class="gwiazdki-lista">'; 
				
				for ($i=1; $i<6; $i++){
					if ($przedKropka >= $i){
						$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pelna"></a></li>';
					} elseif ($przedKropka+1 == $i){
						if ($poKropce <= 50 && $poKropce > 0){
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pol"></a></li>';
						} elseif ($poKropce == 0){
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pusta"></a></li>';
						} else {
							$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pelna"></a></li>';
						}			
					} else {
						$ulGwiazdki .= '<li class="gwiazdka-' . $i . '"><a class="gwiazdka gwiazdka-pusta"></a></li>';
					}
					
				}
		$ulGwiazdki .= '</ul>';
		
		$ulGwiazdki .= '<p class="gwiazdki-oceny"><span>';

		if ($sredniaOcenaWyswietl != 0){
			$ulGwiazdki .=  $sredniaOcenaWyswietl . '/5'; 
		} else {
			$ulGwiazdki .=  'na razie brak ocen';
		}

		$ulGwiazdki .= '</span></p>';

		$ulGwiazdki .= '<p class="glos-oddany niewidoczny"></p>';
		echo $ulGwiazdki;
		//$rysujGwiazdkiOut['ulGwiazdki'] = $ulGwiazdki;
		//$rysujGwiazdkiOut['sredniaOcenaWyswietl'] = $sredniaOcenaWyswietl;
		?>

	</div>
	
	<?php
	}
	
}



add_action( 'wp_head', 'structuredDataHeader', 110 );

function structuredDataHeader(){

		
		
		$czyWlaczoneOceny = get_field('czy_gwiazdki', $rty);
		
		if ($czyWlaczoneOceny){
			
			
			
			
			
			
			
					$iloscOcenJest = get_field('ilosc_ocen_gwiazdkowych', $rty);
		$sumaOcenJest = get_field('suma_ocen_gwiazdkowych', $rty);
		$sredniaOcenaWyswietl = round($sumaOcenJest/$iloscOcenJest,2);


		?>
		<script type="application/ld+json">{
    "@context": "https://schema.org/",
    "@type": "CreativeWorkSeries",
    "name": "<?php the_title(); ?>",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $sredniaOcenaWyswietl; ?>",
        "bestRating": "5",
        "ratingCount": "<?php echo $iloscOcenJest; ?>"
    }
}</script>
			
			
			
			
			
			
			
			
	<?php	}
		
		

}


