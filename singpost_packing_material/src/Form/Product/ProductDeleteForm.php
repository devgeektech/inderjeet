<?php


namespace Drupal\singpost_packing_material\Form\Product;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_packing_material\Model\PackingMaterialProduct;

/**
 * Class ProductDeleteForm
 *
 * @package Drupal\singpost_packing_material\Form\Product
 */
class ProductDeleteForm extends ConfirmFormBase{

	/**
	 * @var \Drupal\singpost_packing_material\Model\PackingMaterialProduct
	 */
	protected $_model;

	/**
	 * ProductDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Model\PackingMaterialProduct $product
	 */
	public function __construct(PackingMaterialProduct $product){
		$this->_model = $product;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_product_delete_form';
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.pm.product.manage');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return t('Delete Product');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('<p>Are you sure you want to delete product: "<i><strong>@title?</strong></i>"</p><p>This action cannot be undone.</p>',
			[
				'@title' => $this->_model->title
			]);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$title = $this->_model->title;
		$id    = $this->_model->id;

		if ($this->_model->hasOrderDetail($id)){
			$this->messenger()
			     ->addError($this->t('Cannot delete this product because its has order detail.'));
		}else{
			if ($this->_model->delete()){
				$this->messenger()
				     ->addMessage($this->t('Product "<i>@title</i>" has been successfully deleted.',
					     [
						     '@title' => $title
					     ]));
			}else{
				$this->messenger()
				     ->addError($this->t('Something went wrong. Cannot delete this product.'));
			}
		}

		$form_state->setRedirect('singpost.pm.product.manage');
	}
}