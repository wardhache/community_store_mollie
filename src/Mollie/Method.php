<?php
namespace Concrete\Package\CommunityStoreMollie\Src\Mollie;

use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Database;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM;
use Mollie\Api\MollieApiClient;

/**
 * @ORM\Entity
 * @ORM\Table(name="molStoreMethods")
 */
class Method
{
  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $pID;

  /**
   * @var string
   *
   * @ORM\Column(type="string")
   */
  protected $pMollieID;

  /**
   * @var string
   *
   * @ORM\Column(type="string")
   */
  protected $pTitle;

  /**
   * @var string
   *
   * @ORM\Column(type="string")
   */
  protected $pImage;

  /**
   * @var float
   *
   * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
   */
  protected $pMinimum;

  /**
   * @var float
   *
   * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
   */
  protected $pMaximum;

  /** @var string */
  protected static $table = 'molStoreMethods';

  public static function getTable(): string
  {
    return self::$table;
  }

  public function setMollieID(string $pMollieID): self
  {
    $this->pMollieID = $pMollieID;

    return $this;
  }

  public function setTitle(string $pTitle): self
  {
    $this->pTitle = $pTitle;

    return $this;
  }

  public function setImage(string $pImage): self
  {
    $this->pImage = $pImage;

    return $this;
  }

  public function setMinimum(?float $pMinimum): self
  {
    $this->pMinimum = $pMinimum;

    return $this;
  }

  public function setMaximum(?float $pMaximum): self
  {
    $this->pMaximum = $pMaximum;

    return $this;
  }

  public function getID(): int
  {
    return $this->pID;
  }

  public function getMollieID(): string
  {
    return $this->pMollieID;
  }

  public function getTitle(): string
  {
    return $this->pTitle;
  }

  public function getImage(): string
  {
    return $this->pImage;
  }

  /** @return float|null */
  public function getMinimum()
  {
    return $this->pMinimum;
  }

  /** @return float|null */
  public function getMaximum(): ?float
  {
    return $this->pMaximum;
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

  public static function rescan()
  {
    $em = Database::connection()->getEntityManager();
    $em->createQueryBuilder()->delete(self::class)->getQuery()->execute();

    $mollie = new MollieApiClient();
    $mollie->setApiKey(Config::get('community_store.mollie.api_key'));
    $paymentMethods = $mollie->methods->allActive();

    foreach ($paymentMethods as $paymentMethod) {
      $method = new self();
      $method->setMollieID($paymentMethod->id);
      $method->setTitle($paymentMethod->description);
      $method->setImage($paymentMethod->image->size1x);

      if ($minimum = $paymentMethod->minimumAmount->value) {
        $method->setMinimum((float) $minimum);
      }

      if ($maximum = $paymentMethod->maximumAmount->value) {
        $method->setMaximum((float) $maximum);
      }

      $method->save();
    }
  }

  public static function getByID($pID)
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findOneBy(['pID' => $pID], []);
  }

  public static function getByMollieID($pMollieID)
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findOneBy(['pMollieID' => $pMollieID], []);
  }

  public static function getAll()
  {
    $em = databaseORM::entityManager();

    return $em->getRepository(get_class())->findBy([], []);
  }
}
