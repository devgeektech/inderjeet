<?php


namespace Drupal\singpost_publications\Form;


use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\ArrayHelper;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class PublicationBulkActionForm
 *
 * @package Drupal\singpost_publications\Form
 */
class PublicationBulkActionForm extends ConfirmFormBase{

	use MessengerTrait;

	/**
	 * @var string
	 */
	protected $_action;

	/**
	 * @var array
	 */
	protected $_records;

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected $_session;

	/**
	 * @var \Drupal\singpost_publications\Repositories\PublicationRepository
	 */
	protected $_service;

	/**
	 * PublicationBulkActionForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication
	 */
	public function __construct(Session $session, PublicationRepository $publication){
		$this->_session = $session;
		$this->_service = $publication;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Form\ConfirmFormBase|\Drupal\singpost_publications\Form\PublicationBulkActionForm
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('session'),
			$container->get('singpost.publication.service'));
	}

	/**
	 * @param \Drupal\singpost_publications\Model\Publication[] $records
	 * @param $action
	 * @param $context
	 */
	public static function performBatchAction($records, $action, &$context){
		foreach ($records as $item){
			$result = FALSE;

			switch ($action){
				case 'delete':
					$result = $item->delete();
					break;
				case 'publish':
					$result = $item->updateAttributes(['published' => 1]);
					break;
				case 'unpublish':
					$result = $item->updateAttributes(['published' => 0]);
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
	 * @param $success
	 * @param $results
	 */
	public static function onFinishBatchCallback($success, $results){
		if ($success){
			$message = t('@action @count publication(s).', [
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
		return 'publication_bulk_action_form';
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getQuestion(){
		return t('Are you sure you want to %action selected publication(s)?',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\StringTranslation\TranslatableMarkup
	 */
	public function getPageTitle(){
		return t('%action Publications',
			['%action' => $this->_action]);
	}

	/**
	 * @return \Drupal\Core\Url
	 */
	public function getCancelUrl(){
		return new Url('singpost.publication.admin');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 * @param null $action
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $action = NULL){
		$this->_action = $action;
		$publications  = $this->_session->get('bulk_selected_publications');

		if (empty($publications)){
			$form_state->setRedirect('singpost.publication.admin');
		}

		$this->_records = $this->_service->getBulkActionpublicationList($publications);

		$form['publications'] = [
			'#theme' => 'item_list',
			'#items' => ArrayHelper::getColumn($this->_records, 'title')
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$batch = [
			'title'            => t('@action selected publication(s)',
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
		$this->_session->remove('bulk_selected_publications');
		$form_state->setRedirect('singpost.publication.admin');
	}
}