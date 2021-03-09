<?php

namespace Drupal\insignregtest\Controller;

/**
 * @file
 * Contains \Drupal\insignregtest\Controller\AdminController.
 */

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Controller\ControllerBase;

use Drupal\insignregtest\Controller\InsignCodesRepositary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * @package Drupal\insignregtest\Controller
 */
class AdminController extends ControllerBase
{

    /**
     * Our database repository service.
     *
     * @var \Drupal\insignregtest\Controller\InsignCodesRepositary
     */
    protected $repo;

    /**
     * Renderer service will be used via Dependency Injection.
     *
     * @var Drupal\Core\Render\Renderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
      $container->get('code_repositary'),
      $container->get('renderer')
    );
    }

    /**
     * AdminController constructor.
     *
     * @param \Drupal\insignregtest\insignregtestStorage $storage
     *   Request stack service for the container.
     * @param Drupal\Core\Render\Renderer $renderer
     *   Renderer service for the container.
     */
    public function __construct(InsignCodesRepositary $repo, Renderer $renderer)
    {
        $this->repo= $repo;
        $this->renderer = $renderer;
    }


    /**
     * Get data as content table.
     *
     * @return array
     *   Content table.
     */
    public function content()
    {
      
        // Add link top
        $url = Url::fromRoute('codes_add');
        $export_url = Url::fromRoute('codes_export');
        $class[] = 'button button-action button--primary button--small';
        $add_link = Link::fromTextAndUrl($this->t('Add Code'), $url);
        $add_link = $add_link->toRenderable();
        $add_link['#attributes'] = ['class' => $class];
        $add_link = render($add_link);

        $top_links = '<p>' . $add_link. ' | ' . Link::fromTextAndUrl($this->t('Export Codes'), $export_url)->toString() . '</p>';
 
        $text = [
      '#type' => 'markup',
      '#markup' => $top_links,
    ];

        // Export link footer
  
        $export_link = '<p>' . Link::fromTextAndUrl($this->t('Export Codes'), $export_url)->toString() . '</p>';

        $footer = [
      '#type' => 'markup',
      '#markup' =>  $export_link,
    ];

        // Table header.
        $header = [
      'id' => $this->t('Id'),
      'code' => $this->t('Code'),
      'user' => $this->t('User'),
      'edit' => '',
      'delete' => '',
    ];
        $rows = [];
        foreach ($this->repo->getAll() as $content) {
            // Row with attributes on the row and some of its cells.
            $editUrl = Url::fromRoute('codes_edit', ['id' => $content->id]);
            $deleteUrl = Url::fromRoute('codes_delete', ['id' => $content->id]);

            //If uid get user details
            $user_name ='';
            $user_link='';
            if ($content->uid) {
                $user = $this->repo->getUser($content->uid);
                if ($user) {
                 
                    $user_name = $user['name'];
                    $userUrl = Url::fromRoute('entity.user.canonical', ['user' => $content->uid]);
                    $user_link = Link::fromTextAndUrl($user['name'], $userUrl)->toString();
                }
            }

            $rows[] = [
        'data' => [
          Link::fromTextAndUrl($content->id, $editUrl)->toString(),
          $content->code,
          $user_link,
          Link::fromTextAndUrl($this->t('Edit'), $editUrl)->toString(),
          $user_name ? '': Link::fromTextAndUrl($this->t('Delete'), $deleteUrl)->toString(),
        ],
      ];
        }
        $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => [
        'id' => 'codes-table',
      ],
    ];
 
        return [
      $text,
      $table,
      $footer
    ];
    }


   /**
     * Export the data to CSV
     *
     * @return array
     *
     */
    public function export()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv; utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename = insigncodes.csv');

        $output = "ID, Code, User \n";
 
        foreach ($this->repo->getAll() as $content) {
            $user_name ='';
            if ($content->uid) {
                $user = $this->repo->getUser($content->uid);
                if ($user) {
                    $user_name = $user['name'];
                }
            }
            $output .= $content->id.",".$content->code.",".$user_name." \n";

        }

        $response->setContent(render($output));
        $response->send();

        exit();

    }
}
