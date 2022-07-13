<?php

namespace Drupal\singpost_toolbox_locate_us\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;

/**
 * Class LocateUsTypeDeleteForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class LocateUsTypeDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_locate_us\Model\LocateUsType
	 */
	protected $_locate_us_type;

	/**
	 * LocateUsTypeDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_toolbox_locate_us\Model\LocateUsType $locate_us_type
	 */
	public function __construct(LocateUsType $locate_us_type){
		$this->_locate_us_type = $locate_us_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'locate_us_type_delete';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion(){
		return $this->t('Delete Locate Us Type');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete Locate Us Type: "<i>@locate_us_type?</i>"',
			[
				'@locate_us_type' => $this->_locate_us_type->title,
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl(){
		return new Url('singpost.toolbox.locate_us.admin.type');
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$locate_us_type = $this->_locate_us_type;

		if ($this->_locate_us_type->delete()){
			$this->messenger()
			     ->addMessage($this->t('Locate Us Type "<i>@locate_us_type</i>" has been successfully deleted.',
				     [
					     '@locate_us_type' => $locate_us_type->title,
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this Locate Us Type.'));
		}

		$form_state->setRedirect('singpost.toolbox.locate_us.admin.type');
	}

}