<?php


namespace Drupal\singpost_packing_material\Form\Product;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\singpost_packing_material\Repositories\ProductRepository;

/**
 * Class ProductForm
 *
 * @package Drupal\singpost_packing_material\Form\Product
 */
class ProductForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\ProductRepository
	 */
	protected $_product;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_model;

	/**
	 * ProductForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $category
	 * @param \Drupal\singpost_packing_material\Repositories\ProductRepository $product
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function __construct(
		CategoryRepository $category,
		ProductRepository $product,
		ModelInterface $model){
		$this->_category = $category;
		$this->_product  = $product;
		$this->_model    = $model;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_product_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_model->title ?? ''
		];

		$form['category_id'] = [
			'#type'          => 'select',
			'#title'         => t('Category'),
			'#required'      => TRUE,
			'#empty_option'  => t('Select category'),
			'#options'       => $this->_category->getCategories(),
			'#default_value' => $this->_model->category_id ?? ''
		];

		$form['dimension'] = [
			'#type'          => 'textfield',
			'#title'         => t('Dimension'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->dimension ?? ''
		];

		$form['estimated_weight'] = [
			'#type'          => 'textfield',
			'#title'         => t('Estimated Weight'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->estimated_weight ?? ''
		];

		$form['unit'] = [
			'#type'          => 'textfield',
			'#title'         => t('Unit'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->unit ?? ''
		];

		$form['price'] = [
			'#type'          => 'textfield',
			'#title'         => t('Price'),
			'#required'      => TRUE,
			'#default_value' => $this->_model->price ?? ''
		];

		$form['discounted_price'] = [
			'#type'          => 'textfield',
			'#title'         => t('Discounted Price'),
			'#required'      => TRUE,
			'#default_value' => $this->_model->discounted_price ?? ''
		];

		$form['product_img'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Product Image'),
			'#upload_validators' => [
				'file_validate_extensions' => ['png jpg jpeg'],
			],
			'#upload_location'   => 'public://upload/packing-material/product',
			'#required'          => TRUE,
			'#description'       => t('Allowed types: @types',
				['@types' => 'png jpg jpeg']),
			'#default_value'     => ($this->_model && $this->_model->product_img) ? [$this->_model->product_img] : ''
		];

		$form['bundle'] = [
			'#type'          => 'textfield',
			'#title'         => t('Bundle'),
			'#default_value' => $this->_model->bundle ?? ''
		];

		$form['description_bundle'] = [
			'#type'          => 'textfield',
			'#title'         => t('Description Bundle'),
			'#maxlength'     => 255,
			'#default_value' => $this->_model->description_bundle ?? ''
		];

		$form['tooltip_text'] = [
			'#type'          => 'text_format',
			'#title'         => t('Tooltip Text'),
			'#required'      => TRUE,
			'#default_value' => $this->_model->tooltip_text['value'] ?? '',
			'#format'        => $this->_model->tooltip_text['format'] ?? 'basic_html'
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
			'#url'        => Url::fromRoute('singpost.pm.product.manage'),
		];

		if ((!$this->_model->is_new) && (!$this->_product->hasOrderDetail($this->_model->id))){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.pm.product.delete',
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
		$price            = $form_state->getValue('price');
		$discounted_price = $form_state->getValue('discounted_price');
		$bundle           = $form_state->getValue('bundle');

		if (!is_numeric($price)){
			$form_state->setErrorByName('price',
				t('Only numbers and the decimal separator (.) allowed in Price.'));
		}

		if (!is_numeric($discounted_price)){
			$form_state->setErrorByName('discounted_price',
				t('Only numbers and the decimal separator (.) allowed in Discounted Price.'));
		}

		if (!is_numeric($bundle)){
			$form_state->setErrorByName('bundle', t('Only numbers are allowed in Bundle.'));
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_model->load([
			'category_id'        => $form_state->getValue('category_id'),
			'title'              => $form_state->getValue('title'),
			'dimension'          => $form_state->getValue('dimension'),
			'estimated_weight'   => $form_state->getValue('estimated_weight'),
			'unit'               => $form_state->getValue('unit'),
			'price'              => $form_state->getValue('price'),
			'discounted_price'   => $form_state->getValue('discounted_price'),
			'tooltip_text'       => $form_state->getValue('tooltip_text'),
			'product_img'        => $form_state->getValue('product_img'),
			'bundle'             => $form_state->getValue('bundle'),
			'description_bundle' => $form_state->getValue('description_bundle'),
			'published'          => $form_state->getValue('published'),
		]);

		if ($this->_model->is_new){
			$message = t('Successfully created new Product.');
		}else{
			$message = t('Successfully updated Product');
		}

		$redirect = Url::fromRoute('singpost.pm.product.manage');

		if ($this->_model->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save product.'));
			$form_state->setRebuild();
		}
	}
}