<?php

  namespace Drupal\insignregtest\Routing;


  use Drupal\Core\Routing\RouteSubscriberBase;

  use Symfony\Component\Routing\RouteCollection;


  /**
   * Listens to the dynamic route events.
   */


  class RouteSubscriber extends RouteSubscriberBase {


   /**
    * Change the default registration page route to insignregtest module registration page
    *
    * @param RouteCollection $collection
    * @return void
    */
    protected function alterRoutes(RouteCollection $collection) {
     
      // register form
      if ($route = $collection->get('user.register')) {
            $route->setPath('/insignreg');
            }

    }
  }

