<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao;

/**
 * Utilitaires divers et variés...
 *
 */
class Utils
{
	/*
	 * Utilitaires sur les fichiers
	 *
	 */

	/**
	 * Indique si un répertoire contient des fichiers
	 *
	 * @param string $sDir
	 * @return boolean
	 */
	public static function dirHasFiles($sDir)
	{
		if (!is_dir($sDir)) {
			return false;
		}

		$bReturn = false;

		foreach (new \DirectoryIterator($sDir) as $oFileInfo)
		{
			if (!$oFileInfo->isDot())
			{
				$bReturn = true;
				break;
			}
		}

		return $bReturn;
	}

	/**
	 * Upload status
	 *
	 * Returns true if upload status is ok, throws an exception instead.
	 *
	 * @param array		$file		File array as found in $_FILES
	 * @throws Exception
	 * @return boolean
	 */
	public static function uploadStatus($aFile)
	{
		if (!isset($aFile['error'])) {
			throw new Exception(__('c_c_upload_error_1'));
		}

		switch ($aFile['error'])
		{
			default:
			case UPLOAD_ERR_OK:
				return true;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new Exception(__('c_c_upload_error_2'));
				return false;

			case UPLOAD_ERR_PARTIAL:
				throw new Exception(__('c_c_upload_error_3'));
				return false;

			case UPLOAD_ERR_NO_FILE:
				throw new Exception(__('c_c_upload_error_4'));
				return false;

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Exception(__('c_c_upload_error_5'));
				return false;

			case UPLOAD_ERR_CANT_WRITE:
				throw new Exception(__('c_c_upload_error_6'));
				return false;
		}
	}

	/**
	 * Retourne le nom du sous-répertoire d'un chemin situé après un répertoire donnée.
	 *
	 * Exemple :
	 *
	 * $str = 'dir1/dir2/dir3/dir4/filename.ext';
	 * $dir = 'dir1/dir2';
	 *
	 * echo util::getNextSubDir($str, $dir); // Outputs dir3
	 *
	 * @param string $sPath Le chemin complet
	 * @param string $sBasePath Le répertoire donné.
	 * @return string
	 */
	public static function getNextSubDir($sPath, $sBasePath)
	{
		if (is_file($sPath)) {
			$sPath = dirname($sPath);
		}

		$aPathComponents = array_filter(explode('/', str_replace('\\', '/', realpath($sPath))));
		$aBasePathComponents = array_filter(explode('/', str_replace('\\', '/', realpath($sBasePath))));

		foreach ($aPathComponents as $i=>$k)
		{
			if (!isset($aBasePathComponents[$i]) || $aBasePathComponents[$i] != $k) {
				return $k;
			}
		}
	}

	/**
	 * Permet de copier un répertoire de façon récursive.
	 *
	 * @param string $src
	 * @param string $dst
	 */
	public static function rcopy($src,$dst)
	{
		if (is_file($src)) {
			copy($src, $dst);
		}
		elseif (is_dir($src))
		{
			\files::makeDir($dst,true);

			$dir = opendir($src);

			while (false !== ($file = readdir($dir)))
			{
				if ($file == '.' || $file == '..' || $file == '.svn') {
					continue;
				}

				if (is_dir($src.'/'.$file) ) {
					self::rcopy($src.'/'.$file, $dst.'/'.$file);
				}
				else {
					copy($src.'/'.$file, $dst.'/'.$file);
				}
			}
			closedir($dir);
		}
	}

	/**
	 * Suppression récursive et agressive d'une liste
	 * de fichiers dans un répertoire donné.
	 *
	 * Les fichiers à supprimer peuvent êtres des répertoires.
	 *
	 * @param string $sDirectoryPath	Le répertoire à nettoyer
	 * @param array $aToDelete			Les fichiers à supprimer
	 * @return integer Le nombre de fichier supprimés.
	 */
	public static function recursiveCleanup($sDirectoryPath,$aToDelete)
	{
		static $iNumProcessed = null;

		if (is_null($iNumProcessed)) {
			$iNumProcessed = 0;
		}

		$oDirectory = dir($sDirectoryPath);

		while (($sFilename = $oDirectory->read()) !== false)
		{
			if ($sFilename == '.' || $sFilename == '..') {
				continue;
			}

			$sFile = $sDirectoryPath.'/'.$sFilename;

			if (is_dir($sFile))
			{
				if (in_array($sFilename,$aToDelete))
				{
					\files::deltree($sFile);
					$iNumProcessed++;
				}
				else {
					self::recursiveCleanup($sFile,$aToDelete);
				}
			}
			elseif (is_file($sFile))
			{
				if (in_array($sFilename,$aToDelete))
				{
					unlink($sFile);
					$iNumProcessed++;
				}
			}
		}

		$oDirectory->close();
		return $iNumProcessed;
	}

