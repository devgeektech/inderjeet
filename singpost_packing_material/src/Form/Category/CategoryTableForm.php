<?php


namespace Drupal\singpost_packing_material\Form\Category;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\user\Entity\User;

/**
 * Class CategoryTableForm
 *
 * @package Drupal\singpost_packing_material\Form\Category
 */
class CategoryTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * CategoryTableForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $repository
	 * @param array $filters
	 */
	public function __construct(CategoryRepository $repository, array $filters){
		$this->_category = $repository;
		$this->_filters  = $filters;
	}

	/**
	 * @return string|void
	 */
	public function getFormId(){
		return 'pm_category_table_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('ID'), 'field' => 'id'],
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Weight'), 'field' => 'weight', 'sort' => 'asc'],
			['data' => t('Published'), 'field' => 'published'],
			['data' => t('Created at'), 'field' => 'created_at'],
			['data' => t('Created by'), 'field' => 'created_by'],
			'actions' => t('Operations'),
		];

		$pager = $this->_category->applyFilters($this->_filters)
		                         ->getTablePaginatedData($header, $limit);

		$categories = $this->_category->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.pm.category.manage',
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

		if (!empty($categories)){
			foreach ($categories as $item){

				$user = User::load($item->created_by);

				$form['table'][$item->id] = [
					'#attributes' => ['class' => ['draggable']],
					'id'          => [
						'#plain_text' => $item->id
					],
					'title'       => [
						'#plain_text' => $item->title
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
						'#action'  => Url::fromRoute('singpost.pm.category.status')
					],
					'created_at'  => [
						'#plain_text' => Drupal::service('date.formatter')
						                       ->format($item->created_at)],
					'create_by'   => [
						'#plain_text' => $user->getAccountName()
					],
					'actions'     => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.pm.category.edit',
									['id' => $item->id])
							]
						],
					]
				];

				if (!$this->_category->existsProduct($item->id)){
					$form['table'][$item->id]['actions']['#links'] += [
						'delete' => [
							'title' => t('Delete'),
							'url'   => Url::fromRoute('singpost.pm.category.delete', [
								'id' => $item->id
							]),
						],
					];
				}
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No category found.');
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
			$this->_category->getModel()::findOrFail($id)->updateAttributes(['weight' => $weight]);
		}

		Drupal::messenger()->addStatus(t('Category have been updated.'));
		$form_state->setRedirect('singpost.pm.category.manage');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}