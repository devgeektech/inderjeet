<?php


namespace Drupal\singpost_toolbox_track_and_trace\Form\Status;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;

/**
 * Class StatusForm
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Form\Status
 */
class StatusForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_model;

	/**
	 * StatusForm constructor.
	 *
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function __construct(ModelInterface $model){
		$this->_model = $model;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'tnt_status_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['type'] = [
			'#type'          => 'textfield',
			'#title'         => t('Type'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_model->type ?? ''
		];

		$form['content'] = [
			'#type'          => 'textarea',
			'#title'         => t('Content'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#cols'          => 5,
			'#default_value' => $this->_model->content ?? ''
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published?'),
			'#default_value' => $this->_model->published ?? TRUE
		];

		$form['actions'] = [
			'#type' => 'actions'
		];

		$form['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Save'),
			'#attributes' => ['class' => ['button button--primary']],
		];

		$form['actions']['cancel'] = [
			'#type'       => 'link',
			'#title'      => t('Cancel'),
			'#attributes' => ['class' => ['button']],
			'#url'        => Url::fromRoute('singpost.toolbox.track_and_trace.status'),
		];

		if (!$this->_model->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.toolbox.track_and_trace.status.delete',
					['id' => $this->_model->id]),
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_model->load([
			'type'      => $form_state->getValue('type'),
			'content'   => $form_state->getValue('content'),
			'published' => $form_state->getValue('published'),
		]);

		if ($this->_model->is_new){
			$message = t('Successfully created new Status.');
		}else{
			$message = t('Successfully updated Status');
		}

		$redirect = Url::fromRoute('singpost.toolbox.track_and_trace.status');

		if ($this->_model->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save Status.'));
			$form_state->setRebuild();
		}
	}
}