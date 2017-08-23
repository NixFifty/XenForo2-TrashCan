<?php

namespace NF\TrashCan\XF\Entity;

class Thread extends XFCP_Thread
{
	public function canTrash(&$error = null)
	{
		if ($this->discussion_state == 'deleted')
		{
			$error = \XF::phraseDeferred('nf_trashcan_cannot_trash_deleted_thread');

			return false;
		}

		if ($this->node_id == \XF::options()->nf_trashcan_trash_forum)
		{
			$error = \XF::phraseDeferred('nf_trashcan_cannot_trash_trashed_thread');

			return false;
		}

		return \XF::visitor()->hasNodePermission($this->node_id, 'trash');
	}
}