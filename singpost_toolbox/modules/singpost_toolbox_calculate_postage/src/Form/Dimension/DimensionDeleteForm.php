<?php

namespace Drupal\singpost_toolbox_calculate_postage\Form\Dimension;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_calculate_postage\Model\Dimension;

/**
 * Class DimensionDeleteForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form
 */
class DimensionDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Model\Dimension
	 */
	protected $_dimension;

	/**
	 * DimensionDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_toolbox_calculate_postage\Model\Dimension $dimension
	 */
	public function __construct(Dimension $dimension){
		$this->_dimension = $dimension;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'dimension_delete';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion(){
		return $this->t('Delete Dimension');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete Dimension: "<i>@dimension?</i>"',
			[
				'@dimension' => $this->_dimension->size_code,
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl(){
		return new Url('singpost.toolbox.calculate.dimension.manage');
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$dimension = $this->_dimension;

		if ($this->_dimension->delete()){
			$this->messenger()
			     ->addMessage($this->t('Dimension "<i>@dimension</i>" has been successfully deleted.',
				     [
					     '@dimension' => $dimension->size_code,
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this Dimension.'));
		}

		$form_state->setRedirect('singpost.toolbox.calculate.dimension.manage');
	}

}