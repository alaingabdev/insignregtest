<?php

namespace Drupal\insignregtest\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\insignregtest\Controller\InsignCodesRepositary;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeleteForm.
 *
 * @package Drupal\insignregtest
 */
class DeleteForm extends ConfirmFormBase {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Our database repository service.
   *
   * @var \Drupal\insignregtest\Controller\InsignCodesRepositary 
   */
  protected $repo;

  protected $requestStack;

  protected $id;

 /**
  * Undocumented function
  *
  * @param ContainerInterface $container
  *
  */
  public static function create(ContainerInterface $container) {
    $form = new static(
      $container->get('code_repositary'),
      $container->get('request_stack')
    );
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }

  /**
   * AdminController constructor.
   *
   * @var \Drupal\insignregtest\Controller\InsignCodesRepositary $repo
   *   Repositary for the insigncode table
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service for the container.
   */
  public function __construct(InsignCodesRepositary $repo, RequestStack $request_stack) {
    $this->repo = $repo;
    $this->requestStack = $request_stack;
  }


  protected function request() {
    return $this->requestStack->getCurrentRequest();
  }


  public function getFormId() {
    return 'codes_delete';
  }


  public function getQuestion() {
    return $this->t('Are you sure you want to delete code %id ?',
      ['%id' => $this->id]);
  }


  public function getConfirmText() {
    return $this->t('Delete');
  }


  public function getCancelUrl() {
    return new Url('codes_list');
  }


  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->id = $this->request()->get('id');
    return parent::buildForm($form, $form_state);
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($this->id)) {
      $return = $this->repo->delete($this->id);
      if ($return) {
        $this->messenger()->addMessage($this->t('Code has been removed.'));
      }
    }
    $form_state->setRedirect('codes_list');
  }

}
