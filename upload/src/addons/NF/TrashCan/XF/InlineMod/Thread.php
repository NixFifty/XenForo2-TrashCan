<?php

namespace NF\TrashCan\XF\InlineMod;

class Thread extends XFCP_Thread
{
	public function getPossibleActions()
	{
		$actions = parent::getPossibleActions();

		$actions['nf_trash'] = $this->getActionHandler('NF\TrashCan:Thread\Trash');

		return $actions;
	}
}