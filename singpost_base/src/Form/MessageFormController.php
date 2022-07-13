<?php

/**
 * @file
 * Contains \Drupal\singpost_base\Form\MessageFormController.
 */

namespace Drupal\singpost_base\Form;

use Drupal\simplenews\Form\SubscriptionsFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;


/**
 * Configure simplenews subscriptions of the logged user.
 */
 abstract class MessageFormController extends SubscriptionsFormBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(MessengerInterface $messenger = NULL) {
    $this->messenger = $messenger;
  }

  public static function setMessage(array &$form, FormStateInterface $form_state) {
   

   
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => \Drupal::messenger()->all(),
    ];
 
    // Render Messages 
    $messages = \Drupal::service('renderer')->render($message);
    $status = $messages;

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.simplenews-result-message', // Mention the message that you want to print
         t('@result', ['@result' => $status]) 
       ),
    );
    \Drupal::messenger()->deleteAll();
    //$response->addCommand(new ReplaceCommand('#simplenews-subscriptions-block', $form));
    
    // Replace the form:
    //$response->addCommand(new ReplaceCommand('#subscribe-wrapper', $form));

    // Response
    return $response;
  }

 
}