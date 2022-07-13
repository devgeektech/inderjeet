<?php


namespace Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel;

/**
 * Class DeliveryServiceDeleteForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService
 */
class DeliveryServiceDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel
	 */
	protected $_model;

	/**
	 * DeliveryServiceDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel $model
	 */
	public function __construct(DeliveryServiceModel $model){
		$this->_model = $model;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'delivery_service_delete_form';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return $this->t('Delete Delivery Service');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete Delivery Service Name: "<i>@name?</i>"', [
			'@name' => $this->_model->delivery_service_name
		]);
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.toolbox.calculate.delivery_service.manage');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$model = $this->_model;

		if ($this->_model->delete()){
			$this->messenger()
			     ->addMessage($this->t('Delivery Service Name "<i>@name</i>" has been successfully deleted.',
				     [
					     '@name' => $model->delivery_service_name
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this Delivery Service.'));
		}

		$form_state->setRedirect('singpost.toolbox.calculate.delivery_service.manage');
	}
}