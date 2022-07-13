<?php


namespace Drupal\singpost_sgx_announcements\Form;


use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\ArrayHelper;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SgxAnnouncementBulkActionForm
 *
 * @package Drupal\singpost_sgx_announcements\Form
 */
class SgxAnnouncementBulkActionForm extends ConfirmFormBase{

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected $_session;
	/**
	 * The action name.
	 *
	 * @var string
	 */
	private $_action;
	/**
	 * The records on which the action to be performed.
	 *
	 * @var array
	 */
	private $_records;

	/**
	 * @var \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository
	 */
	private $_service;

	/**
	 * SgxAnnouncementBulkActionForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 * @param \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository $service
	 */
	public function __construct(Session $session, SgxAnnouncementRepository $service){
		$this->_session = $session;
		$this->_service = $service;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('session'),
			$container->get('singpost.sgx.announcement.service'));
	}

	/**
	 * Batch operation callback.
	 *
	 * @param \Drupal\singpost_announcements\Model\Announcement[] $records
	 * @param $action
	 * @param $context
	 */
	public static function performBatchAction($records, $action, &$context){
		foreach ($records as $record){
			$result = FALSE;

			switch ($action){
				case 'delete':
					$result = $record->delete();

					break;
				case 'publish':
					$result = $record->updateAttributes(['published' => 1]);
					break;
				case 'unpublish':
					$result = $record->updateAttributes(['published' => 0]);
					break;
				default:
					break;
			}

			if ($result === TRUE){
				$context['results']['action'] = ucfirst($action);

				if (isset($context['results']['processed'])){
					$context['results']['processed'] ++;
				}else{
					$context['results']['processed'] = 1;
				}
			}
		}
	}

	/**
	 * Finish callback for batch process.
	 *
	 * @param $success
	 * @param $results
	 */
	public static function onFinishBatchCallback($success, $results){
		if ($success){
			$message = t('@action @count SGX announcement(s).', [
				'@action' => $results['action'],
				'@count'  => $results['processed']
			]);

			Drupal::messenger()->addMessage($message);
		}else{
			$message = t('Finished with error. Please check logs for more details.');
			Drupal::messenger()->addError($message, 'error');
		}
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_bulk_action';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return t('Are you sure you want to %action selected SGX announcement(s)?',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getPageTitle(){
		return t('%action SGX Announcements',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.sgx.announcements.admin');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 * @param null $action
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $action = NULL){
		$this->_action     = $action;
		$sgx_announcements = $this->_session->get('bulk_selected_sgx_announcements');

		if (empty($sgx_announcements)){
			$form_state->setRedirect('singpost.sgx.announcements.admin');
		}

		$this->_records = $this->_service->getBulkActionSgxAnnouncementsList($sgx_announcements);

		$form['sgx_announcements'] = [
			'#theme' => 'item_list',
			'#items' => ArrayHelper::getColumn($this->_records, 'title')
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$batch = [
			'title'            => t('@action selected SGX announcement(s)',
				['@action' => ucfirst($this->_action)]),
			'operations'       => [
				[
					[get_class($this), 'performBatchAction'],
					[$this->_records, $this->_action],
				],
			],
			'finished'         => [get_class($this), 'onFinishBatchCallback'],
			'progress_message' => NULL,
		];

		batch_set($batch);
		$this->_session->remove('bulk_selected_sgx_announcements');
		$form_state->setRedirect('singpost.sgx.announcements.admin');
	}
}