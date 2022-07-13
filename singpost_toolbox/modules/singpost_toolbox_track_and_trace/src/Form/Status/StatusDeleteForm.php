<?php


namespace Drupal\singpost_toolbox_track_and_trace\Form\Status;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_track_and_trace\Model\Status;

/**
 * Class StatusDeleteForm
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Form\Status
 */
class StatusDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_track_and_trace\Model\Status
	 */
	protected $_model;

	/**
	 * StatusDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_toolbox_track_and_trace\Model\Status $model
	 */
	public function __construct(Status $model){
		$this->_model = $model;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'tnt_status_delete_form';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return $this->t('Delete TnT Status');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete TnT Status: "<i>@type?</i>"', [
			'@type' => $this->_model->type
		]);
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.toolbox.track_and_trace.status');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$model = $this->_model;

		if ($this->_model->delete()){
			$this->messenger()
			     ->addMessage($this->t('TnT Status "<i>@type</i>" has been successfully deleted.',
				     [
					     '@type' => $model->type
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this TnT Status.'));
		}

		$form_state->setRedirect('singpost.toolbox.track_and_trace.status');
	}
}