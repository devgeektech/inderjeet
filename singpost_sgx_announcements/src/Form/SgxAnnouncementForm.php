<?php


namespace Drupal\singpost_sgx_announcements\Form;


use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;

/**
 * Class SgxAnnouncementForm
 *
 * @package Drupal\singpost_sgx_announcements\Form
 */
class SgxAnnouncementForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var SgxAnnouncementRepository
	 */
	protected $_service;

	/**
	 * @var ModelInterface
	 */
	protected $_sgx_announcement;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_date_format;

	/**
	 * AnnouncementForm constructor.
	 *
	 * @param SgxAnnouncementRepository $service
	 * @param \Drupal\singpost_base\ModelInterface $sgx_announcement
	 * @param \Drupal\Core\Datetime\DateFormatter $date_format
	 */
	public function __construct(
		SgxAnnouncementRepository $service,
		ModelInterface $sgx_announcement,
		DateFormatter $date_format){
		$this->_service          = $service;
		$this->_sgx_announcement = $sgx_announcement;
		$this->_date_format      = $date_format;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_form';
	}

	/**
	 * Form constructor.
	 *
	 * @param array $form
	 *   An associative array containing the structure of the form.
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *   The current state of the form.
	 *
	 * @return array
	 *   The form structure.
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_sgx_announcement->title ?? ''
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published'),
			'#default_value' => $this->_sgx_announcement->published ?? TRUE
		];

		$form['date'] = [
			'#type'          => 'textfield',
			'#title'         => t('Date'),
			'#attributes'    => [
				'class' => ['calendar']
			],
			'#placeholder'   => t('dd-mm-yyyy G:i'),
			'#default_value' => $this->_sgx_announcement->is_new ? $this->_date_format->format(time(),
				'custom',
				'd-m-Y G:i') : $this->_date_format->format($this->_sgx_announcement->date,
				'custom', 'd-m-Y G:i'),
			'#required'      => TRUE
		];

		$form['file'] = [
			'#type'              => 'managed_file',
			'#title'             => 'File',
			'#upload_validators' => [
				'file_validate_extensions' => ['pdf xlsx docx'],
			],
			'#upload_location'   => 'public://upload/sgx-announcement/',
			'#description'       => t('Allowed types: @types', ['@types' => 'pdf xlsx docx']),
			'#default_value'     => $this->_sgx_announcement->file ? [$this->_sgx_announcement->file] : '',
			'#preview'           => TRUE
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
			'#url'        => Url::fromRoute('singpost.sgx.announcements.admin'),
		];

		if (!$this->_sgx_announcement->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.sgx.announcements.delete',
					['id' => $this->_sgx_announcement->id]),
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_sgx_announcement->load([
			'title'     => $form_state->getValue('title'),
			'date'      => strtotime($form_state->getValue('date')),
			'file'      => $form_state->getValue('file'),
			'published' => $form_state->getValue('published'),
		]);

		if ($this->_sgx_announcement->is_new){
			$message = t('Successfully created new SGX Announcement.');
		}else{
			$message = t('Successfully updated SGX Announcement');
		}

		$redirect = Url::fromRoute('singpost.sgx.announcements.admin');

		if ($this->_sgx_announcement->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save SGX announcement.'));
			$form_state->setRebuild();
		}
	}
}