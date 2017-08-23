<?php

namespace NF\TrashCan\InlineMod\Thread;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class Trash extends AbstractAction
{
	public function getTitle()
	{
		return \XF::phrase('nf_trashcan_inline_moderation_trash_threads...');
	}

	protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
	{
		/** @var \NF\TrashCan\XF\Entity\Thread $entity */
		return $entity->canTrash();
	}

	protected function applyToEntity(Entity $entity, array $options)
	{
		/** @var \NF\TrashCan\Service\Thread\Trasher $trasher */
		$trasher = $this->app()->service('NF\TrashCan:Thread\Trasher', $entity);

		if ($options['alert'])
		{
			$trasher->setSendAlert(true, $options['alert_reason']);
		}

		$trasher->trash($options['reason']);

		$this->returnUrl = $this->app()->router()->buildLink('forums', $trasher->getTrashForum());
	}

	public function getBaseOptions()
	{
		return [
			'reason' => '',
			'alert' => false,
			'alert_reason' => ''
		];
	}

	public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
	{
		$viewParams = [
			'threads' => $entities,
			'total' => count($entities)
		];

		return $controller->view('NF\TrashCan:Public:InlineMod\Thread\Trash', 'nf_trashcan_inline_mod_thread_trash', $viewParams);
	}

	public function getFormOptions(AbstractCollection $entities, Request $request)
	{
		$options = [
			'reason' => $request->filter('reason', 'str'),
			'alert' => $request->filter('starter_alert', 'bool'),
			'alert_reason' => $request->filter('starter_alert_reason', 'str')
		];

		return $options;
	}
}