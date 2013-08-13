<?php
/**
 * Pile d'erreurs pour l'administration.
 *
 * @addtogroup Okatea
 *
 */

class adminErrors extends htmlStack
{
	/**
	 * Ajoute une erreur à la pile des erreurs.
	 *
	 * @param $msg string
	 * @return void
	 */
	public function set($msg)
	{
		$this->setItem($msg);
	}

	/**
	 * Formate et retourne les erreurs présentes dans la pile.
	 *
	 * @param $format string
	 * @return string
	 */
	public function getErrors($format='<div class="errors_box">%s</div>')
	{
		return sprintf($format,parent::getHTML());
	}

	/**
	 * Indique si il y a des avertissements
	 *
	 * @return boolean
	 */
	public function hasError()
	{
		return $this->hasItem();
	}

} # class adminErrors
