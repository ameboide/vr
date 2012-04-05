<?php
require_once 'conf.php';
class DB
{
	var $dbh;
	function DB()
	{
		$conf = new CONF();

		$this->dbh = @mysql_connect($conf->host, $conf->user, $conf->pass) or die(mysql_error());
		mysql_select_db($conf->db) or mysql_error($this->dbh);
		if($conf->enc){
			if (!function_exists('mysql_set_charset')) mysql_query("set names '{$conf->enc}'", $this->dbh);
			else mysql_set_charset($conf->enc, $this->dbh);
		}
	}

	function _query($query){
		$resp = mysql_query($query, $this->dbh);
		if(!$resp) throw new Exception(mysql_error()."\n\nquery: ".$query);
		return $resp;
	}

	function _conds($campos, $unir = 'AND'){
		if(empty($campos)) return '1>0';
		if(!is_array($campos)) return $campos;
		$conds = array();
		foreach($campos as $campo => $valor){
			if($campo=='AND' || $campo=='OR') $conds[] = $this->_conds($valor, $campo); //arbol de condiciones
			else if(is_array($valor)){ //campo in valores
				$c = '';
				$kn = array_search(null, $valor, true);
				if($kn !== false){
					$c = $campo.' IS NULL OR ';
					unset($valor[$kn]);
				}
				foreach($valor as $k=>$v) $valor[$k] = "'".addslashes($v)."'";
				$conds[] = $c.$campo.' IN ('.implode(', ', $valor).')';
			}
			else if(is_numeric($campo)) $conds[] = $valor; //condicion random
			else $conds[] = $campo.($valor===null ? " IS NULL" : " = '".addslashes($valor)."'");
		}
		return '('.implode(' '.$unir.' ', $conds).')';
	}

	function select($cols, $tabla, $conds='', $extra=''){
		$q = $this->_query("SELECT $cols FROM $tabla WHERE ".$this->_conds($conds).' '.$extra);
		$res = array();
		while(true){
			$fila = mysql_fetch_array($q, MYSQL_ASSOC);
			if(!$fila) break;
			foreach($fila as &$c) if(is_numeric($c)) $c*=1;
			$res[] = $fila;
		}
		return $res;
	}

	function campo($col, $tabla, $conds_campos, $extra=''){
		$resp = $this->select($col, $tabla, $conds_campos, $extra.' LIMIT 0,1');
		if(empty($resp)) return false;
		return $resp[0][$col];
	}

	function insert($tabla, $campos){
		$datos = array();
		foreach($campos as $campo => $valor)
			$datos[] = $campo." = ".($valor===null ? "NULL" : "'".addslashes($valor)."'");

		$resp = $this->_query("INSERT INTO $tabla SET ".implode(', ', $datos));
		return mysql_insert_id($this->dbh);
	}

	function update($tabla, $campos, $conds){
		$datos = array();
		foreach($campos as $campo => $valor)
			$datos[] = $campo." = ".($valor===null ? "NULL" : "'".addslashes($valor)."'");

		return $this->_query("UPDATE $tabla SET ".implode(', ', $datos)." WHERE ".$this->_conds($conds));
	}

	function hash($pass){
		return sha1('23aeroiyna4omw38jrlbnltif23' . $pass);
	}
}
?>