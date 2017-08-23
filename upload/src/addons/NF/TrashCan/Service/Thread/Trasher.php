<?php

namespace NF\TrashCan\Service\Thread;

use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;

class Trasher extends AbstractService
{
	/**
	 * @var Thread
	 */
	protected $thread;

	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	/**
	 * @var \XF\Entity\Forum
	 */
	protected $trashForum = null;

	public function __construct(\XF\App $app, Thread $thread)
	{
		parent::__construct($app);

		$this->setThread($thread);
	}

	public function setThread(Thread $thread)
	{
		$this->thread = $thread;
	}

	public function getThread()
	{
		return $this->thread;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function trash($reason = '', $type = null)
	{
		if ($type == null)
		{
			$type = \XF::options()->nf_trashcan_trash_mode;
		}

		switch ($type)
		{
			case 'delete-trash':
			case 'trash':
				break;

			default:
				throw new \InvalidArgumentException("Unexpected trash type '$type'. Should be delete-trash or trash.");
		}

		$trashForum = $this->getTrashForum();

		if (!$trashForum)
		{
			return false;
		}

		$user = $this->user ?: \XF::visitor();

		$wasVisible = $this->thread->discussion_state == 'visible';

		$result = true;

		\XF::db()->beginTransaction();

		$this->thread->setOption('log_moderator', false);

		if ($type == 'delete-trash')
		{
			$result = $this->thread->softDelete($reason, $user);
		}

		if ($result)
		{
			/** @var \XF\Service\Thread\Mover $mover */
			$mover = $this->service('XF:Thread\Mover', $this->thread);
			$mover->setNotifyWatchers(false);
			$mover->setSendAlert(false);
			$result = $result && $mover->move($trashForum);
		}

		$this->app->logger()->logModeratorAction('thread', $this->thread, 'nf_trash', ['reason' => $reason]);

		\XF::db()->commit();

		if ($result && $wasVisible && $this->alert && $this->thread->user_id != $user->user_id)
		{
			/** @var \XF\Repository\Thread $threadRepo */
			$threadRepo = $this->repository('XF:Thread');
			$threadRepo->sendModeratorActionAlert($this->thread, 'delete', $this->alertReason);
		}

		return $result;
	}

	/**
	 * @return null|\XF\Entity\Forum|\XF\Mvc\Entity\Entity
	 */
	public function getTrashForum()
	{
		if ($this->trashForum == null)
		{
			$this->trashForum = $this->em()->find('XF:Forum', \XF::options()->nf_trashcan_trash_forum);
		}

		return $this->trashForum;
	}
}