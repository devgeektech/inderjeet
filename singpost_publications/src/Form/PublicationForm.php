<?php


namespace Drupal\singpost_publications\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Drupal\Component\Utility\Html;

/**
 * Class PublicationForm
 *
 * @package Drupal\singpost_publications\Form
 */
class PublicationForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_publications\Repositories\PublicationRepository
	 */
	protected $_service;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_publication;

	/**
	 * PublicationForm constructor.
	 *
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $service
	 * @param \Drupal\singpost_base\ModelInterface $publication
	 */
	public function __construct(PublicationRepository $service, ModelInterface $publication){
		$this->_service     = $service;
		$this->_publication = $publication;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publication_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_publication->title ?? ''
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published'),
			'#default_value' => $this->_publication->published ?? TRUE
		];

		$form['published_at'] = [
			'#type'          => 'textfield',
			'#title'         => t('Published At'),
			'#attributes'    => [
				'class'    => ['calendar year-only'],
				'readonly' => TRUE
			],
			'#default_value' => $this->_publication->published_at ?? '',
			'#required'      => TRUE
		];

		$form['summary'] = [
			'#type'          => 'textarea',
			'#title'         => t('Summary'),
			'#default_value' => $this->_publication->summary ?? ''
		];

		$form['image_thumbnail'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Image thumbnail'),
			'#upload_validators' => [
				'file_validate_extensions' => ['gif png jpeg jpg'],
				'file_validate_size' => array(1024 * 1024 * 5)
			],
			'#upload_location'   => 'public://upload/publications/',
			'#description'       => t('Allowed types: @types',
				['@types' => 'gif png jpeg jpg']),
			'#default_value'     => ($this->_publication && $this->_publication->image_thumbnail) ?
				[$this->_publication->image_thumbnail] : ''
		];

		$form['micro_site_cta_title'] = array(
			'#type'          => 'textfield',
			'#title'         => t('Link text'),
			'#default_value' => $this->_publication->micro_site_cta_title ?? '',
			'#maxlength' => 255
		);

		$form['micro_site_cta_url'] = array(
			'#type'          => 'textfield',
			'#title'         => t('URL'),
			'#default_value' => $this->_publication->micro_site_cta_url ?? '',
			'#maxlength' => 255,
		);

		$values = array(
			'internal' => t('Internal'),
			'external' => t('External')
		);
		$form['micro_link_type'] = array(
			'#title' => t('Target'),
			'#type' => 'select',
			'#default_value' => $this->_publication->micro_link_type ?? '',
			'#options' => $values,
		);

		$form['annual_report'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Annual Report'),
			'#upload_validators' => [
				'file_validate_extensions' => ['txt pdf docx xlsx'],
				'file_validate_size' => array(1024 * 1024 * 15)
			],
			'#upload_location'   => 'public://upload/publications/',
			'#description'       => t('Allowed types: @types',
				['@types' => 'txt pdf docx xlsx']),
			'#default_value'     => ($this->_publication && $this->_publication->annual_report) ?
				[$this->_publication->annual_report] : ''
		];

		$form['sustainability_report'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Sustainability Report'),
			'#upload_validators' => [
				'file_validate_extensions' => ['txt pdf docx xlsx'],
				'file_validate_size' => array(1024 * 1024 * 15)
			],
			'#upload_location'   => 'public://upload/publications/',
			'#description'       => t('Allowed types: @types',
				['@types' => 'txt pdf docx xlsx']),
			'#default_value'     => ($this->_publication && $this->_publication->sustainability_report) ?
				[$this->_publication->sustainability_report] : ''
		];

		$form['heading'] = [
			'#type'  => 'textfield',
			'#title' => t('Title'),
			'#default_value' => $this->_publication->heading ?? ''
		];
		$form['sub_heading'] = [
			'#type'  => 'textfield',
			'#title' => t('Sub Title'),
			'#default_value' => $this->_publication->sub_heading ?? ''
		];

		$form['content'] = [
			'#type'   => 'fieldset',
			'#title'  => 'List of link',
			'#prefix' => '<div id="content-row-wrapper">',
			'#suffix' => '</div>',
			'#tree'   => TRUE
		];

		if ($this->_publication && !empty($this->_publication->content)){
			$count   = count($this->_publication->content);
			$content = range(1, $count);
		}

		if (empty($form_state->get('field_deltas'))){
			$form_state->set('field_deltas', (!empty($content)) ? $content : [1]);
		}

		$field_count = $form_state->get('field_deltas');

		foreach ($field_count as $key => $value){
			$form['content'][$value] = [
				'#type'  => 'details',
				'#title' => t('Link @num', ['@num' => $key + 1]),
				'#open'  => FALSE,
				'#tree'  => TRUE
			];
			$form['content'][$value]['label'] = [
				'#type'          => 'textfield',
				'#title'         => t('Label'),
				'#title_display' => 'invisible',
				'#placeholder'   => t('Label'),
				'#default_value' => $this->_publication->content[$key]['label'] ?? ''
			];

			$form['content'][$value]['listsummary'] = [
				'#type'          => 'textarea',
				'#title'         => t('Summary'),
				'#title_display' => 'invisible',
				'#default_value' => $this->_publication->content[$key]['listsummary'] ?? ''
			];

			$form['content'][$value]['file'] = [
				'#type'              => 'managed_file',
				'#upload_validators' => [
					'file_validate_extensions' => ['txt pdf xlsx docx'],
				],
				'#upload_location'   => 'public://upload/publications/',
				'#description'       => t('Allowed types: @types',
					['@types' => 'txt pdf xlsx docx']),
				'#default_value'     => ($this->_publication && $this->_publication->content[$key]['file']) ?
					[$this->_publication->content[$key]['file']] : ''
			];

			if ($value > 1){
				$form['content'][$value]['actions'] = ['#type' => 'actions'];

				$form['content'][$value]['actions']['remove_row'] = [
					'#type'                    => 'submit',
					'#value'                   => t('Remove'),
					'#submit'                  => ['::addMoreRemove'],
					'#limit_validation_errors' => [],
					'#ajax'                    => [
						'callback' => '::moreCallback',
						'wrapper'  => 'content-row-wrapper',
					],
					'#name'                    => 'remove_row_' . $value,
				];
			}
		}

		$form['content']['actions'] = ['#type' => 'actions'];

		$form['content']['actions']['add_row'] = [
			'#type'                    => 'submit',
			'#value'                   => t('Add more'),
			'#submit'                  => ['::addMore'],
			'#limit_validation_errors' => [],
			'#ajax'                    => [
				'callback' => '::moreCallback',
				'wrapper'  => 'content-row-wrapper',
			],
		];

		$form['actions'] = ['#type' => 'actions'];

		$form['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Save'),
			'#attributes' => ['class' => ['button button--primary']],
		];

		$form['actions']['cancel'] = [
			'#type'       => 'link',
			'#title'      => t('Cancel'),
			'#attributes' => ['class' => ['button']],
			'#url'        => Url::fromRoute('singpost.publication.admin'),
		];

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function addMore(array &$form, FormStateInterface $form_state){
		$field_deltas_array = $form_state->get('field_deltas');

		if (count($field_deltas_array) > 0){
			$field_deltas_array[] = max($field_deltas_array) + 1;
		}else{
			$field_deltas_array[] = 0;
		}

		$form_state->set('field_deltas', $field_deltas_array);
		$form_state->setRebuild();
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function addMoreRemove(array &$form, FormStateInterface $form_state){
		$delta_remove       = $form_state->getTriggeringElement()['#parents'][1];
		$field_deltas_array = $form_state->get('field_deltas');
		$key_to_remove      = array_search($delta_remove, $field_deltas_array);

		unset($field_deltas_array[$key_to_remove]);
		$form_state->set('field_deltas', $field_deltas_array);
		$form_state->setRebuild();
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return mixed
	 */
	public function moreCallback(array &$form, FormStateInterface $form_state){
		return $form['content'];
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		// Genrate Slug from Title
		$slugify = Html::getClass($form_state->getValue('title'));
		// End Genrate Slug from Title
		//print_r($slugify);
		$this->_publication->load([ 
			'title'                 => $form_state->getValue('title'),
			'summary'               => $form_state->getValue('summary'),
			'image_thumbnail'       => $form_state->getValue('image_thumbnail'),
			'micro_site_cta_title'  => $form_state->getValue('micro_site_cta_title'),
			'micro_site_cta_url' 	=> $form_state->getValue('micro_site_cta_url'),
			'micro_link_type' 		=> $form_state->getValue('micro_link_type'),
			'annual_report'         => $form_state->getValue('annual_report'),
			'sustainability_report' => $form_state->getValue('sustainability_report'),
			'sub_heading' 			=> $form_state->getValue('sub_heading'),
			'heading' 				=> $form_state->getValue('heading'),
			'content'               => $form_state->getValue('content'),
			'published_at'          => $form_state->getValue('published_at'),
			'published'             => $form_state->getValue('published'),
			'slug'             		=> $slugify
		]);

		if ($this->_publication->is_new){
			$message = t('Successfully created new Publication.');
		}else{
			$message = t('Successfully updated Publication');
		}

		$redirect = Url::fromRoute('singpost.publication.admin');

		if ($this->_publication->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save publication.'));
			$form_state->setRebuild();
		}
	}
}