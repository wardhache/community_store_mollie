<?php
namespace Concrete\Package\CommunityStoreMollie\Src\Mollie\Order;

use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="molStoreOrderTransactions")
 */
class Transaction
{
  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $tID;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   */
  protected $oID;

  /**
   * @var int
   *
   * @ORM\Column(type="string")
   */
  protected $pID;

  /**
   * @var Order
   *
   * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order", cascade={"persist"})
   * @ORM\JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
   */
  protected $order;

  /** @var string */
  protected static $table = 'molStoreOrderTransactions';

  public static function getTable(): string
  {
    return self::$table;
  }

  public function setOrder(Order $order): self
  {
    $this->order = $order;

    return $this;
  }

  public function setMolliePaymentID(string $molliePaymentID): self
  {
    $this->pID = $molliePaymentID;

    return $this;
  }

  public function getID(): int
  {
    return $this->tID;
  }

  public function getOrder(): Order
  {
    return $this->order;
  }

  public function getMolliePaymentID(): string
  {
    return $this->pID;
  }

  public function save(): void
  {
    $em = databaseORM::entityManager();
    $em->persist($this);
    $em->flush();
  }

  public function delete(): void
  {
    $em = databaseORM::entityManager();
    $em->remove($this);
    $em->flush();
  }

  public static function add(Order $order, string $molliePaymentID): self
  {
    $entity = new self();

    $entity
      ->setOrder($order)
      ->setMolliePaymentID($molliePaymentID)
      ->save();

    return $entity;
  }

  public function update(Order $order, string $molliePaymentID): self
  {
    $this
      ->setOrder($order)
      ->setMolliePaymentID($molliePaymentID)
      ->save();

    return $this;
  }

  public static function getByID(int $tID): self
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findOneBy(['tID' => $tID], []);
  }

  public static function getByOrder(Order $order): self
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findOneBy(['order' => $order], []);
  }

  public static function getByMolliePaymentID(int $molliePaymentID): self
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findOneBy(['pID' => $molliePaymentID], []);
  }

  public static function getAll(): array
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findBy([], []);
  }
}
