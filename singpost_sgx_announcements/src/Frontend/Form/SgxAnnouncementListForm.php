<?php


namespace Drupal\singpost_sgx_announcements\Frontend\Form;


use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\file\Entity\File;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;

/**
 * Class SgxAnnouncementListForm
 *
 * @package Drupal\singpost_sgx_announcements\Frontend\Form
 */
class SgxAnnouncementListForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository
	 */
	protected $_sgx_announcement;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * SgxAnnouncementListForm constructor.
	 *
	 * @param \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository $sgx_announcement
	 * @param array $filters
	 */
	public function __construct(
		SgxAnnouncementRepository $sgx_announcement,
		array $filters){
		$this->_sgx_announcement = $sgx_announcement;
		$this->_filters          = $filters;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_list_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(10);
		$sort_value = $this->_filters['sort_args'];
		/* $header = [
			['data' => t('Date'), 'field' => 'title', 'sort' => 'asc'],
		]; */

		$header = [
			['data' => t('Date'), 'field' => 'date', 'sort' => $sort_value],
		];

		$pager = $this->_sgx_announcement->applyFilters($this->_filters)
		                                 ->getTablePaginatedData($header, $limit);

		$sgx_announcements = $this->_sgx_announcement->getTableData($pager);

		$list = [];
		if (!empty($sgx_announcements)){
			foreach ($sgx_announcements as $index => $value){
				$list[date('Y', $value->date)][$index] = [
					'id'    => $value->id,
					'title' => $value->title,
					'date'  => date('d M Y', $value->date)
				];

				if (!empty($value->file)){
					$file = File::load($value->file);
					$path = $file->createFileUrl();

					$list[date('Y', $value->date)][$index] += [
						'url_img' => $path
					];
				}else{
					$list[date('Y', $value->date)][$index] += [
						'url_img' => '#'
					];
				}
			}
		}
		krsort($list);
		
		$form['table'] = [
			'#theme'             => 'singpost_sgx_announcement',
			'#sgx_announcements' => $list,
			'#cache'             => ['max-age' => 0],
		];

		$form['pager'] = [
			'#type' => 'pager',
		];

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}