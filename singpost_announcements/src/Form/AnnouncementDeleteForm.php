<?php

namespace Drupal\singpost_announcements\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_announcements\Model\Announcement;

/**
 * Class AnnouncementDeleteForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class AnnouncementDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_announcements\Model\Announcement
	 */
	protected $_announcement;

	/**
	 * AnnouncementDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_announcements\Model\Announcement $announcement
	 */
	public function __construct(Announcement $announcement){
		$this->_announcement = $announcement;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'announcement_delete';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion(){
		return $this->t('Delete Announcement');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete Announcement: "<i>@announcement?</i>"', [
			'@announcement' => $this->_announcement->title
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl(){
		return new Url('singpost.announcements.admin');
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$announcement = $this->_announcement;

		if ($this->_announcement->delete()){
			$this->messenger()
			     ->addMessage($this->t('Announcement "<i>@announcement</i>" has been successfully deleted.',
				     [
					     '@announcement' => $announcement->title
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this announcement.'));
		}

		$form_state->setRedirect('singpost.announcements.admin');
	}
}