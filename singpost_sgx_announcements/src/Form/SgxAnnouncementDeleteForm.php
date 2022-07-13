<?php


namespace Drupal\singpost_sgx_announcements\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_sgx_announcements\Model\SgxAnnouncement;

/**
 * Class SgxAnnouncementDeleteForm
 *
 * @package Drupal\singpost_sgx_announcements\Form
 */
class SgxAnnouncementDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_sgx_announcements\Model\SgxAnnouncement
	 */
	protected $_sgx_announcement;

	/**
	 * SgxAnnouncementDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_sgx_announcements\Model\SgxAnnouncement $sgx_announcement
	 */
	public function __construct(SgxAnnouncement $sgx_announcement){
		$this->_sgx_announcement = $sgx_announcement;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_delete_form';
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.sgx.announcements.admin');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return $this->t('Delete SGX Announcement');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete SGX Announcement: "<i>@sgx</i>" ?', [
			'@sgx' => $this->_sgx_announcement->title
		]);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$sgx_announcement = $this->_sgx_announcement;

		if ($this->_sgx_announcement->delete()){
			$this->messenger()
			     ->addMessage($this->t('SGX Announcement "<i>@sgx</i>" has been successfully deleted.',
				     [
					     '@sgx' => $sgx_announcement->title
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this SGX announcement.'));
		}

		$form_state->setRedirect('singpost.sgx.announcements.admin');
	}
}