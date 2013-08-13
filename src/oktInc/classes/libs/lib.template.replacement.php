<?php
/**
 * @class templateReplacement
 * @ingroup okt_classes_libs
 * @brief Systeme basique de remplacement de variables dans les templates
 *
 * templateReplacement::parseFile('chemin/fichier.tpl', array('foo'=>'bar','baz'=>'boor'));
 * templateReplacement::parse('une chaine de caractères', array('foo'=>'bar','baz'=>'boor'));
 */


class templateReplacement
{
	static public $key_start_string = '%';
	static public $key_end_string = '%';

	/**
	 * Parse file
	 *
	 * @param string $sFileName template file
	 * @param array $aVariables variables
	 * @return string parsed file or empty string
	 */
	public static function parseFile($sFileName, $aVariables)
	{
		if (file_exists($sFileName)) {
			return self::parse(file_get_contents($sFileName),$aVariables);
		}
		else {
			//trigger_error('error.template.no_file '.$sFileName,E_USER_WARNING);
			return false;
		}
	}

	public static function setStartString($sStr)
	{
		self::$key_start_string = $sStr;
	}

	public static function setEndString($sStr)
	{
		self::$key_end_string = $sStr;
	}

	/**
	 * Parse string
	 *
	 * @param string $sTemplate template string
	 * @param array $aVariables variables
	 * @return string Parsed template
	 */
	public static function parse($sTemplate, $aVariablesRaw)
	{
		ksort($aVariablesRaw);

		$aVariables = self::prepareVars($aVariablesRaw);

		return str_replace(array_keys($aVariables), array_values($aVariables), $sTemplate);
	}

	/**
	 * Preparation of variables
	 *
	 * converts ('key'=>'value') into ('%KEY%'=>(strval)value)
	 *
	 * @param array $aVariables
	 * @return array prepared variables
	 */
	public static function prepareVars($aVariables)
	{
		$aResult = array();

		foreach ((array)$aVariables as $sKey=>$mValue)
		{
			if (substr($sKey,0,1) != self::$key_start_string) {
				$sKey = self::$key_start_string.$sKey;
			}

			if (substr($sKey,-1) != self::$key_end_string) {
				$sKey = $sKey.self::$key_end_string;
			}

			$aResult[strtoupper($sKey)] = strval($mValue);
		}

		return $aResult;
	}

} # class
