<?php

namespace NF\TrashCan\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
	public function actionTrash(ParameterBag $params)
	{
		/** @var \NF\TrashCan\XF\Entity\Thread $thread */
		$thread = $this->assertViewableThread($params->thread_id);
		if (!$thread->canTrash($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$reason = $this->filter('reason', 'str');

			/** @var \NF\TrashCan\Service\Thread\Trasher $trasher */
			$trasher = $this->service('NF\TrashCan:Thread\Trasher', $thread);

			if ($this->filter('starter_alert', 'bool'))
			{
				$trasher->setSendAlert(true, $this->filter('starter_alert_reason', 'str'));
			}

			$trasher->trash($reason);

			$this->plugin('XF:InlineMod')->clearIdFromCookie('thread', $thread->thread_id);

			return $this->redirect($this->buildLink('forums', $trasher->getTrashForum()));
		}
		else
		{
			$viewParams = [
				'thread' => $thread,
				'forum' => $thread->Forum
			];

			return $this->view('NF\TrashCan:Thread\Trash', 'nf_trashcan_thread_trash', $viewParams);
		}
	}
}