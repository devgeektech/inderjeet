<?php


namespace Drupal\singpost_toolbox_track_and_trace\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_toolbox_track_and_trace\Form\Status\StatusDeleteForm;
use Drupal\singpost_toolbox_track_and_trace\Form\Status\StatusForm;
use Drupal\singpost_toolbox_track_and_trace\Form\Status\StatusTableForm;
use Drupal\singpost_toolbox_track_and_trace\Model\Status;
use Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StatusController
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Controller
 */
class StatusController extends ControllerBase{

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * @var \Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository
	 */
	protected $_repository;

	/**
	 * CategoryController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository $repository
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		StatusRepository $repository){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_repository   = $repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.tnt.status.service'));
	}

	/**
	 * @return array
	 */
	public function index(){
		$table_instance   = new StatusTableForm($this->_repository);
		$content['table'] = $this->_form_builder->getForm($table_instance);

		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * @return mixed
	 */
	public function add(){
		$model = new Status();
		$form  = new StatusForm($model);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function edit(int $id){
		$model         = Status::findOrFail($id);
		$form          = new StatusForm($model);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function changeStatus(Request $request){
		$id     = $request->get('id');
		$status = $request->get('active', 1);

		$model = Status::findOrFail($id);

		return new JsonResponse($model->updateAttributes(['published' => $status]));
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	public function delete(int $id){
		$model         = Status::findOrFail($id);
		$form          = new StatusDeleteForm($model);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}
}