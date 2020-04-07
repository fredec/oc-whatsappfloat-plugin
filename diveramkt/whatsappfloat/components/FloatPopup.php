<?php namespace Diveramkt\FloatingBanner\Components;

use Cache;
use Cms\Classes\ComponentBase;
use session;

use Diveramkt\FloatingBanner\Models\Popup;

class FloatPopup extends ComponentBase
{

	public function componentDetails(){
		return [
			'name' => 'Floating Popup',
			'description' => 'A popup to open over the page content'
		];
	}

	public function defineProperties(){
		return [

		];
	}

	    // VERIFICAR DIFERENCA DE TEMPO HORA/MINUTOS/SEGUNDO EM DOIS TEMPOS DIFERENTES (DATA HORARIO)
	public function horas_datas($data1, $data2){
    // $data1='2017-01-25 00:22:15';
    // $data2='2017-04-27 01:00:00';

		if(function_exists('date_diff')){
			$datetime1 = date_create($data1);
			$datetime2 = date_create($data2);
			$diff = date_diff($datetime1, $datetime2);
		}else{
			$date1=explode(' ', $data1); $date1[0]=explode('-', $date1[0]); $date1[1]=explode(':', $date1[1]);
			$date2=explode(' ', $data2); $date2[0]=explode('-', $date2[0]); $date2[1]=explode(':', $date2[1]);
			$diff=new stdclass;
			$diff->y=0;$diff->m=0;$diff->d=0;$diff->h=0;$diff->i=0;$diff->s=0;

			if($date2[0][0] >= $date1[0][0]) $diff->y=$date2[0][0]-$date1[0][0];
			if($date2[0][1] >= $date1[0][1]) $diff->m=$date2[0][1]-$date1[0][1];
			if($date2[0][2] >= $date1[0][2]) $diff->d=$date2[0][2]-$date1[0][2];
			if($date2[1][0] >= $date1[1][0]) $diff->h=$date2[1][0]-$date1[1][0];
			if($date2[1][1] >= $date1[1][1]) $diff->i=$date2[1][1]-$date1[1][1];
			if($date2[1][2] >= $date1[1][2]) $diff->s=$date2[1][2]-$date1[1][2];

// CALCULANDO SEGUNDOS TOTAIS
			$s1=$date1[1][2]+($date1[1][1]*60)+($date1[1][0]*3600);
			$s2=$date2[1][2]+($date2[1][1]*60)+($date2[1][0]*3600);
			$s=$s2-$s1;

    // CALCULANDO HORAS
			$diff->h=$s/3600; $diff->h=explode('.', $diff->h); $diff->h=$diff->h[0]; $s-=(3600*$diff->h);
    // CALCULANDO HORAS

    // CALCULANDO MINUTOS
			$diff->i=$s/60; $diff->i=explode('.', $diff->i); $diff->i=$diff->i[0]; $s-=(60*$diff->i);
    // CALCULANDO MINUTOS

			$diff->s=$s;

			$diff->invert=0;

			$data1=explode(' ', $data1);
			$data1=$data1[0];
			$data2=explode(' ', $data2);
			$data2=$data2[0];

			$diff->days=cal_days_interval($data1, $data2);
		}


		$retorno=new stdclass;
		$retorno->horas = $diff->h + ($diff->days * 24);
		$retorno->minutos=$diff->i;
		$retorno->segundos=$diff->s;

		$retorno->minutos_totais=$retorno->minutos;
		if($retorno->horas > 0) $retorno->minutos_totais+=($retorno->horas*60);
		return $retorno;
	}
    // VERIFICAR DIFERENCA DE TEMPO HORA/MINUTOS/SEGUNDO EM DOIS TEMPOS DIFERENTES (DATA HORARIO)

	public function onRun(){

		$this->popup = $this->getPopup();
		if(isset($this->popup[0])) $this->popup=$this->popup[0];
		else return;

		// $name='banner_float_'.serialize($this->popup->attributes); // $name=preg_replace("/[^a-zA-Z0-9]/", "", $name);
		if(!isset($this->popup->attributes) && !isset($this->popup->attributes['image'])) return;
		$name='banner_popup_diveramkt'.str_replace(array('/','.'), array('-',''), $this->popup->attributes['image']);
		if(isset($this->popup->attributes) && isset($this->popup->attributes['dias_oculto']) && $this->popup->attributes['dias_oculto'] > 0){

			if(Session::get($name)) {

				$horas_dia=$this->popup->attributes['dias_oculto']*24;

				$value = Session::get($name);
				$veri=$this->horas_datas($value, date('Y-m-d H:i:s'));
				$horas=$veri->horas;
				if($horas >= $horas_dia) Session::put($name, date('Y-m-d H:i:s'));
				else $this->popup='';

			}else Session::put($name, date('Y-m-d H:i:s'));

			// $this->popup->attributes['dias_oculto']
			// $dias=$this->popup->attributes['dias_oculto']*(24*60);
			// if (Cache::has($name)) {
			// 	$veri=Cache::get($name);
			// 	$this->popup='';
			// }else{
			// 	Cache::pull($name);
			// 	Cache::add($name, 'carregado', $dias);
			// }

		// }elseif(Cache::has($name)) Cache::forget($name);
		// }else Cache::forget($name);
		}else Session::forget($name);


		if(isset($this->popup->attributes) && str_replace(' ','',$this->popup->attributes['link']) != ''){
			$this->popup->attributes['target']='_parent';
			$url=$this->popup->attributes['link'];
			if(!strpos("[".$url."]", "http://") && !strpos("[".$url."]", "https://")) $url='http://'.$url;
			$this->popup->attributes['link']=$url;

			if(!strpos("[".$this->popup->attributes['link']."]", $_SERVER['HTTP_HOST'])) $this->popup->attributes['target']='_blank';
		}
		
	}

	protected function getPopup(){
		// return Popup::first();

		$retorno=Popup::where('data_entrada','<=',date('Y-m-d H:i:s'))->whereNull('data_saida')
		->orWhere('data_entrada','<=',date('Y-m-d H:i:s'))->where('data_saida','0000-00-00')
		->orWhere('data_entrada','<=',date('Y-m-d H:i:s'))->where('data_saida','>',date('Y-m-d H:i:s'))
		->get();

		if(isset($retorno[0])){
			$veri_link=explode($_SERVER['HTTP_HOST'], $retorno[0]->link); $veri_link=end($veri_link);
			if($_SERVER['REDIRECT_URL'] == $veri_link) $retorno=false;
		}else $retorno=false;

		return $retorno;
	}

	public $popup;
}