	/*
	 * Utilitaires sur les chiffres
	 *
	 */

	/**
	 * Vérifie si $val est un entier.
	 * Contrairement à la fonction PHP cette fonction va retourner vrai pour '42'
	 * (autrement dit : elle ne tient pas compte du type)
	 *
	 * @param $val
	 * @return boolean
	 */
	public static function isInt($val)
	{
		return ($val !== true) && ((string)(int) $val) === ((string) $val);
	}

	/**
	 * Transforme un nombre formaté en un nombre manipulable par le système.
	 *
	 * @TODO: à revoir, moche...
	 * @param	string	number		Le nombre à formater
	 * @return string
	 */
	public static function sysNumber($number,$allow_negative=false)
	{
		$number = str_replace(__('c_c_number_thousands_separator'), '', $number);
		$number = str_replace(__('c_c_number_decimals_separator'), '.', $number);

		if (!is_numeric($number)) {
			return null;
		}

		if (!$allow_negative && $number < 0) {
			$number = -$number;
		}

		return $number;
	}

	/**
	 * Formatage d'un nombre selon les préférences locales
	 *
	 * Par exemple :
	 * 1 2058,38 en français
	 * 1,2058.38 en anglais
	 *
	 * @param	float	number		Le nombre à formater
	 * @param	integer	dec			Le nombre de décimaux à afficher
	 * @return	string
	 */
	public static function formatNumber($number, $dec=2)
	{
		return \html::escapeHTML(number_format((float)$number, $dec, __('c_c_number_decimals_separator'), __('c_c_number_thousands_separator')));
	}

	/**
	 * Fonction de remplacement à la fonction PHP native number_format()
	 *
	 * @param float $number
	 * @param integer $decimals
	 * @param string $decimal_point
	 * @param string $thousand_separator
	 * @return string
	 */
	/*
	public static function numberFormat($number, $decimals=0, $decimal_point = '.', $thousand_separator='')
	{
		$tmp1 = round((float) $number, $decimals);

		while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1) {
			$tmp1 = $tmp2;
		}

		return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
	}
	*/

	/**
	 * Formatage d'un prix selon les préférences locales et le taux de conversion
	 *
	 * @param	float	price		Le prix à formater
	 * @param	float	taux		Le taux de conversion
	 * @param	integer	dec			Le nombre de décimaux
	 * @return	boolean
	 */
	public static function formatPrice($price, $taux=0, $dec=2)
	{
		if ($taux>0) {
			return self::formatNumber(self::ht2ttc($price,$taux), $dec);
		} else {
			return self::formatNumber($price, $dec);
		}
	}

	/**
	 * Calcule le prix TTC d'un prix HT selon un taux donné.
	 * Pric calculé selon la formule suivante :
	 *
	 * TTC = HT + (HT x TAUX)/100
	 *
	 * @param 	float ht		Le prix HT
	 * @param	float taux		Le taux de conversion
	 * @return float le prix TTC
	 */
	public static function ht2ttc($ht, $taux)
	{
		if ($taux == 0) {
			return $ht;
		}

		return ($ht+($ht*$taux)/100);
	}

	/**
	 * Calcule le prix TTC d'un prix HT selon un taux donné.
	 * Pric calculé selon la formule suivante :
	 *
	 * HT = TTC / (1 + TAUX/100)
	 *
	 * @param 	float ttc		Le prix TTC
	 * @param	float taux		Le taux de conversion
	 * @return float le prix HT
	 */
	public static function ttc2ht($ttc, $taux)
	{
		return ($ttc/(1+$taux/100));
	}

	/**
	 * Retourne le montant des mensualités d'un crédit à TAEG.
	 *
	 * @param float $k 		Capital/prix
	 * @param float $ti 	Taux d'interet
	 * @param float $ta 	Taux assurance
	 * @param integer $n 	Nombre de menusalités
	 * @return float
	 */
	public static function getMonthlyPaymentsOfTAEG($k, $ti, $ta, $n)
	{
		$t = (floatval($ti) + floatval($ta)) / 100;

		return (floatval($k) * $t/12) / (1 - pow(1 + $t/12, -intval($n)));
	}

	/**
	 * Human localized readable file size.
	 *
	 * @param integer	$size		Bytes
	 * @return array
	 */
	public static function l10nFileSize($size,$dec=2)
	{
		$aSize = self::getSize($size);

		return sprintf(__('c_c_x_bytes_size_in_'.$aSize['unit']),self::formatNumber($aSize['size'],$dec));
	}

