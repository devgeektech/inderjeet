<?php


namespace Drupal\singpost_packing_material\Form\Category;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;

/**
 * Class CategoryForm
 *
 * @package Drupal\singpost_packing_material\Form\Category
 */
class CategoryForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_service;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_category;

	/**
	 * CategoryForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $repository
	 * @param \Drupal\singpost_base\ModelInterface $category
	 */
	public function __construct(CategoryRepository $repository, ModelInterface $category){
		$this->_service  = $repository;
		$this->_category = $category;
	}

	/**
	 * @return string|void
	 */
	public function getFormId(){
		return 'pm_category_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_category->title ?? ''
		];

		$form['feature_img'] = [
			'#type'              => 'managed_file',
			'#title'             => t('Featured Image'),
			'#upload_validators' => [
				'file_validate_extensions' => ['png jpg jpeg'],
			],
			'#upload_location'   => 'public://upload/packing-material/category',
			'#required'          => TRUE,
			'#description'       => t('Allowed types: @types',
				['@types' => 'png jpg jpeg']),
			'#default_value'     => ($this->_category && $this->_category->feature_img) ? [$this->_category->feature_img] : ''
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published?'),
			'#default_value' => $this->_category->published ?? TRUE
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
			'#url'        => Url::fromRoute('singpost.pm.category.manage'),
		];

		if ((!$this->_category->is_new) && (!$this->_service->existsProduct($this->_category->id))){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.pm.category.delete',
					['id' => $this->_category->id]),
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_category->load([
			'title'       => $form_state->getValue('title'),
			'feature_img' => $form_state->getValue('feature_img'),
			'published'   => $form_state->getValue('published'),
		]);

		if ($this->_category->is_new){
			$message = t('Successfully created new Category.');
		}else{
			$message = t('Successfully updated Category');
		}

		$redirect = Url::fromRoute('singpost.pm.category.manage');

		if ($this->_category->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save category.'));
			$form_state->setRebuild();
		}
	}
}