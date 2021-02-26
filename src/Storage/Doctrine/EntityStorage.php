<?php

namespace Lemo\Grid\Storage\Doctrine;

use ArrayIterator;
use Doctrine\ORM\EntityManagerInterface;
use Lemo\Grid\Exception\InvalidArgumentException;
use Lemo\Grid\Storage\StorageInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Json;

class EntityStorage implements StorageInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;

    /**
     * Entity instance
     *
     * @var object
     */
    protected $entity;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Name of entity
     *
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $fieldNameIdentity;

    /**
     * @var string
     */
    protected $fieldNameKey;

    /**
     * @var string
     */
    protected $fieldNameValue;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  AuthenticationServiceInterface $authenticationService
     * @param  EntityManagerInterface         $entityManager
     * @param  string                         $entityName
     * @param  string                         $fieldNameKey
     * @param  string                         $fieldNameValue
     * @param  string                         $fieldNameIdentity
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        EntityManagerInterface $entityManager,
        $entityName,
        $fieldNameKey,
        $fieldNameValue,
        $fieldNameIdentity = null
    ) {
        $this->authenticationService = $authenticationService;
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
        $this->fieldNameIdentity = $fieldNameIdentity;
        $this->fieldNameKey = $fieldNameKey;
        $this->fieldNameValue = $fieldNameValue;

        $this->checkEntity();
    }

    /**
     * Defined by Laminas\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return bool
     */
    public function isEmpty($key)
    {
        return empty($this->getEntity($key)->{'get' . ucfirst($this->fieldNameValue)}());
    }

    /**
     * Defined by Laminas\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return ArrayIterator
     */
    public function read($key)
    {
        $content = $this->getEntity($key)->{'get' . ucfirst($this->fieldNameValue)}();

        if (!empty($content)) {
            $content = Json\Decoder::decode($content, Json\Json::TYPE_ARRAY);

            // Read class name and remove it from array
            $className = $content['__className'];
            unset($content['__className']);

            $content = new $className($content);
        }

        return $content;
    }

    /**
     * Defined by Laminas\Authentication\Storage\StorageInterface
     *
     * @param  string        $key
     * @param  ArrayIterator $content
     * @return EntityStorage
     */
    public function write($key, $content)
    {
        // Encode content as JSON
        $content = Json\Encoder::encode($content, true);

        $entity = $this->getEntity($key);
        $entity->{'set' . ucfirst($this->fieldNameKey)}($key);
        $entity->{'set' . ucfirst($this->fieldNameValue)}($content);

        // Put current entity to property
        $this->entity = $entity;

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Defined by Laminas\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return EntityStorage
     */
    public function clear($key)
    {
        $entity = $this->getEntity($key);

        // Clear current entity in property
        $this->entity = null;

        $this->entityManager->remove($entity);
        $this->entityManager->flush($entity);

        return $this;
    }

    /**
     * @param  string $key
     * @return object
     */
    protected function getEntity($key)
    {
        if (null === $this->entity) {

            // Preprare createria for findOne method
            $criteria = [$this->fieldNameKey => $key];
            if (null !== $this->fieldNameIdentity) {

                // Load identity entity
                $entityIdentity = $this->authenticationService
                    ->getIdentity();

                // Load identity entity identifiers
                $entityIdentityIdentifiers = $this->entityManager
                    ->getClassMetadata(get_class($entityIdentity))
                    ->getIdentifierValues($entityIdentity);

                $criteria[$this->fieldNameIdentity] = $entityIdentityIdentifiers;
            }

            // Try load entity form DB
            $this->entity = $this->entityManager->getRepository($this->entityName)->findOneBy($criteria);

            // No entity found, create new instance
            if (null === $this->entity) {
                $this->entity = new $this->entityName();
            }

            // Put identity entity to current entity
            if (null !== $this->fieldNameIdentity) {
                $entityIdentity = $this->entityManager->getRepository(get_class($entityIdentity))
                    ->findOneBy($entityIdentityIdentifiers);

                $this->entity->{'set' . ucfirst($this->fieldNameIdentity)}($entityIdentity);
            }
        }

        return $this->entity;
    }

    /**
     * Check if entity exist and contains required fields
     *
     * @return EntityStorage
     */
    protected function checkEntity()
    {
        if (!class_exists($this->entityName)) {
            throw new InvalidArgumentException(sprintf("Entity '{$this->entityName}' was not found"));
        }

        if (!method_exists($this->entityName, 'set' . ucfirst($this->fieldNameKey))) {
            throw new InvalidArgumentException(sprintf("Entity '%s' does not have field '%s'", $this->entityName, $this->fieldNameKey));
        }

        if (!method_exists($this->entityName, 'set' . ucfirst($this->fieldNameValue))) {
            throw new InvalidArgumentException(sprintf("Entity '%s' does not have field '%s'", $this->entityName, $this->fieldNameValue));
        }

        if (null !== $this->fieldNameIdentity && !method_exists($this->entityName, 'set' . ucfirst($this->fieldNameIdentity))) {
            throw new InvalidArgumentException(sprintf("Entity '%s' does not have field '%s'", $this->entityName, $this->fieldNameIdentity));
        }

        return $this;
    }
}