	/**
	 * Human readable file size.
	 *
	 * Return an array like this
	 * array(
	 *     'size' => integer,
	 *     'unit' => string ('bytes', 'KB', 'MB'...)
	 * );
	 *
	 * @param integer	$size		Bytes
	 * @return array;
	 */
	public static function getSize($size)
	{
		static $kb = 1024;
		static $mb = 1048576;
		static $gb = 1073741824;
		static $tb = 1099511627776;

		if ($size < $kb) {
			return array('size' => $size, 'unit' => 'bytes');
		}
		elseif ($size < $mb) {
			return array('size' => ($size/$kb), 'unit' => 'KB');
		}
		elseif ($size < $gb) {
			return array('size' => ($size/$mb), 'unit' => 'MB');
		}
		elseif ($size < $tb) {
			return array('size' => ($size/$gb), 'unit' => 'GB');
		}
		else {
			return array('size' => ($size/$tb), 'unit' => 'TB');
		}
	}

	/**
	 * Checks intersection of two ranges
	 *
	 * @param int $nA1
	 * @param int $nA2
	 * @param int $nB1
	 * @param int $nB2
	 * @return boolean true if $nA1-$nA2 intersects $nB1-$nB2
	 */
	public static function isIntersecting($nA1,$nA2,$nB1, $nB2)
	{
		$nALow = min(intval($nA1),intval($nA2));
		$nAHigh = max(intval($nA1),intval($nA2));

		$nBLow = min(intval($nB1),intval($nB2));
		$nBHigh = max(intval($nB1),intval($nB2));

		if (($nALow<$nBLow && $nAHigh<$nBLow && $nAHigh<$nBHigh) || ($nALow>$nBHigh && $nAHigh>$nBHigh)) {
			return false;
		}

		return true;
	}

	/**
	 * Calcul le nombre de d'heures, de minutes et de secondes
	 * à partir d'un nombre de seconde.
	 *
	 * @param integer $iSeconds
	 * @return array
	 */
	public static function secondsToTime($iSeconds)
	{
		# extract hours
		$iHours = floor($iSeconds / 3600);

		# extract minutes
		$iDivisorForMinutes = $iSeconds % 3600;
		$iMinutes = floor($iDivisorForMinutes / 60);

		# extract the remaining seconds
		$iDivisorForSeconds = $iDivisorForMinutes % 60;
		$iSeconds = ceil($iDivisorForSeconds);

		# return the final array
		return array(
			'h' => (integer)$iHours,
			'm' => (integer)$iMinutes,
			's' => (integer)$iSeconds
		);
	}

	/**
	 * Retourne le nombre de d'heures, de minutes et de secondes
	 * à partir d'un nombre de seconde pour l'afficher.
	 *
	 * @param integer $iSeconds
	 * @return string
	 */
	public static function displayableSecondsToTime($iSeconds)
	{
		if ($iSeconds < 1) {
			return '&lt; 1 '.__('c_c_second');
		}

		$a = self::secondsToTime($iSeconds);

		$s = '';

		if ($a['h'] > 0) {
			$s .= $a['h'].' '.($a['h']>1 ? __('c_c_hours') : __('c_c_hour')).', ';
		}

		if ($a['m'] > 0 || $a['h'] > 0) {
			$s .= $a['m'].' '.($a['m']>1 ? __('c_c_minutes') : __('c_c_minute')).' et ';
		}

		if ($a['s'] > 0 || $a['m'] > 0 || $a['h'] > 0) {
			$s .= $a['s'].' '.__('c_c_seconds');
		}

		return $s;
	}

	/*
	 * Utilitaires sur les textes
	 *
	 */

	/**
	 * Vérifie qu'une chaine de caractères est bien encodée en UTF-8 et,
	 * si tel n(est pas le cas, la convertie.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toUtf8($string)
	{
		if ('UTF-8' != ($encoding = mb_detect_encoding($string, mb_detect_order(), true)))
		{
			if ($encoding) {
				$string = mb_convert_encoding($string, 'UTF-8', $encoding);
			}
			else {
				$string = mb_convert_encoding($string, 'UTF-8');
			}
		}

		return $string;
	}

	/**
	 * HTML attributes escape
	 *
	 * @param string $str	String to escape
	 * @return	string
	 */
	public static function escapeAttrHTML($str)
	{
		return \html::escapeHTML(str_replace(array('"','\''), array('','’'), $str));
	}

	/**
	 * Encode une adresse email pour le HTML
	 *
	 * @param string $str
	 * @return	string
	 */
	public static function emailEncode($str)
	{
		$encoded = bin2hex($str);
		$encoded = chunk_split($encoded, 2, '%');
		$encoded = '%'.substr($encoded, 0, strlen($encoded) - 1);
		return $encoded;
	}

