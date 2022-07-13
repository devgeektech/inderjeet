<?php


namespace Drupal\singpost_publications\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PublicationSearchForm
 *
 * @package Drupal\singpost_publications\Form
 */
class PublicationSearchForm extends BaseSearchForm{

	/**
	 * @var PublicationRepository
	 */
	protected $_publication;

	/**
	 * PublicationSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication
	 */
	public function __construct(Request $request, PublicationRepository $publication){
		parent::__construct($request);
		$this->_publication = $publication;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publication_search_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	public function searchFilters($values = []){
		$filters['title'] = [
			'#type'          => 'search',
			'#title'         => t('Title'),
			'#placeholder'   => t('Search by title'),
			'#default_value' => !empty($values['title']) ? $values['title'] : '',
			'#size'          => 30,
			'field'          => 'title',
			'condition'      => 'LIKE',
		];

		$filters['published_at'] = [
			'#type'          => 'textfield',
			'#title'         => t('Published At'),
			'#attributes'    => [
				'class'    => ['calendar year-only'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($values['published_at']) ? $values['published_at'] : '',
			'#size'          => 30,
			'field'          => 'published_at',
			'condition'      => '='
		];

		$filters['status'] = [
			'#type'          => 'select',
			'#title'         => t('Status'),
			'#attributes'    => [
				'class' => ['select2']
			],
			'#default_value' => $values['status'] ?? '',
			'#options'       => [
				'' => t('All'),
				1  => t('Published'),
				0  => t('Unpublished')
			],
			'field'          => 'published',
			'condition'      => '='
		];

		return $filters;
	}
}