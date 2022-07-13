<?php


namespace Drupal\singpost_packing_material\Form\Category;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_packing_material\Model\PackingMaterialCategory;

/**
 * Class CategoryDeleteForm
 *
 * @package Drupal\singpost_packing_material\Form\Category
 */
class CategoryDeleteForm extends ConfirmFormBase{

	/**
	 * @var \Drupal\singpost_packing_material\Model\PackingMaterialCategory
	 */
	protected $_category;

	/**
	 * CategoryDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Model\PackingMaterialCategory $category
	 */
	public function __construct(PackingMaterialCategory $category){
		$this->_category = $category;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_category_delete_form';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return $this->t('Delete Category');

	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('<p>Are you sure you want to delete category: "<i><strong>@title?</strong></i>"</p><p>This action cannot be undone.</p>',
			[
				'@title' => $this->_category->title
			]);
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.pm.category.manage');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$title = $this->_category->title;
		$id    = $this->_category->id;

		if ($this->_category->existsProduct($id)){
			$this->messenger()
			     ->addError($this->t('Cannot delete this category because category has products.'));
		}else{
			if ($this->_category->delete()){
				$this->messenger()
				     ->addMessage($this->t('Category "<i>@title</i>" has been successfully deleted.',
					     [
						     '@title' => $title
					     ]));
			}else{
				$this->messenger()
				     ->addError($this->t('Something went wrong. Cannot delete this category.'));
			}
		}

		$form_state->setRedirect('singpost.pm.category.manage');
	}
}