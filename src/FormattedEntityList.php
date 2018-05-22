<?php
/**An Entity List with user properties*/

namespace Drupal\fancy_mail;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

class FormattedEntityList {

  /**
   * @var ContentEntityBase
   */
  protected $entity;

  /**
   * @var array|mixed[]
   */

  protected $entity_values;

  /**
   * @var User
   */
  protected $user;

  /**
   * @var array|mixed[]
   */

  protected $user_values;

  /**
   * @var $params
   */

  protected $params = [];

  public function __construct(ContentEntityInterface $entity){

    // determine if the user for the entity can be set
    if($entity instanceof EntityOwnerInterface && !$entity->getEntityType()->isRevisionable()){
      $this->user = $entity->getOwner();
    } else if($entity->getEntityType()->isRevisionable()) {
      $this->user = $entity->getRevisionUser();
    } else {
      $this->user = null;
    }

    if(!empty($this->user)) {
      $this->user_values = $this->user->toArray();
    }
    $this->entity = $entity;
    $this->entity_values = $entity->toArray();
  }
  public function getAllParams(){

    // sets the main entity parameters and it's user if it has one
    $params[$this->entity->getEntityTypeId()] = $this->processParamsEntity($this->entity);
    if($this->user !== null) {
      $params['user'] = $this->processParamsEntity($this->user);
    }

    // get it's type if it's a node
    if($this->entity instanceof NodeInterface) {
      $params['node_type'] = ucwords(str_replace("_", " ", $this->entity->getType()));
    }

    $params[$this->entity->getEntityTypeId()]['id'] = $this->entity->id();
    $this->params = $params;

    return $this->params;
  }


  /**
   * @param EntityInterface $entity
   * @return mixed
   */
  public function processParamsEntity(EntityInterface $entity) {

    if($entity instanceof UserInterface) {
      $params['user']['role'] = implode(',', $entity->getRoles());
      $params['user']['email'] = $entity->getEmail();
    } else {
      //loop through entity keys
      $entity_values = array_keys($entity->toArray());

      $params[$entity->getEntityTypeId()] = [];

      foreach ($entity_values as $key) {
        $field_item_list = $entity->get($key);
        if (strpos($key, 'field_') !== false || $key === "title" || $key === "mail" || $key === "body") {
          if ($field_item_list instanceof FieldItemListInterface) {
            $new_key = str_replace('field_', "", $key);
            // attach render array view of field_item_list (will view default if mail_list is not found)
            $params[$entity->getEntityTypeId()][$new_key] = $field_item_list->view('mail_list');
          }
        }
      }
    }

    return $params[$entity->getEntityTypeId()];
  }
}
