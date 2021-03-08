<?php

  namespace Drupal\insignregtest\EventSubscriber;

  use Symfony\Component\EventDispatcher\EventSubscriberInterface;

  use Symfony\Component\HttpFoundation\RedirectResponse;
  use Symfony\Component\HttpKernel\Event\GetResponseEvent;
  use Symfony\Component\HttpKernel\KernelEvents;

  /**
   * Listens to the request
   */

  class RequestSubscriber implements EventSubscriberInterface
  {

    /**
     * Redirect non logged user to login page but let him access the registraion  and reset password pages
     *
     * @param GetResponseEvent $event
     * @return void
     */
      public function checkForLoggedUser(GetResponseEvent $event)
      {
        


          $account = \Drupal::currentUser();
          $request = $event->getRequest();

             // dump($request->getPathInfo());
          if (empty($account->id())) {

        /* Redirect to login if not logeed and not login or reset password page */
   
              if ($request->getPathInfo() != "/user/login" && $request->getPathInfo() != "/insignreg"  && $request->getPathInfo() != "/user/register"  && $request->getPathInfo() != "/user/password") {
                 $response = new RedirectResponse('/user/login', 301);
                 $response->send();
                 exit(0);
              }
          }
      }
  
      public static function getSubscribedEvents()
      {
          $events[KernelEvents::REQUEST][] = array('checkForLoggedUser', 27);
          return $events;
      }
  }
