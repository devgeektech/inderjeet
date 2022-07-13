<?php


namespace Drupal\singpost_toolbox\Plugin\Block;


use Drupal;
use Drupal\Component\Serialization\PhpSerialize;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Controller\CalculateController;
use Drupal\singpost_toolbox_find_postal_code\Controller\FindPostalCodeController;
use Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend\RedirectRedeliverSideForm;
use PDO;

/**
 * Provides a 'Toolbox Side' Block.
 *
 * @Block(
 *   id = "toolbox_side_block",
 *   admin_label = @Translation("Toolbox side block"),
 *   category = @Translation("SingPost Toolbox"),
 * )
 */
class ToolboxSideBlock extends BlockBase{

	/**
	 * @return array
	 */
	public function build(){
		return [
			'#theme'    => 'singpost_toolbox_side',
			'#forms'    => $this->_getMenuLinks(),
			'#cache'    => ['max-age' => 0],
			'#attached' => [
				'library' => ['singpost_toolbox/toolbox']
			]
		];
	}

	/**
	 * @return mixed
	 */
	private function _getMenuLinks(){
		$menu_name = 'singposttoolbox';

		$dbc   = Database::getConnection();
		$query = $dbc->select('menu_tree', 'mt')->fields('mt', [
			'title',
			'id',
			'route_name',
			'route_parameters',
			'url',
		]);

		$query->condition('menu_name', $menu_name);
		$query->condition('enabled', 1);
		$query->leftJoin('menu_link_content', 'lc',
			'REPLACE(mt.id, CONCAT(lc.bundle, \':\'), \'\') = uuid');
		$query->addField('lc', 'id', 'menu_link_id');
		$query->orderBy('weight', 'asc');
		$links = $query->execute()->fetchAllAssoc('id', PDO::FETCH_ASSOC);

		$tools = ToolboxNodeBlock::getTools();

		foreach ($links as &$link){
			$title = PhpSerialize::decode($link['title']);

			if (is_string($title)){
				$link['title'] = $title;
			}else{
				$link['title'] = $title->render();
			}

			if ($link['route_name']){
				$route_parameters = [];

				if (!empty($link['route_parameters'])){
					$route_parameters = PhpSerialize::decode($link['route_parameters']);
				}
				$link['link'] = Url::fromRoute($link['route_name'], $route_parameters)->toString();
			}elseif ($link['url']){
				$link['link'] = Url::fromUri($link['url'])->toString();
			}

			if (!empty($tools[$link['id']]['form_icon'])){
				$link['icon'] = $tools[$link['id']]['form_icon'];
			}

			switch ($link['id']){
				case 'singpost.toolbox.find_postal_code.index':
					$controller   = new FindPostalCodeController();
					$link['form'] = $controller->buildForm('side');

					break;

				case 'singpost.toolbox.redirect_redeliver.index':
					$form         = Drupal::formBuilder()
					                      ->getForm(RedirectRedeliverSideForm::class);
					$link['form'] = Drupal::service('renderer')->render($form);

					break;

				case 'singpost.toolbox.calculate_postage.index':
					$calculate    = new CalculateController();
					$link['form'] = $calculate->buildSideForm();

					break;

				default:
					if (!empty($tools[$link['id']]['form_class']) && class_exists($tools[$link['id']]['form_class'])){
						$form = Drupal::formBuilder()
						              ->getForm($tools[$link['id']]['form_class'], 'side');

						$link['form'] = Drupal::service('renderer')->render($form);
					}

					break;
			}
		}

		return $links;
	}
}