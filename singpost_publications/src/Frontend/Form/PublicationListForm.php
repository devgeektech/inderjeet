<?php


namespace Drupal\singpost_publications\Frontend\Form;


use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_publications\Repositories\PublicationRepository;

/**
 * Class PublicationListForm
 *
 * @package Drupal\singpost_publications\Frontend\Form
 */
class PublicationListForm implements FormInterface{

	/**
	 * @var \Drupal\singpost_publications\Repositories\PublicationRepository
	 */
	protected $_publication;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * PublicationListForm constructor.
	 *
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication_repository
	 * @param array $filters
	 */
	public function __construct(PublicationRepository $publication_repository, array $filters){
		$this->_publication = $publication_repository;
		$this->_filters     = $filters;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publications_list_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(9);
		$sort_value = $this->_filters['sort_args'];
		/* $header = [
			['data' => t('Date'), 'field' => 'title', 'sort' => 'asc'],
		]; */

		$header = [
			['data' => t('Date'), 'field' => 'published_at', 'sort' => $sort_value],
		];

		$pager = $this->_publication->applyFilters($this->_filters)
		                                 ->getTablePaginatedData($header, $limit);
		$publications = $this->_publication->getTableData($pager);

		$list = [];
		if (!empty($publications)){
			foreach ($publications as $key => $publication){
				/* if($publication->content){
					$list_file = Json::decode($publication->content);
					$content   = [];

					if (!empty($list_file)){
						foreach ($list_file as $index => $value){
							$content[$key][$index]['name'] = $value['label'];

							if (!empty($value['file'])){
								$file = File::load($value['file']);
								$path = $file->createFileUrl();

								$content[$key][$index]['url'] = $path;
							}else{
								$content[$key][$index]['url'] = '#';
							}
						}
					}
				} */
				if($publication->slug == 'empty'){
					$slug = $publication->id;
				}
				else{
					$slug = $publication->slug;
				}

				$list[$key] = [ 
					'id'             => $publication->id,
					'title'          => $publication->title,
					'slug'           => $slug,
					'summary'        => $publication->summary,
					'thumbnail'      => $publication->image_thumbnail ? (File::load($publication->image_thumbnail)
					                                                         ->createFileUrl()) : '#',
					'annual'         => $publication->annual_report ? (File::load($publication->annual_report)
					                                                       ->createFileUrl()) : '',
					'sustainability' => $publication->sustainability_report ? (File::load($publication->sustainability_report)
					                                                               ->createFileUrl()) : '',
					//'content'        => $content[$key]
				];
			}
		}
		$form['table'] = [
			'#theme'        => 'singpost_publications',
			'#publications' => $list,
			'#cache'        => ['max-cache' => 0]
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
	public function validateForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}