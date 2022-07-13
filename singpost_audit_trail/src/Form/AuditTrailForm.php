<?php

namespace Drupal\singpost_audit_trail\Form;

use Drupal;
use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\singpost_audit_trail\Repositories\AuditTrailRepository;
use Drupal\singpost_base\ModelInterface;

/**
 * Class AuditTrailForm
 *
 * @package Drupal\singpost_audit_trail\Form
 */
class AuditTrailForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var AuditTrailRepository
	 */
	protected $_service;

	/**
	 * @var ModelInterface
	 */
	protected $_audit_trail;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * AuditTrailForm constructor.
	 *
	 * @param AuditTrailRepository $service
	 * @param ModelInterface $audit_trail
	 * @param DateFormatter $dateformat
	 */
	public function __construct(
		AuditTrailRepository $service,
		ModelInterface $audit_trail,
		DateFormatter $dateformat){
		$this->_service     = $service;
		$this->_audit_trail = $audit_trail;
		$this->_dateformat  = $dateformat;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'audit_trail_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$rows = [
			[
				['data' => t('Type'), 'header' => TRUE],
				Html::escape($this->_audit_trail->type),
			],
			[
				['data' => t('Date'), 'header' => TRUE],
				Drupal::service('date.formatter')->format($this->_audit_trail->created_at, 'long'),
			],
			[
				['data' => t('Action'), 'header' => TRUE],
				Html::escape($this->_audit_trail->action),
			],
			[
				['data' => t('Link'), 'header' => TRUE],
				Html::escape($this->_audit_trail->link),
			],
			[
				['data' => t('Request'), 'header' => TRUE],
				Html::decodeEntities($this->_audit_trail->request),
			],
			[
				['data' => t('Response'), 'header' => TRUE],
				Html::decodeEntities($this->_audit_trail->response),
			],
			[
				['data' => t('Responded'), 'header' => TRUE],
				Html::decodeEntities(floor($this->_audit_trail->responded_at - $this->_audit_trail->requested_at) * 1000) . 'ms',
			],
			[
				['data' => t('Created By'), 'header' => TRUE],
				Html::escape($this->_audit_trail->getAuthor()),
			],
		];

		$build['audit_table'] = [
			'#theme'      => 'table',
			'#rows'       => $rows,
			'#attributes' => ['class' => ['dblog-event']],
		];

		return $build;
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}