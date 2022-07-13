<?php


namespace Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\EntityUrlFieldHelper;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository;
use Drupal\user\Entity\User;

/**
 * Class DeliveryServiceTableForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService
 */
class DeliveryServiceTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository
	 */
	protected $_repository;

	/**
	 * DeliveryServiceTableForm constructor.
	 *
	 * @param array $filters
	 * @param \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository $repository
	 */
	public function __construct(array $filters, DeliveryServiceRepository $repository){
		$this->_filters    = $filters;
		$this->_repository = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'delivery_service_table_form';
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
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('ID'), 'field' => 'id', 'sort' => 'asc'],
			['data' => t('Delivery Service Name'), 'field' => 'delivery_service_name'],
			['data' => t('Display Name'), 'field' => 'display_name'],
			['data' => t('Link to page'), 'field' => 'url'],
			['data' => t('Published'), 'field' => 'published'],
			['data' => t('Created at'), 'field' => 'created_at'],
			['data' => t('Created by'), 'field' => 'created_by'],
			'actions' => t('Operations'),
		];

		$pager = $this->_repository->applyFilters($this->_filters)
		                           ->getTablePaginatedData($header, $limit);

		$data = $this->_repository->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.toolbox.calculate.delivery_service.manage',
			$limit);

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#attributes' => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($data)){
			foreach ($data as $item){
				$user = User::load($item->created_by);

				$form['table'][$item->id] = [
					'id'                    => [
						'#plain_text' => $item->id
					],
					'delivery_service_name' => [
						'#plain_text' => $item->delivery_service_name
					],
					'display_name'          => [
						'#plain_text' => $item->display_name
					],
					'url'                   => [
						'#plain_text' => ($item->url) ? EntityUrlFieldHelper::getUriAsDisplayableString($item->url) : ''
					],
					'published'             => [
						'#theme'   => 'toggle_button',
						'#nid'     => $item->id,
						'#checked' => $item->published,
						'#action'  => Url::fromRoute('singpost.toolbox.calculate.delivery_service.change_status')
					],
					'created_at'            => [
						'#plain_text' => Drupal::service('date.formatter')
						                       ->format($item->created_at, 'custom', 'd/M/Y H:i')
					],
					'create_by'             => [
						'#plain_text' => $user->getAccountName()
					],
					'actions'               => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.toolbox.calculate.delivery_service.edit',
									[
										'id' => $item->id
									])
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.toolbox.calculate.delivery_service.delete',
									[
										'id' => $item->id
									])
							]
						]
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No delivery service found.');
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}