<?php

namespace Drupal\singpost_toolbox_locate_us\Form;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\ArrayHelper;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class LocateUsTypeBulkActionForm
 *
 * @package Drupal\singpost_toolbox_locate_us\Form
 */
class LocateUsTypeBulkActionForm extends ConfirmFormBase{

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
	 * @var \Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository
	 */
	private $_service;

	/**
	 * AnnouncementBulkActionForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 * @param \Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository $service
	 */
	public function __construct(Session $session, LocateUsRepository $service){
		$this->_session = $session;
		$this->_service = $service;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('session'),
			$container->get('singpost.toolbox.locate_us.service'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'locate_us_type_bulk_action';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion(){
		return t('Are you sure you want to %action selected locate us type(s)?',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getPageTitle(){
		return t('%action Locate Us Types',
			['%action' => $this->_action]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl(){
		return new Url('singpost.toolbox.locate_us.admin.type');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(
		array $form,
		FormStateInterface $form_state,
		$action = NULL){
		$this->_action   = $action;
		$locate_us_types = $this->_session->get('bulk_selected_locate_us_types');

		if (empty($locate_us_types)){
			$form_state->setRedirect('singpost.toolbox.locate_us.admin.type');
		}

		$this->_records = $this->_service->getBulkActionLocateUsList($locate_us_types);

		$form['locate_us_type'] = [
			'#theme' => 'item_list',
			'#items' => ArrayHelper::getColumn($this->_records, 'title'),
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$batch = [
			'title'            => t('@action selected locate us type(s)',
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
		$this->_session->remove('bulk_selected_locate_us_types');
		$form_state->setRedirect('singpost.toolbox.locate_us.admin.type');
	}

	/**
	 * Batch operation callback.
	 *
	 * @param \Drupal\singpost_toolbox_locate_us\Model\LocateUsType[] $records
	 * @param $action
	 * @param $context
	 */
	public static function performBatchAction($records, $action, &$context){
		foreach ($records as $locate_us_type){
			$result = FALSE;

			switch ($action){
				case 'delete':
					$result = $locate_us_type->delete();
					break;
				case 'publish':
					$result = $locate_us_type->updateAttributes(['status' => 1]);
					break;
				case 'unpublish':
					$result = $locate_us_type->updateAttributes(['status' => 0]);
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
			$message = t('@action @count locate us type(s).', [
				'@action' => $results['action'],
				'@count'  => $results['processed'],
			]);

			Drupal::messenger()->addMessage($message);
		}else{
			$message = t('Finished with error. Please check logs for more details.');
			Drupal::messenger()->addError($message, 'error');
		}
	}

}