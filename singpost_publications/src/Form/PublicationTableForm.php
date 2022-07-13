<?php


namespace Drupal\singpost_publications\Form;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_publications\Repositories\PublicationRepository;

/**
 * Class PublicationTableForm
 *
 * @package Drupal\singpost_publications\Form
 */
class PublicationTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var PublicationRepository
	 */
	protected $_publication;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * PublicationTableForm constructor.
	 *
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication
	 * @param array $filters
	 */
	public function __construct(PublicationRepository $publication, array $filters){
		$this->_publication = $publication;
		$this->_filters     = $filters;
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
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Year'), 'field' => 'published_at', 'sort' => 'desc'],
			['data' => t('Published'), 'field' => 'published'],
			'actions' => t('Operations')
		];

		$pager = $this->_publication->applyFilters($this->_filters)
		                            ->getTablePaginatedData($header, $limit);

		$publications = $this->_publication->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['bulk_action'] = [
			'#type'    => 'select',
			'#title'   => t('Action'),
			'#options' => [
				'delete'    => t('Delete'),
				'publish'   => t('Publish'),
				'unpublish' => t('Unpublish')
			],
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.publication.admin',
			$limit);

		$form['buttons'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form-actions js-form-wrapper form-wrapper']]
		];

		$form['buttons']['apply'] = [
			'#type'       => 'submit',
			'#value'      => 'Apply Action',
			'#attributes' => ['class' => ['button']]
		];

		$form['table'] = [
			'#type'        => 'table',
			'#header'      => $header,
			'#tableselect' => TRUE,
			'#attributes'  => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($publications)){
			foreach ($publications as $item){
				$form['table'][$item->id] = [
					'id'           => [
						'#plain_text' => $item->id
					],
					'title'        => [
						'#plain_text' => $item->title
					],
					'published_at' => [
						'#plain_text' => $item->published_at,
					],
					'published'    => [
						'#theme'   => 'toggle_button',
						'#nid'     => $item->id,
						'#checked' => $item->published,
						'#action'  => Url::fromRoute('singpost.publication.status')
					],
					'actions'      => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.publication.edit', [
									'id' => $item->id
								])
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.publication.delete', [
									'id' => $item->id
								]),
							],
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No publication found.');
		}

		return $form;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publication_table_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected = array_filter($form_state->getValue('table'));

		Drupal::request()->getSession()->set('bulk_selected_publications', $selected);
		$form_state->setRedirect('singpost.publication.action',
			['action' => $form_state->getValue('bulk_action')]);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!$form_state->getValue('bulk_action')){
			$form_state->setErrorByName('bulk_action', t('Please select an action'));
		}

		if (!array_filter($form_state->getValue('table'))){
			$form_state->setErrorByName('table', t('Please select at least one item'));
		}
	}
}