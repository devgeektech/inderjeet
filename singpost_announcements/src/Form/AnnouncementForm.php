<?php

namespace Drupal\singpost_announcements\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\ModelInterface;

/**
 * Class AnnouncementForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class AnnouncementForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var AnnouncementRepository
	 */
	protected $_service;

	/**
	 * @var ModelInterface
	 */
	protected $_announcement;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * AnnouncementForm constructor.
	 *
	 * @param AnnouncementRepository $service
	 * @param \Drupal\singpost_base\ModelInterface $announcement
	 * @param \Drupal\Core\Datetime\DateFormatter $dateformat
	 */
	public function __construct(
		AnnouncementRepository $service,
		ModelInterface $announcement,
		DateFormatter $dateformat){
		$this->_service      = $service;
		$this->_announcement = $announcement;
		$this->_dateformat   = $dateformat;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'announcement_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_announcement->title ?? ''
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published?'),
			'#default_value' => $this->_announcement->published ?? TRUE
		];

		$form['start_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('Start date'),
			'#attributes'    => [
				'class'    => ['calendar start-date'],
				'readonly' => TRUE
			],
			'#placeholder'   => t('dd-mm-yyyy G:i'),
			'#default_value' => $this->_announcement->is_new ? $this->_dateformat->format(time(),
				'custom',
				'd-m-Y G:i') : $this->_dateformat->format($this->_announcement->start_date,
				'custom', 'd-m-Y G:i'),
			'#required'      => TRUE
		];

		$form['end_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('End date'),
			'#attributes'    => [
				'class'    => ['calendar end-date'],
				'readonly' => TRUE
			],
			'#placeholder'   => t('dd-mm-yyyy G:i'),
			'#default_value' => $this->_announcement->is_new ? $this->_dateformat->format(time(),
				'custom', 'd-m-Y G:i') : $this->_dateformat->format($this->_announcement->end_date,
				'custom', 'd-m-Y G:i'),
			'#required'      => TRUE
		];

		$form['summary'] = [
			'#type'          => 'textarea',
			'#title'         => t('Summary'),
			'#description'   => t('Leave blank to use trimmed value of body as the summary'),
			'#placeholder'   => t('Max length of 255 characters'),
			'#maxlength'     => 255,
			'#default_value' => $this->_announcement->summary ?? ''
		];

		$form['content'] = [
			'#type'          => 'text_format',
			'#title'         => t('Content'),
			'#default_value' => $this->_announcement->content['value'] ?? '',
			'#format'        => $this->_announcement->content['format'] ?? 'basic_html',
		];
		$form['fileelect'] = array (
			'#title'         => t('Upload PDF file'),
			'#type'          => 'file',
		);
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
			'#url'        => Url::fromRoute('singpost.announcements.admin'),
		];

		if (!$this->_announcement->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.announcements.delete',
					['id' => $this->_announcement->id]),
			];
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (strtotime($form_state->getValue('end_date')) < strtotime($form_state->getValue('start_date'))){
			$form_state->setErrorByName('end_date',
				t('Start Date must be less than End Date'));
		}
		$new_file = file_save_upload('fileelect', array (
			'file_validate_extensions'    => array ('pdf doc docx'),
			'custom_validate_size' => array (1024 * 1024 * 30), // You should define validation function for file size, in case file is too big.
		));
		print_r($new_file);
		die($new_file);
		if($newfile) {
			$some_location = '/web/upload';
			$des = $some_location . '-' . time() . $newfile->filename; // Define the new location and add the time stamp to file name.

			$result = file_copy($newfile, $des, FILE_EXISTS_REPLACE);
			if ($result) {
				echo "File Upload";
			}
			else {
				echo "File Uplaod Fail";
			}

		}
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();
		$this->_announcement->load([
			'title'      => $form_state->getValue('title'),
			'start_date' => strtotime($form_state->getValue('start_date')),
			'end_date'   => strtotime($form_state->getValue('end_date')),
			'summary'    => $form_state->getValue('summary'),
			'content'    => $form_state->getValue('content'),
			'published'  => $form_state->getValue('published'),
		]);

		if ($this->_announcement->is_new){
			$message  = t('Successfully created new Announcement.');
			$redirect = Url::fromRoute('singpost.announcements.admin');
		}else{
			$message  = t('Successfully updated Announcement');
			$redirect = Url::fromRoute('singpost.announcements.edit',
				['id' => $this->_announcement->id]);
		}

		if ($this->_announcement->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()->addError(t('Something went wrong. Cannot save announcement.'));
			$form_state->setRebuild();
		}
	}
}