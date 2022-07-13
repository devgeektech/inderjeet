<?php


namespace Drupal\singpost_publications\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_publications\Model\Publication;

/**
 * Class PublicationDeleteForm
 *
 * @package Drupal\singpost_publications\Form
 */
class PublicationDeleteForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_publications\Model\Publication
	 */
	protected $_publication;

	/**
	 * PublicationDeleteForm constructor.
	 *
	 * @param \Drupal\singpost_publications\Model\Publication $publication
	 */
	public function __construct(Publication $publication){
		$this->_publication = $publication;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publication_delete_form';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return $this->t('Delete Publication');
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.publication.admin');
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getDescription(){
		return $this->t('Are you sure you want to delete Publication: "<i>@title?</i>"', [
			'@title' => $this->_publication->title
		]);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$publication = $this->_publication;

		if ($this->_publication->delete()){
			$this->messenger()
			     ->addMessage($this->t('Publication "<i>@title</i>" has been successfully deleted.',
				     [
					     '@title' => $publication->title
				     ]));
		}else{
			$this->messenger()
			     ->addError($this->t('Something went wrong. Cannot delete this publication.'));
		}

		$form_state->setRedirect('singpost.publication.admin');
	}
}