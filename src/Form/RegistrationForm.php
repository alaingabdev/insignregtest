<?php

namespace Drupal\insignregtest\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class insignregtestForm.
 *
 * @package Drupal\insignregtest\Form
 */
class RegistrationForm extends FormBase
{
    public function getFormId()
    {
        return 'insignregtest_form';
    }


    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['username'] = array(
      '#type' => 'textfield',
      '#title' => t('Username:'),
      '#required' => true,
      '#default_value' => '',
      );


        $form['email'] = array(
      '#type' => 'email',
      '#title' => t('Email:'),
      '#required' => true,
      '#default_value' => '',
      );

      $form['password'] = array(
        '#type' => 'password',
        '#title' => t('Password:'),
        '#required' => true,
        '#default_value' => '',
        );

        $form['code'] = array(
      '#type' => 'textfield',
      '#title' => t('Code'),
      '#required' => true,
      '#default_value' => '',
       );

        $form['submit'] = [
        '#type' => 'submit',

        '#value' => t('Submit'),
    ];

        return $form;
    }

  
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $conn = Database::getConnection();

        $email = $form_state->getValue('email');
        $password = $form_state->getValue('password');
        $code = $form_state->getValue('code');

     

        /**
         * Check username is not used
         */
  
        $query = $conn->select('users_field_data', 'u')
        ->condition('name', $form_state->getValue('username'))
        ->fields('u');
        $user_check = $query->execute()->fetchAssoc();

        if ($user_check) {
            $form_state->setErrorByName('username', $this->t('The username is already used'));
        }

        /**
         *  Validate email
         */

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_state->setErrorByName('email', $this->t('The email is invalid'));
        }
       
    
        /**
         * Check email is not used
         */

        $query = $conn->select('users_field_data', 'u')
            ->condition('mail', $email)
            ->fields('u');
        $user_check = $query->execute()->fetchAssoc();

        if ($user_check) {
            $form_state->setErrorByName('email', $this->t('The email is already used'));
        }

         /**
         * Validate password
         */

        if (strlen($password) < 8) {
            $form_state->setErrorByName('password', $this->t('The password must 8 chars minimum'));
        }

        /**
         * Validate the code
         */

        if ($code != "42BDF75C3561INSDDIGN896FF8E85032") {


         /**
         * Check is code in DB and not apready used uid not NULL
         */
        $conn = Database::getConnection();
            $query = $conn->select('insigncodes', 'c')
        ->condition('code', $code)
        ->fields('c');
            $code_check = $query->execute()->fetchAssoc();

            if ($code_check) {
                if ($code_check['uid']) {
                    $form_state->setErrorByName('code', $this->t('The code is already used'));
                }
            } else {
                $form_state->setErrorByName('code', $this->t('The code is invalid'));
            }
        }

        parent::validateForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $field=$form_state->getValues();
        $username=$field['username'];
        $email=$field['email'];
        $code=$field['code'];
        $pass = $field['password'];
        
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();
        
        $user->setPassword($pass);
        $user->enforceIsNew();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->activate();
        $result = $user->save();

        $uid = $user->id();

        /**
         * Update code table with new uer id
         */
        $conn = Database::getConnection();
        $query = $conn->update('insigncodes')
        ->fields([
          'uid' => $uid
        ])
        ->condition('code', $code)
        ->execute();


        /**
        * Login the user
        */
        $uid = \Drupal::service('user.auth')->authenticate($username, $pass);
        $user = \Drupal\user\Entity\User::load($uid);
        user_login_finalize($user);

        //_user_mail_notify('register_no_approval_required', $user);

        drupal_set_message("You account has been created");
        $form_state->setRedirect('user.page');
    }
}