	/**
	 * String to lower URL
	 *
	 * Transforms a string to a lowercase proper URL.
	 *
	 * @param string	$str			String to transform
	 * @param boolean	$with_slashes	Keep slashes in URL
	 * @return string
	 */
	public static function strToLowerURL($str,$with_slashes=true)
	{
		return strtolower(text::str2URL($str,$with_slashes));
	}

	/**
	 * Transform a string in a camelCase style
	 *
	 * @param string $str
	 */
	static public function strToCamelCase($str)
	{
		$str = self::strToLowerURL($str,false);

		$str = implode('',array_map('ucfirst',explode('_',$str)));
		$str = implode('',array_map('ucfirst',explode('-',$str)));

		return (string)(strtolower(substr($str,0,1)).substr($str,1));
	}

	/**
	 * Transform a string in underscored style
	 *
	 * @param string $str
	 */
	static public function strToUnderscored($str)
	{
		$str = self::strToLowerURL($str,false);
		return (string)str_replace('-','_',$str);
	}

	/**
	 * Transform a string in slug regarding to configuration.
	 *
	 * @param string $str
	 */
	static public function strToSlug($str, $with_slashes=true)
	{
		/*
		static $sType = null;

		if (is_null($sType))
		{
			global $okt;

			if (isset($okt) && !empty($okt->config->slug_type)) {
				$sType = $okt->config->slug_type;
			}
			else {
				$sType = 'ascii';
			}
		}
		*/

		switch ($GLOBALS['okt']->config->slug_type)
		{
			case 'utf8':
				return text::tidyURL($str, $with_slashes);

			case 'ascii':
			default:
				return self::strToLowerURL($str, $with_slashes);
		}
	}

	/**
	 * Convertis \r\n et \r en \n
	 *
	 * @param	string	str		La chaine à convertir
	 * @return string La chaine convertie
	 */
	public static function linebreaks($str)
	{
		return str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
	}

	/**
	 * Convertis les sauts de ligne en paragraphes HTML
	 *
	 * @param	string	str		La chaine à convertir
	 * @return string La chaine convertie
	 */
	public static function nlToP($str)
	{
		$str = trim($str);
		$str = self::linebreaks($str);
		$str = str_replace("\n", "</p>\n<p>", $str);
		$str = str_replace('<p></p>', '', $str);
		return '<p>'.$str.'</p>'.PHP_EOL;
	}

	/**
	 * Convertis les sauts de ligne en paragraphes et saut de lignes HTML
	 *
	 * @param	string	str		La chaine à convertir
	 * @return string La chaine convertie
	 */
	public static function nlToPbr($str)
	{
		$str = trim($str);
		$str = self::linebreaks($str);
		$str = str_replace("\n", '<br />', $str);
		$str = str_replace('<br /><br />', "</p>\n<p>", $str);
		$str = str_replace('<p></p>', '', $str);
		return '<p>'.$str.'</p>'.PHP_EOL;
	}

	/**
	 * Supprime les sauts de ligne dans la chaine
	 *
	 * @param	string	str		La chaine à convertir
	 * @return string La chaine convertie
	 */
	public static function clean($str)
	{
		$str = \html::clean($str);
		$str = self::linebreaks($str);
		$str = str_replace("\n", ' ', $str);

		return $str;
	}

	/**
	 * Retourne une chaine de caractère incrémentée
	 * en fonction d'une liste donnée
	 *
	 * @param array $list
	 * @param string $url
	 * @return string
	 */
	public static function getIncrementedString($list, $str, $prefix='')
	{
		foreach ($list as $k=>$v) {
			if (!preg_match('/^('.preg_quote($str,'/').')('.preg_quote($prefix,'/').'?)([0-9]*)$/',$v)) {
				unset($list[$k]);
			}
		}
		natsort($list);
		$t_url = end($list);

		if (preg_match('/^('.preg_quote($str,'/').')('.preg_quote($prefix,'/').'+)([0-9]+)$/',$t_url,$m)) {
			$i = (integer) $m[3];
		} else {
			$i = 1;
		}

		return $str.$prefix.($i+1);
	}

	public static function removeAttrFromUrl($sParamKey,$sUrl)
	{
		return preg_replace('/(&|(?<=\?))'.$sParamKey.'=.*?(?=&|$)/', '', $sUrl);
	}

