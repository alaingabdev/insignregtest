<?php

namespace Drupal\insignregtest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class InsignCodesRepositary.
 *
 * @package Drupal\insignregtest
 */
class InsignCodesRepositary extends ControllerBase
{
    use MessengerTrait;
    use StringTranslationTrait;

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $database;

    /**
     * Construct a repository object.
     *
     * @param \Drupal\Core\Database\Connection $connection
     *   The database connection.
     * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
     *   The translation service.
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger service.
     */
    public function __construct(Connection $database, TranslationInterface $translation, MessengerInterface $messenger)
    {
        $this->database = $database;
        $this->setStringTranslation($translation);
        $this->setMessenger($messenger);
    }

    /**
     * Method getAll().
     *
     * @return mixed
     *   DB query.
     */
    public function getAll()
    {
        $result = $this->database->select('insigncodes', 's')
      ->fields('s')
      ->execute();
        return $result;
    }

    /**
     * Get if $id exists.
     *
     * @param string $id
     *   Id of the record.
     *
     * @return bool
     *   Execute get($id) method and return bool.
     */
    public function exists($id)
    {
        return (bool) $this->get($id);
    }

    /**
     * Getter of DB Codes data.
     *
     * @param string $id
     *   Id of the record.
     *
     * @return bool|array
     *   DB query.
     */
    public function get($id)
    {
        $result = $this->database->query('SELECT * FROM {insigncodes} WHERE id = :id', [':id' => $id])
      ->fetchAllAssoc('id');
        if ($result) {
            return $result[$id];
        } else {
            return false;
        }
    }

    /**
     * Getter of User Data
     *
     * @param int $uid
     *   Id of the user.
     *
     * @return bool|array
     *   DB query.
     */
    public function getUser($uid)
    {
        $result = $this->database->query('SELECT * FROM {users_field_data} WHERE uid = :uid', [':uid' => $uid])
      ->fetchAllAssoc('uid');
        if ($result) {
            return $result[$uid];
        } else {
            return false;
        }
    }

     /**
     * Method check if code already saved
     *
     * @param string $code
     *   Id of the user.
     *
     * @return bool|array
     *   DB query.
     */
    public function getDuplicatedCode($code)
    {
      $result = $this->database->query('SELECT * FROM {insigncodes} WHERE code = :code', [':code' => $code])
      ->fetchAllAssoc('code');
        if ($result) {
            return $result[$code];
        } else {
            return false;
        }
    }


    /**
     * Add method.
     *
     * @param string $code
     *   Code.
     *
     * @throws \Exception
     *   DB insert query.
     *
     * @return int|null
     *   DB insert query return value.
     */
    public function add($code)
    {
        $fields = [
      'code' => $code
    ];
        $return_value = null;
        try {
            $return_value = $this->database->insert('insigncodes')
        ->fields($fields)
        ->execute();
        } catch (\Exception $e) {
            $this->messenger()->addMessage($this->t('Insert failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]), 'error');
        }
        return $return_value;
    }

    /**
     * Edit method.
     *
     * @param int $id
     *   Code id.
     * @param string $code
     *   Code.
     */
    public function edit($id, $code)
    {
        $fields = [
      'code' => $code
    ];
        $this->database->update('insigncodes')
      ->fields($fields)
      ->condition('id', $id)
      ->execute();
    }

    /**
     * Delete method.
     *
     * @param int $id
     *   DB delete query.
     */
    public function delete($id)
    {
        $this->database->delete('insigncodes')
      ->condition('id', $id)
      ->execute();
    }
}
