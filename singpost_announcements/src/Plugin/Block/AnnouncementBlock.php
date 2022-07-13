<?php

namespace Drupal\singpost_announcements\Plugin\Block;

use Drupal\singpost_announcements\Frontend\Form\AnnouncementListSlider;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\singpost_announcements\Model\Announcement;
use Drupal\singpost_announcements\Form\AnnouncementTableForm;
use Drupal\singpost_announcements\Form\AnnouncementSearchForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a block with a SingPost Announcements.
 *
 * @Block(
 *   id = "singpost_announcements",
 *   admin_label = @Translation("Singpost Announcements"),
 * )
 */
class AnnouncementBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
		$title = $this->configuration['label_display'] ? $this->configuration['label'] : '';

		return [
			'#theme'             => 'singpost_announcements_bock',
			'#html'              => $this->configuration['html']['value'],
			'#block_title'       => $title,
			'#block_class'       => $this->configuration['block_class'],
			'#result'  			 => $this->getresult(),
			'#block_inner_class' => $this->configuration['block_inner_class']
		];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  private function getresult(){
		$allannouncementArr = Announcement::getAllAnnoucement();
		$resultpage = $allannouncementArr;
		return $resultpage;
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
    $this->configuration['singpost_announcements_settings'] = $form_state->getValue('singpost_announcements_settings');
  }
}