	/**
	 * Force le téléchargement d'un fichier $fileName
	 *
	 * @param string $fileName
	 */
	public static function forceDownload($fileName=null)
	{
		# désactive le temps max d'exécution
		set_time_limit(0);

		# on a bien une demande de téléchargement de fichier
		if (empty($fileName)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		$name = basename($fileName);

		# vérifie l'existence et l'accès en lecture au fichier
		if (!is_file($fileName) || !is_readable($fileName)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		# calcul la taille total du fichier
		$size = filesize($fileName);

		# désactivation compression GZip
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		# fermeture de la session
		session_write_close();

		# désactive la mise en cache
		header('Cache-Control: no-cache, must-revalidate');
		header('Cache-Control: post-check=0,pre-check=0');
		header('Cache-Control: max-age=0');
		header('Pragma: no-cache');
		header('Expires: 0');

		# force le téléchargement du fichier avec un beau nom
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="'.$name.'"');

		# on indique au client la prise en charge de l'envoi de données par portion.
		header("Accept-Ranges: bytes");

		# par défaut, on commence au début du fichier
		$start = 0;

		# par défaut, on termine à la fin du fichier (envoi complet)
		$end = $size - 1;
		if (isset($_SERVER['HTTP_RANGE']))
		{
			# l'entête doit être dans un format valide
			if (!preg_match('#bytes=([0-9]+)?-([0-9]+)?(/[0-9]+)?#i', $_SERVER['HTTP_RANGE'], $m)) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				exit;
			}

			# modification de $start et $end et on vérifie leur validité
			$start = !empty($m[1]) ? (integer)$m[1] : null;
			$end = !empty($m[2]) ? (integer)$m[2] : $end;
			if (!$start && !$end || $end !== null && $end >= $size || $end && $start && $end < $start) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				exit;
			}

			# si $start n'est pas spécifié, on commence à $size - $end
			if ($start === null) {
				$start = $size - $end;
				$end -= 1;
			}

			# indique l'envoi d'un contenu partiel
			header('HTTP/1.1 206 Partial Content');

			# décrit quelle plage de données est envoyée
			header('Content-Range: '.$start.'-'.$end.'/'.$size);
		}

		# on indique bien la taille des données envoyées
		header('Content-Length: '.($end-$start+1));

		# ouverture du fichier en lecture et en mode binaire
		$f = fopen($fileName, 'rb');

		# on se positionne au bon endroit ($start)
		fseek($f, $start);

		# cette variable sert à connaître le nombre d'octet envoyé.
		$remainingSize = $end-$start+1;

		# calcul la taille des lots de données je choisi 4ko ou $remainingSize si plus petit que 4ko
		$length = $remainingSize < 4096 ? $remainingSize : 4096;

		while (($datas = fread($f, $length)) !== false)
		{
			# envoie des données vers le client
			echo $datas;

			# on a envoyé $length octets, on le soustrait alors du nombre d'octets restant
			$remainingSize -= $length;

			# si tout est envoyé, on quitte la boucle
			if ($remainingSize <= 0) {
				break;
			}

			# si reste moins de $length octets à envoyer, on le rédefinit en conséquence
			if ($remainingSize < $length) {
				$length = $remainingSize;
			}
		}

		fclose($f);
	}

	/**
	 * Retourne le type de media en fonction du type mime
	 *
	 * @param string $mime_type
	 * @return string
	 */
	public static function getMediaType($mime_type)
	{
			$type_prefix = explode('/',$mime_type);
			$type_prefix = $type_prefix[0];

			$media_type = null;

			switch ($type_prefix)
			{
				case 'image':
					$media_type = 'image';
					break;

				case 'audio':
					$media_type = 'audio';
					break;

				case 'text':
					$media_type = 'text';
					break;

				case 'video':
					$media_type = 'video';
					break;

				default:
					$media_type = 'blank';
			}

			switch ($mime_type)
			{
				case 'application/msword':
				case 'application/vnd.oasis.opendocument.text':
				case 'application/vnd.sun.xml.writer':
				case 'application/postscript':
					$media_type = 'document';
					break;

				case 'application/pdf':
					$media_type = 'pdf';
					break;

				case 'application/msexcel':
				case 'application/vnd.oasis.opendocument.spreadsheet':
				case 'application/vnd.sun.xml.calc':
					$media_type = 'spreadsheet';
					break;

				case 'application/mspowerpoint':
				case 'application/vnd.oasis.opendocument.presentation':
				case 'application/vnd.sun.xml.impress':
					$media_type = 'presentation';
					break;

				case 'application/x-debian-package':
				case 'application/x-gzip':
				case 'application/x-java-archive':
				case 'application/rar':
				case 'application/x-redhat-package-manager':
				case 'application/x-tar':
				case 'application/x-gtar':
				case 'application/zip':
					$media_type = 'package';
					break;

				case 'application/octet-stream':
					$media_type = 'executable';
					break;
				case 'application/x-shockwave-flash':
					$media_type = 'video';
					break;

				case 'application/ogg':
					$media_type = 'audio';
					break;

				case 'text/html':
					$media_type = 'html';
					break;
			}

			return $media_type;
	}

