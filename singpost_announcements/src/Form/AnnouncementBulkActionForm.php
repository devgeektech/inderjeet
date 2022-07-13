<?php

namespace Drupal\singpost_announcements\Form;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\Support\ArrayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class AnnouncementBulkActionForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class AnnouncementBulkActionForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * The action name.
	 *
	 * @var string
	 */
	private $_action;

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected $_session;

	/**
	 * The records on which the action to be performed.
	 *
	 * @var array
	 */
	private $_records;

	/**
	 * @var \Drupal\singpost_announcements\Repositories\AnnouncementRepository
	 */
	private $_service;

	/**
	 * AnnouncementBulkActionForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 * @param \Drupal\singpost_announcements\Repositories\AnnouncementRepository $service
	 */
	public function __construct(Session $session, AnnouncementRepository $service){
		$this->_session = $session;
		$this->_service = $service;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('session'),
			$container->get('singpost.announcement.service'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'announcement_bulk_action';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion(){
		return t('Are you sure you want to %action selected announcement(s)?',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getPageTitle(){
		return t('%action Announcements',
			['%action' => $this->_action]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl(){
		return new Url('singpost.announcements.admin');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $action = NULL){
		$this->_action = $action;
		$announcements = $this->_session->get('bulk_selected_announcements');

		if (empty($announcements)){
			$form_state->setRedirect('singpost.announcements.admin');
		}

		$this->_records = $this->_service->getBulkActionAnnouncementsList($announcements);

		$form['announcements'] = [
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
			'title'            => t('@action selected announcement(s)',
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
		$this->_session->remove('bulk_selected_announcements');
		$form_state->setRedirect('singpost.announcements.admin');
	}

	/**
	 * Batch operation callback.
	 *
	 * @param \Drupal\singpost_announcements\Model\Announcement[] $records
	 * @param $action
	 * @param $context
	 */
	public static function performBatchAction($records, $action, &$context){
		foreach ($records as $announcement){
			$result = FALSE;

			switch ($action){
				case 'delete':
					$result = $announcement->delete();
					break;
				case 'publish':
					$result = $announcement->updateAttributes(['published' => 1]);
					break;
				case 'unpublish':
					$result = $announcement->updateAttributes(['published' => 0]);
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
			$message = t('@action @count announcement(s).', [
				'@action' => $results['action'],
				'@count'  => $results['processed']
			]);

			Drupal::messenger()->addMessage($message);
		}else{
			$message = t('Finished with error. Please check logs for more details.');
			Drupal::messenger()->addError($message, 'error');
		}
	}
}