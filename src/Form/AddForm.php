<?php

namespace Drupal\insignregtest\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\insignregtest\Controller\InsignCodesRepositary;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class AddForm.
 *
 * @package Drupal\insignregtest
 */
class AddForm extends FormBase implements FormInterface, ContainerInjectionInterface
{
    use StringTranslationTrait;
    use MessengerTrait;

    /**
     * Our database repository service.
     *
     * @var \Drupal\insignregtest\Controller\InsignCodesRepositary
     */
    protected $repo;

    /**
     * The current user.
     *
     * We'll need this service in order to check if the user is logged in.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $requestStack;

    /**
     * {@inheritdoc}
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        $form = new static(
      $container->get('code_repositary'),
      $container->get('current_user'),
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
     *   Request stack service for the container.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   Request stack service for the container.
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     *   Request stack service for the container.
     */
    public function __construct(InsignCodesRepositary $repo, AccountProxyInterface $current_user, RequestStack $request_stack)
    {
        $this->repo = $repo;
        $this->currentUser = $current_user;
        $this->requestStack = $request_stack;
    }

  
    protected function request()
    {
        return $this->requestStack->getCurrentRequest();
    }


    public function getFormId()
    {
        return 'codes_add';
    }


    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $this->id = $this->request()->get('id');
        $codes = $this->repo->get($this->id);

        $form['code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Code'),
      '#default_value' => $codes ? $codes->code : '',
      '#required' => true,
    ];
   
        $form['actions'] = [
      '#type' => 'actions',
    ];
        $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $codes ? $this->t('Edit') : $this->t('Add'),
    ];

        $form['actions']['submit']['#attributes']['class'][] = 'button button--primary js-form-submit form-submit';

        return $form;
    }

  
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Verify that the user is logged-in.
        if ($this->currentUser->isAnonymous()) {
            $form_state->setError($form['add'], $this->t('You must be logged in to add values to the database.'));
        }


        $code = $form_state->getValue('code');

        if (!preg_match("/^[a-zA-Z0-9]+$/", $code)) {
            $form_state->setErrorByName('code', $this->t('The code must contains only letters or numbers'));
        }
        if (strlen($code) < 15) {
            $form_state->setErrorByName('code', $this->t('The code must 15 chars minimum'));
        }

        $duplicated_code = $this->repo->getDuplicatedCode($code);

        if ($duplicated_code) {
            $form_state->setErrorByName('code', $this->t('The code already exist'));
        }

        parent::validateForm($form, $form_state);
    }

  
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $code = $form_state->getValue('code');

        if (!empty($this->id)) {
            $return = $this->repo->edit($this->id, Html::escape($code));
            if ($return) {
                $this->messenger()->addMessage($this->t('Code has been edited.'));
            }
        } else {
            $return = $this->repo->add(Html::escape($code));
            if ($return) {
                $this->messenger()->addMessage($this->t('Code has been saved.'));
            }
        }
        $form_state->setRedirect('codes_list');
    }
}