	public static function setDefaultModuleTpl($sModuleId, $sSection, $sTemplate)
	{
		global $okt;

		if (!$okt->modules->moduleExists($sModuleId) || !isset($okt->{$sModuleId}->config) || !isset($okt->{$sModuleId}->config->templates)) {
			return false;
		}

		$aTemplates = $okt->{$sModuleId}->config->templates;
		$aTemplates[$sSection]['default'] = $sTemplate;

		$okt->{$sModuleId}->config->templates = $aTemplates;

		$okt->{$sModuleId}->config->writeCurrent();

		return true;
	}

	/**
	 * Retourne la configuration des tailles des miniatures des images.
	 *
	 * @param string $sModuleId
	 * @param string $sWidth_min
	 * @param string $sheight_min
	 * @param string $iWidth
	 * @param string $iHeight
	 *
	 * @return string
	 */
	public static function setDefaultModuleImageSize($sModuleId, $aImages)
	{
		global $okt;

		if (!$okt->modules->moduleExists($sModuleId) || !isset($okt->{$sModuleId}->config) || !isset($okt->{$sModuleId}->config->images)) {
			return false;
		}

		$okt->{$sModuleId}->config->images = array_merge($okt->{$sModuleId}->config->images, $aImages);

		$okt->{$sModuleId}->config->writeCurrent();

		return true;
	}

	/**
	 * Retourne les choix possibles d'activation d'un champ (désactivé/activé/activé et obligatoire).
	 *
	 * @param boolean $bMandatory
	 * @return array
	 */
	public static function getStatusFieldChoices($bMandatory=true)
	{
		$aChoices = array(
			__('c_c_Disabled') => 0,
			__('c_c_Enabled') => 1
		);

		if ($bMandatory) {
			$aChoices[__('c_c_Enabled_Mandatory')] = 2;
		}

		return $aChoices;
	}

