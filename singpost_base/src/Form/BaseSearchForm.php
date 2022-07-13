<?php

namespace Drupal\singpost_base\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseSearchForm
 *
 * @package Drupal\singpost_base\Form
 */
abstract class BaseSearchForm implements FormInterface{

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null
	 */
	protected $_session;

	/**
	 * BaseSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function __construct(Request $request){
		$this->_session = $request->getSession();
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$default_values = $this->getSearchValues();
		$filters        = $this->searchFilters($default_values);

		if (!empty($filters)){
			foreach ($filters as $field => &$filter){
				unset($filter['field']);
				unset($filter['condition']);
				$form['filters'][$field] = $filter;
			}
		}

		$form['filters']['actions'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => ['form-actions js-form-wrapper form-wrapper']
			]
		];

		$form['filters']['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Search'),
			'#attributes' => [
				'class' => ['button btn--primary'],
			],
		];

		if ($this->getSearchValues()){
			$form['filters']['actions']['reset'] = [
				'#type'       => 'submit',
				'#value'      => t('Reset'),
				'#attributes' => [
					'class' => ['button']
				],
			];
		}

		return $form;
	}

	/**
	 * Get search values from the session
	 *
	 * @return array|mixed
	 */
	public function getSearchValues(){
		return $this->_session->get($this->getFormId()) ?? [];
	}

	/**
	 * @inheritDoc
	 */
	abstract public function getFormId();

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	abstract public function searchFilters($values = []);

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$action = $form_state->getValue('op')->getUntranslatedString();
		$this->_clearSearchValues();

		if ($action !== 'Reset'){
			$values  = $form_state->getValues();
			$filters = $this->_processFormValues($values);
			$this->_session->set($this->getFormId(), $filters);
		}
	}

	/**
	 * @return void
	 */
	private function _clearSearchValues(){
		$this->_session->remove($this->getFormId());
	}

	/**
	 * Process form data before storing to session
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	protected function _processFormValues(array $values){
		$filters = [];
		foreach ($values as $field => $value){
			if ($value !== NULL && $value !== ''){
				$filters[$field] = $value;
			}
		}

		return $filters;
	}

	/**
	 * Get filter array argument
	 *
	 * @return array
	 */
	public function getFilterQuery(){
		$where = $args = [];
		$sort_args = '';

		return $this->parseQuery($where, $args, $sort_args);
	}

	/**
	 * Create filter arguments
	 *
	 * @param $where
	 * @param $args
	 *
	 * @return array
	 */
	public function parseQuery(&$where, &$args, &$sort_args){
		$values  = $this->getSearchValues();
		
		$filters = $this->searchFilters($values);

		foreach ($values as $key => $filter){
			if ($filter === NULL || $filter === '' || empty($filters[$key])){
				continue;
			}
			if (is_array($filter)){
				$filter_where = [];
				$filter       = array_map('trim', $filter);

				foreach ($filter as $value){
					$params         = $this->_createParams($filters, $key, $value);
					$filter_where[] = $params['where'];
					$args[]         = $params['args'];
				}

				if (!empty($filter_where)){
					$where[] = '(' . implode(' OR ', $filter_where) . ')';
				}
			}else{
				$value  = trim($filter);
				//print_r($key);
				if($key != 'sorting'){
					$params = $this->_createParams($filters, $key, $value);
					$where[] = $params['where'];
					$args[]  = $params['args'];
				}
				else{
					$sort_args  = $value;
				}
			}
		}
		$where = array_filter($where);
		$where = !empty($where) ? implode(' AND ', $where) : '';
		$args = array_filter($args);
		return [
			'where' => $where,
			'args'  => $args,
			'sort_args' => $sort_args,
		];
	}

	/**
	 * @param array $filters
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	private function _createParams(array $filters, $key, $value){
		if($key != 'sorting'){
			if ($filters[$key]['condition'] == 'LIKE'){
				$args = '%' . $value . '%';
			}
			else{
				$args = $value;
			}
			return [
				'where' => $filters[$key]['field'] . " " . $filters[$key]['condition'] . " ?",
				'args'  => $args
			];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}