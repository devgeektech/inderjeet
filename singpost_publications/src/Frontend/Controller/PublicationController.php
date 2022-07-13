<?php

namespace Drupal\singpost_publications\Frontend\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;
use Drupal\singpost_publications\Model\Publication;
use Drupal\singpost_publications\Frontend\Form\PublicationFilterForm;
use Drupal\singpost_publications\Frontend\Form\PublicationListForm;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\file\Entity\File;

/**
 * Class PublicationController
 *
 * @package Drupal\singpost_publications\Frontend\Controller
 */
class PublicationController extends ControllerBase{

	/**
	 * @var \Drupal\singpost_publications\Repositories\PublicationRepository
	 */
	protected $_service;

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * PublicationController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication_repository
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		PublicationRepository $publication_repository){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $publication_repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.publication.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		$filter            = new PublicationFilterForm($this->_request, $this->_service);
		$content['filter'] = $this->_form_builder->getForm($filter);
		$table_instance    = new PublicationListForm($this->_service, $filter->getFilterQuery());
		$content['table']  = $this->_form_builder->getForm($table_instance);

		return $content;
	}


	/**
	 * Publication view page -> Redirect to index page
	 *
	 * @param int $singpost_publication
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function view(int $singpost_publication){
		return new RedirectResponse(Url::fromRoute('singpost.publication.index')->toString());
	}

	public function detail_publication($slug){
		$publication = new Publication();
		$result = $publication->singlepublication($slug);
		$list = [];
		if (!empty($result)){
			foreach ($result as $key => $publication){
				if($publication->content){
					$list_file = Json::decode($publication->content);
					$content   = [];

					if (!empty($list_file)){
						foreach ($list_file as $index => $value){
							$content[$key][$index]['name'] = $value['label'];
							if (!empty($value['listsummary'])){
								$content[$key][$index]['list_summary'] = $value['listsummary'];
							}
							else{
								$content[$key][$index]['list_summary'] = '';
							}
							if (!empty($value['file'])){
								$file = File::load($value['file']);
								$path = $file->createFileUrl();

								$content[$key][$index]['url'] = $path;
							}else{
								$content[$key][$index]['url'] = '#';
							}
						}
					}
				}
				
				$list[$key] = [ 
					'id'             => $publication->id,
					'title'          => $publication->title,
					'summary'        => $publication->summary,
					'sub_heading'    => $publication->sub_heading,
					'heading'        => $publication->heading,
					'thumbnail'      => $publication->image_thumbnail ? (File::load($publication->image_thumbnail)
																			 ->createFileUrl()) : '#',
					'annual'         => $publication->annual_report ? (File::load($publication->annual_report)
																		   ->createFileUrl()) : '',
					'sustainability' => $publication->sustainability_report ? (File::load($publication->sustainability_report)
																				   ->createFileUrl()) : '',
					'content'        => $content[$key],
					'micro_site_title'	=> $publication->micro_site_cta_title ? $publication->micro_site_cta_title : 'empty',
					'micro_site_url'	=> $publication->micro_site_cta_url ? $publication->micro_site_cta_url : 'empty',
					'micro_link_type'	=> $publication->micro_link_type ? $publication->micro_link_type : ''
				];
			}
		}
		/* echo "<pre>";
		print_r($list);
		echo "</pre>"; */
		return [
			  '#theme' 		=> 'singpost_publication_single',
			  '#results' 	=> $list,
			  '#cache'      => ['max-cache' => 0]
		];
	}

	public function getTitle() {
		$path = \Drupal::request()->getpathInfo();
		$arg  = explode('/',$path);
		$node_id = $arg[4];
		if($node_id){
			$publication = new Publication();
			$result = $publication->singlepublication($node_id);
			if (!empty($result)){
				foreach ($result as $key => $publication){
					$post_title = $publication->title;
				}
			}
		}
		else{
			$post_title = 'Detail Publication Page';
		}
		return $this->t($post_title);
	 }

}