	/**
	 * Retourne le titre internationnalisé du site.
	 *
	 * @return string
	 */
	public static function getSiteTitle($sLanguage=null, $sDefault=null)
	{
		global $okt;

		if ($sLanguage !== null && !empty($okt->config->title[$sLanguage])) {
			return $okt->config->title[$sLanguage];
		}
		elseif (!empty($okt->config->title[$okt->user->language])) {
			return $okt->config->title[$okt->user->language];
		}
		elseif (!empty($okt->config->title[$okt->config->language])) {
			return $okt->config->title[$okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne la description internationnalisée du site.
	 *
	 * @return string
	 */
	public static function getSiteDescription($sLanguage=null, $sDefault=null)
	{
		global $okt;

		if ($sLanguage !== null && !empty($okt->config->desc[$sLanguage])) {
			return $okt->config->desc[$sLanguage];
		}
		elseif (!empty($okt->config->desc[$okt->user->language])) {
			return $okt->config->desc[$okt->user->language];
		}
		elseif (!empty($okt->config->desc[$okt->config->language])) {
			return $okt->config->desc[$okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne le title tag internationnalisé du site.
	 *
	 * @return string
	 */
	public static function getSiteTitleTag($sLanguage=null, $sDefault=null)
	{
		global $okt;

		if ($sLanguage !== null && !empty($okt->config->title_tag[$sLanguage])) {
			return $okt->config->title_tag[$sLanguage];
		}
		elseif (!empty($okt->config->title_tag[$okt->user->language])) {
			return $okt->config->title_tag[$okt->user->language];
		}
		elseif (!empty($okt->config->title_tag[$okt->config->language])) {
			return $okt->config->title_tag[$okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne la meta description internationnalisée du site.
	 *
	 * @return string
	 */
	public static function getSiteMetaDesc($sLanguage=null, $sDefault=null)
	{
		global $okt;

		if ($sLanguage !== null && !empty($okt->config->meta_description[$sLanguage])) {
			return $okt->config->meta_description[$sLanguage];
		}
		elseif (!empty($okt->config->meta_description[$okt->user->language])) {
			return $okt->config->meta_description[$okt->user->language];
		}
		elseif (!empty($okt->config->meta_description[$okt->config->language])) {
			return $okt->config->meta_description[$okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne les meta keywords internationnalisés du site.
	 *
	 * @return string
	 */
	public static function getSiteMetaKeywords($sLanguage=null, $sDefault=null)
	{
		global $okt;

		if ($sLanguage !== null && !empty($okt->config->meta_keywords[$sLanguage])) {
			return $okt->config->meta_keywords[$sLanguage];
		}
		elseif (!empty($okt->config->meta_keywords[$okt->user->language])) {
			return $okt->config->meta_keywords[$okt->user->language];
		}
		elseif (!empty($okt->config->meta_keywords[$okt->config->language])) {
			return $okt->config->meta_keywords[$okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne la version courante d'okatea, null si non trouvée
	 *
	 * @return string
	 */
	public static function getVersion()
	{
		if (file_exists(OKT_ROOT_PATH.'/VERSION')) {
			return trim(file_get_contents(OKT_ROOT_PATH.'/VERSION'));
		}

		return null;
	}

	/**
	 * Retourne la révision de la distribution courante d'okatea, null si non trouvée
	 *
	 * @return string
	 */
	public static function getRevision()
	{
		if (file_exists(OKT_ROOT_PATH.'/oktDoc/REVISION')) {
			return trim(file_get_contents(OKT_ROOT_PATH.'/oktDoc/REVISION'));
		}

		return null;
	}

	/**
	 * Retourne le temps d'execution du script
	 *
	 * @return float
	 */
	public static function getExecutionTime()
	{
		$exec_time = null;

		if (OKT_XDEBUG)
		{
			$exec_time = sprintf('%.3f', xdebug_time_index());
		}
		elseif (defined('OKT_START_TIME'))
		{
			$time = explode(' ', microtime());
			$exec_time = sprintf('%.3f', ((float)$time[0] + (float)$time[1]) - OKT_START_TIME);
		}

		return $exec_time;
	}

	/**
	 * Retourne la liste des fichiers cache d'Okatea
	 *
	 * @return array
	 */
	public static function getOktCacheFiles($bForce=false)
	{
		static $aCacheFiles=null;

		if (is_array($aCacheFiles) && !$bForce) {
			return $aCacheFiles;
		}

		$aCacheFiles = array();
		foreach (new \DirectoryIterator(OKT_CACHE_PATH) as $oFileInfo)
		{
			if ($oFileInfo->isDot() || in_array($oFileInfo->getFilename(),array('.svn','.htaccess','index.html'))) {
				continue;
			}

			if ($oFileInfo->isDir())
			{
				foreach (new \DirectoryIterator($oFileInfo->getPathname()) as $oFileInfoInDir)
				{
					if ($oFileInfoInDir->isDot() || in_array($oFileInfoInDir->getFilename(),array('.svn','.htaccess','index.html'))) {
						continue;
					}

					$aCacheFiles[] = $oFileInfo->getFilename().'/'.$oFileInfoInDir->getFilename();
				}
			}
			else {
				$aCacheFiles[] = $oFileInfo->getFilename();
			}

		}
		natsort($aCacheFiles);

		return $aCacheFiles;
	}

	/**
	 * Supprime les fichiers cache d'Okatea
	 *
	 * @return void
	 */
	public static function deleteOktCacheFiles()
	{
		$aCacheFiles = self::getOktCacheFiles();

		foreach ($aCacheFiles as $file)
		{
			if (is_dir(OKT_CACHE_PATH.'/'.$file)) {
				\files::deltree(OKT_CACHE_PATH.'/'.$file);
			}
			else {
				unlink(OKT_CACHE_PATH.'/'.$file);
			}
		}
	}

	/**
	 * Retourne la liste des fichiers cache public d'Okatea
	 *
	 * @return array
	 */
	public static function getOktPublicCacheFiles($bForce=false)
	{
		static $aCacheFiles=null;

		if (is_array($aCacheFiles) && !$bForce) {
			return $aCacheFiles;
		}

		$aCacheFiles = array();
		foreach (new \DirectoryIterator(OKT_PUBLIC_PATH.'/cache') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || in_array($oFileInfo->getFilename(),array('.svn','.htaccess','index.html'))) {
				continue;
			}

			$aCacheFiles[] = $oFileInfo->getFilename();
		}
		natsort($aCacheFiles);

		return $aCacheFiles;
	}

	/**
	 * Supprime les fichiers cache public d'Okatea
	 *
	 * @return void
	 */
	public static function deleteOktPublicCacheFiles($bForce=false)
	{
		$aCacheFiles = self::getOktPublicCacheFiles($bForce);

		foreach ($aCacheFiles as $file)
		{
			if (is_dir(OKT_PUBLIC_PATH.'/cache/'.$file)) {
				\files::deltree(OKT_PUBLIC_PATH.'/cache/'.$file);
			}
			else {
				unlink(OKT_PUBLIC_PATH.'/cache/'.$file);
			}
		}
	}

	/**
	 * Cherche la classe html2pdf sur le serveur
	 *
	 * @return boolean
	 */
	public static function serverHasHtml2pdf()
	{
		static $bFounded = null;

		if ($bFounded !== null) {
			return $bFounded;
		}

		$bFounded = false;
		$sFilepath = '/PDF/html2pdf_v4.03/html2pdf.class.php';
		$aIncludePath = explode(PATH_SEPARATOR,get_include_path());

		foreach ($aIncludePath as $sPath)
		{
			if (file_exists($sPath.$sFilepath)) {
				$bFounded = true;
				break;
			}
		}

		return $bFounded;
	}

	/**
	 * Generate a random key of length $len
	 *
	 * @param $len
	 * @param $readable
	 * @param $hash
	 * @return string
	 */
	public static function random_key($len, $readable=false, $hash=false)
	{
		$key = '';

		if ($hash) {
			$key = substr(sha1(uniqid(rand(), true)), 0, $len);
		}
		else if ($readable)
		{
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

			for ($i = 0; $i < $len; ++$i) {
				$key .= substr($chars, (mt_rand() % strlen($chars)), 1);
			}
		}
		else {
			for ($i = 0; $i < $len; ++$i) {
				$key .= chr(mt_rand(33, 126));
			}
		}

		return $key;
	}

	/**
	 * Generates a salted, SHA-1 hash of $str
	 *
	 * @param $str
	 * @param $salt
	 * @return string
	 */
	public static function hash($str, $salt)
	{
		return sha1($salt.sha1($str));
	}

	public static function getMinifyReplacements($oktConfig)
	{
		static $aReplacements = null;

		if (is_null($aReplacements))
		{
			$aReplacements = array(
				'aSearch' => array(
					'%APP_URL%',
					'%PUBLIC_URL%',
					'%THEME%',
					'%MOBILE_THEME%',
					'%ADMIN_THEME%',
					'%PUBLIC_THEME%'
				),
				'aReplace' => array(
					$oktConfig->app_path,
					$oktConfig->app_path.OKT_PUBLIC_DIR,
					$oktConfig->app_path.OKT_THEMES_DIR.'/'.$oktConfig->theme,
					$oktConfig->app_path.OKT_THEMES_DIR.'/'.$oktConfig->theme_mobile,
					$oktConfig->admin_theme,
					$oktConfig->public_theme
				)
			);
		}

		return $aReplacements;
	}

	/**
	 * Format un chemin d'application en supprimant et/ou laissant les slash de début et de fin.
	 *
	 * @param string $sPath
	 * @param boolean $bStartingSlash (true)
	 * @param boolean $bTrailingSlash (true)
	 * @return string
	 */
	public static function formatAppPath($sPath, $bStartingSlash=true, $bTrailingSlash=true)
	{
		$sPath = preg_replace('|/+$|', '', $sPath);
		$sPath = preg_replace('|^/+|', '', $sPath);

		if ($bStartingSlash) {
			$sPath = '/'.$sPath;
		}

		if ($bTrailingSlash) {
			$sPath = $sPath.'/';
		}

		$sPath = preg_replace('|/+|', '/', $sPath);

		return $sPath;
	}

	/**
	 * Construit un lien vers un phpMyAdmin.
	 *
	 * @param string $url
	 * @param string $user
	 * @param string $password
	 * @return string
	 */
	public static function getPhpMyAdminUrl($url, $user, $password)
	{
		$a = explode('://',$url);

		if (count($a) == 1) {
			$a = array('http',$url);
		}

		return $a[0].'://'.$user.':'.$password.'@'.$a[1];
	}

	/**
	* Trim request
	*
	* Trims every value in GET, POST, REQUEST and COOKIE vars.
	* Removes magic quotes if magic_quote_gpc is on.
	*/
	public static function trimRequest()
	{
		if(!empty($_GET)) {
			array_walk($_GET,array('self','trimRequestHandler'));
		}

		if(!empty($_POST)) {
			array_walk($_POST,array('self','trimRequestHandler'));
		}

		if(!empty($_REQUEST)) {
			array_walk($_REQUEST,array('self','trimRequestHandler'));
		}

		if(!empty($_COOKIE)) {
			array_walk($_COOKIE,array('self','trimRequestHandler'));
		}
	}

	private static function trimRequestHandler(&$v,$key)
	{
		$v = self::trimRequestInVar($v);
	}

	private static function trimRequestInVar($value)
	{
		static $magic_quotes_gpc = null;

		if ($magic_quotes_gpc === null) {
			$magic_quotes_gpc = (boolean)get_magic_quotes_gpc();
		}

		if (is_array($value))
		{
			$result = array();
			foreach ($value as $k => $v)
			{
				if (is_array($v)) {
					$result[$k] = self::trimRequestInVar($v);
				}
				else
				{
					if ($magic_quotes_gpc) {
						$v = stripslashes($v);
					}

					$result[$k] = trim($v);
				}
			}

			return $result;
		}
		else
		{
			if ($magic_quotes_gpc) {
				$value = stripslashes($value);
			}

			return trim($value);
		}
	}


} #class