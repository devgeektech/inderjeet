<?php

namespace Drupal\singpost_audit_trail\Model;

use Drupal\Component\Serialization\Json;
use Drupal\singpost_base\Model;
use Drupal\user\Entity\User;

/**
 * Class AuditTrail
 *
 * @package Drupal\singpost_audit_trail\Model
 *
 * @property int $id
 * @property string $action
 * @property string $type
 * @property string $link
 * @property string $request
 * @property string $response
 * @property int $created_at
 * @property int $created_by
 * @property double $requested_at
 * @property double $responded_at
 */
class AuditTrail extends Model{

	const TYPE_SUCCESS = 'success';

	const TYPE_FAILED = 'failed';

	protected $_attributes = [
		'action', 'type', 'link', 'request', 'response', 'created_at', 'created_by', 'requested_at', 'responded_at'
	];

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return 'audit_trail';
	}

	/**
	 * @inheritDoc
	 */
	public function beforeSave(){
		if (is_array($this->request)){
			$this->request = Json::encode($this->request);
		}

		if (is_array($this->response)){
			$this->response = Json::encode($this->response);
		}

		parent::beforeSave();
	}

	/**
	 * @return string
	 */
	public function getAuthor(){
		return $this->created_by ? User::load($this->created_by)->getAccountName() : t('SYSTEM');
	}
}