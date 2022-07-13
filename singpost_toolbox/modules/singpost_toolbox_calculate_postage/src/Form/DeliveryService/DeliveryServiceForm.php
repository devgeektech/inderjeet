<?php


namespace Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_base\Support\EntityUrlFieldHelper;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository;

/**
 * Class DeliveryServiceForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService
 */
class DeliveryServiceForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_model;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository
	 */
	protected $_service;

	/**
	 * DeliveryServiceForm constructor.
	 *
	 * @param \Drupal\singpost_base\ModelInterface $model
	 * @param \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository $repository
	 */
	public function __construct(ModelInterface $model, DeliveryServiceRepository $repository){
		$this->_model   = $model;
		$this->_service = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'delivery_service_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$compensations = $this->_service->getListCompensation();

		$form['delivery_service_name'] = [
			'#type'          => 'textfield',
			'#title'         => t('Delivery Service Name'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_model->delivery_service_name ?? ''
		];

		$form['display_name'] = [
			'#type'          => 'textfield',
			'#title'         => t('Display Name'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->display_name ?? ''
		];

		$form['url'] = [
			'#type'                  => 'entity_autocomplete',
			'#target_type'           => 'node',
			'#attributes'            => ['data-autocomplete-first-character-blacklist' => '/#?'],
			'#process_default_value' => FALSE,
			'#title'                 => t('URL'),
			'#description'           => t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %add-node or an external URL such as %url. Enter %front to link to the front page.',
				[
					'%front'    => '<front>',
					'%add-node' => '/node/add',
					'%url'      => 'http://example.com'
				]),
			'#default_value'         => ($this->_model->url) ? EntityUrlFieldHelper::getUriAsDisplayableString($this->_model->url) : '',
			'#element_validate'      => ['Drupal\singpost_base\Support\EntityUrlFieldHelper::validateUriElement'],
		];

		$form['service_image'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Upload Service Image'),
			'#upload_validators' => [
				'file_validate_extensions' => ['png jpg jpeg'],
			],
			'#upload_location'   => 'public://upload/calculate_postage_api/service',
			'#required'          => TRUE,
			'#description'       => t('Allowed types: @types',
				['@types' => 'png jpg jpeg']),
			'#default_value'     => $this->_model->service_image ? [$this->_model->service_image] : '',
		];

		$form['maximum_dimension'] = [
			'#type'          => 'textfield',
			'#title'         => t('Maximum Dimension'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->maximum_dimension ?? ''
		];

		$form['recommended'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Recommended?'),
			'#default_value' => $this->_model->recommended ?? FALSE
		];

		$form['compensation'] = [
			'#type'          => 'radios',
			'#title'         => t('Compensation'),
			'#options'       => $compensations,
			'#default_value' => $this->_model->compensation ?? 0
		];

		$form['is_tracked'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Tracked Package?'),
			'#default_value' => $this->_model->is_tracked ?? 0
		];

		$form['disabled'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Disabled?'),
			'#default_value' => $this->_model->disabled ?? FALSE
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
			'#url'        => Url::fromRoute('singpost.toolbox.calculate.delivery_service.manage'),
		];

		if (!$this->_model->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.toolbox.calculate.delivery_service.delete',
					['id' => $this->_model->id]),
			];
		}
		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		$ds_name = $form_state->getValue('delivery_service_name');

		if (!$ds_name){
			$form_state->setErrorByName('delivery_service_name',
				t('Please enter Delivery Service Name.'));
		}else{
			if (strcmp($this->_model->delivery_service_name,
					$ds_name) !== 0 && $this->_service->checkExistDeliveryServiceName($ds_name)){
				$form_state->setErrorByName('delivery_service_name',
					t("Delivery service name <strong>@name</strong> already exists.",
						['@name' => $ds_name]));
			}
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		if (!empty($form_state->getValue('url'))){
			$url = EntityUrlFieldHelper::getUserEnteredStringAsUri($form_state->getValue('url'));
		}

		$this->_model->load([
			'delivery_service_name' => $form_state->getValue('delivery_service_name'),
			'display_name'          => $form_state->getValue('display_name'),
			'url'                   => $url ?? '',
			'service_image'         => $form_state->getValue('service_image'),
			'maximum_dimension'     => $form_state->getValue('maximum_dimension'),
			'recommended'           => $form_state->getValue('recommended'),
			'compensation'          => $form_state->getValue('compensation'),
			'is_tracked'            => $form_state->getValue('is_tracked'),
			'disabled'              => $form_state->getValue('disabled'),
			'published'             => $form_state->getValue('published'),
		]);

		//print_r($this->_model);

		if ($this->_model->is_new){
			$message = t('Successfully created new Delivery Service.');
		}else{
			$message = t('Successfully updated Delivery Service');
		}

		$redirect = Url::fromRoute('singpost.toolbox.calculate.delivery_service.manage');

		if ($this->_model->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save Delivery Service.'));
			$form_state->setRebuild();
		}
	}
}