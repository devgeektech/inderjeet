<?php

namespace Drupal\singpost_sgx_announcements\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\singpost_sgx_announcements\Model\SgxAnnouncement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a block with a SingPost SGX Announcements.
 *
 * @Block(
 *   id = "singpost_sgx_announcements",
 *   admin_label = @Translation("Singpost SGX Announcements New"),
 * )
 */
class SgxAnnouncementBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */

  public function build() {
		$title = $this->configuration['label_display'] ? $this->configuration['label'] : '';
		return [
			'#theme'             => 'singpost_sgx_announcements_block',
			'#block_title'       => $title,
			'#result'  			     => $this->getresult()
		];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  private function getresult(){

    $sgx = SgxAnnouncement::find(['title', 'date', 'file'])
    ->where('published', [SgxAnnouncement::ACTIVE])
    ->orderBy('date', 'desc')
    ->limit(6)
    ->all();
    if ($sgx && !empty($sgx)){
			$list_sgx = [];
			foreach ($sgx as $value){
				$list_sgx[] = [
					'date'  => date('d M Y', $value->date),
					'title' => $value->title,
					'url'   => $value->file ? (File::load($value->file)
					                               ->createFileUrl()) : ''
				];
			}
		}
    return $list_sgx;
  }
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['singpost_sgx_announcements_settings'] = $form_state->getValue('singpost_sgx_announcements_settings');
  }
}