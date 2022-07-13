<?php


namespace Drupal\singpost_packing_material\Form\Product;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\singpost_packing_material\Repositories\ProductRepository;
use Drupal\user\Entity\User;

/**
 * Class ProductTableForm
 *
 * @package Drupal\singpost_packing_material\Form\Product
 */
class ProductTableForm implements FormInterface{

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
	 * @var array
	 */
	protected $_filters;

	/**
	 * ProductTableForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Repositories\ProductRepository $product
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $category
	 * @param array $filters
	 */
	public function __construct(
		ProductRepository $product,
		CategoryRepository $category,
		array $filters){
		$this->_product  = $product;
		$this->_category = $category;
		$this->_filters  = $filters;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_product_table_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('ID'), 'field' => 'id'],
			['data' => t('Category'), 'field' => 'category_id'],
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Dimension'), 'field' => 'dimension'],
			['data' => t('Unit'), 'field' => 'unit'],
			['data' => t('Weight'), 'field' => 'weight', 'sort' => 'asc'],
			['data' => t('Published'), 'field' => 'published'],
			['data' => t('Created at'), 'field' => 'created_at'],
			['data' => t('Created by'), 'field' => 'created_by'],
			'actions' => t('Operations'),
		];

		$pager = $this->_product->applyFilters($this->_filters)
		                        ->getTablePaginatedData($header, $limit);

		$products = $this->_product->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.pm.product.manage',
			$limit);

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#tabledrag'  => [
				[
					'action'       => 'order',
					'relationship' => 'sibling',
					'group'        => 'order-weight',
				]
			],
			'#attributes' => [
				'id' => $this->getFormId()
			]
		];

		$form['actions'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form-actions js-form-wrapper form-wrapper']]
		];

		$form['actions']['save'] = [
			'#type'       => 'submit',
			'#value'      => 'Save',
			'#attributes' => ['class' => ['button']]
		];

		if (!empty($products)){
			foreach ($products as $item){
				$user = User::load($item->created_by);

				$form['table'][$item->id] = [
					'#attributes' => ['class' => ['draggable']],
					'id'          => [
						'#plain_text' => $item->id
					],
					'category_id' => [
						'#plain_text' => $this->_category->getNameCategoryById($item->category_id)
					],
					'title'       => [
						'#plain_text' => $item->title
					],
					'dimension'   => [
						'#plain_text' => $item->dimension
					],
					'unit'        => [
						'#plain_text' => $item->unit
					],
					'#weight'     => $item->weight,
					'weight'      => [
						'#type'          => 'weight',
						'#title'         => t('Weight for @title', ['@name' => $item->title]),
						'#title_display' => 'invisible',
						'#default_value' => $item->weight,
						'#attributes'    => ['class' => ['order-weight']]
					],
					'published'   => [
						'#theme'   => 'toggle_button',
						'#nid'     => $item->id,
						'#checked' => $item->published,
						'#action'  => Url::fromRoute('singpost.pm.product.status')
					],
					'created_at'  => [
						'#plain_text' => Drupal::service('date.formatter')
						                       ->format($item->created_at, 'dd/mm/yyyy H:i')],
					'create_by'   => [
						'#plain_text' => $user->getAccountName()
					],
					'actions'     => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.pm.product.edit',
									['id' => $item->id])
							]
						],
					]
				];

				if (!$this->_product->hasOrderDetail($item->id)){
					$form['table'][$item->id]['actions']['#links'] += [
						'delete' => [
							'title' => t('Delete'),
							'url'   => Url::fromRoute('singpost.pm.product.delete', [
								'id' => $item->id
							]),
						],
					];
				}
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No product found.');
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected_ids = $form_state->getValue('table');
		$new_arr      = [];
		foreach ($selected_ids as $id => $weight){
			if (is_numeric($id)){
				$new_arr += [$id => $weight['weight']];
			}
		}

		$selected_ids = array_filter($new_arr);

		foreach ($selected_ids as $id => $weight){
			$this->_product->getModel()::findOrFail($id)->updateAttributes(['weight' => $weight]);
		}

		Drupal::messenger()->addStatus(t('Weight have been updated.'));
		$form_state->setRedirect('singpost.pm.product.manage');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}