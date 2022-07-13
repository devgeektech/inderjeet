<?php


namespace Drupal\singpost_packing_material\Form\Order;


use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_packing_material\Model\PackingMaterialProduct;

/**
 * Class OrderViewForm
 *
 * @package Drupal\singpost_packing_material\Form\Order
 */
class OrderViewForm extends FormBase{

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_model;

	/**
	 * OrderViewForm constructor.
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
		return 'pm_order_view_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['id'] = [
			'#type'   => 'item',
			'#title'  => t('Order ID'),
			'#markup' => $this->_model->id ? t('#') . sprintf("%06d", $this->_model->id) : ''
		];

		$form['name'] = [
			'#type'   => 'item',
			'#title'  => t('Name'),
			'#markup' => $this->_model->name ?? ''
		];

		$form['block_number'] = [
			'#type'   => 'item',
			'#title'  => t('Block Number'),
			'#markup' => $this->_model->block_number ?? ''
		];

		$form['street_address'] = [
			'#type'   => 'item',
			'#title'  => t('Street Address'),
			'#markup' => $this->_model->street_name ?? ''
		];

		$form['unit_number'] = [
			'#type'   => 'item',
			'#title'  => t('Unit Number'),
			'#markup' => $this->_model->unit_number ?? ''
		];

		$form['postal_code'] = [
			'#type'   => 'item',
			'#title'  => t('Postal Code'),
			'#markup' => $this->_model->postal_code ?? ''
		];

		$form['company_name'] = [
			'#type'   => 'item',
			'#title'  => t('Company Name'),
			'#markup' => $this->_model->company_name ?? ''
		];

		$form['email'] = [
			'#type'   => 'item',
			'#title'  => t('Email'),
			'#markup' => $this->_model->email ?? ''
		];

		$form['contact_number'] = [
			'#type'   => 'item',
			'#title'  => t('Contact Number'),
			'#markup' => $this->_model->contact_number ?? ''
		];

		$form['subtotal'] = [
			'#type'   => 'item',
			'#title'  => t('Subtotal'),
			'#markup' => t('S$') . $this->_model->subtotal ?? '0'
		];

		$form['discount'] = [
			'#type'   => 'item',
			'#title'  => t('Discount'),
			'#markup' => t('S$') . $this->_model->discount ?? '0'
		];

		$form['total'] = [
			'#type'   => 'item',
			'#title'  => t('Total'),
			'#markup' => t('S$') . $this->_model->total ?? '0'
		];

		$form['order_date'] = [
			'#type'   => 'item',
			'#title'  => t('Order Date'),
			'#markup' => Drupal::service('date.formatter')
			                   ->format($this->_model->order_date, 'custom', 'Y-m-d h:m') ?? ''
		];

		$header = [
			['data' => t('Product'), 'field' => 'product_id'],
			['data' => t('Price'), 'field' => 'price'],
			['data' => t('Quantity'), 'field' => 'quantity'],
		];

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#attributes' => [
				'id' => $this->getFormId() . ' detail'
			]
		];

		$details = $this->_model->getOrderDetail($this->_model->id);
		$product = new PackingMaterialProduct();

		if (isset($details) && !empty($details)){
			foreach ($details as $key => $value){
				$form['table'][$key]['product_id'] = [
					'#plain_text' => $product->getProductName($value->product_id) ?? ''
				];
				$form['table'][$key]['price']      = [
					'#plain_text' => t('S$') . $value->price ?? '0'
				];
				$form['table'][$key]['quantity']   = [
					'#plain_text' => $value->quantity ?? ''
				];
			}
		}

		$form['actions'] = [
			'#type' => 'actions'
		];

		$form['actions']['back'] = [
			'#type'  => 'link',
			'#title' => $this->t('Back Listing'),
			'#url'   => Url::fromRoute('singpost.pm.order.manage'),
		];

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}