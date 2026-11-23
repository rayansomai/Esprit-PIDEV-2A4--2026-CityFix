<?php
/**
 * Models/PermissionModel.php
 * Entité « permission » (ex : users.view). reserveAdmin = droit non attribuable
 * aux autres rôles que l'administrateur.
 */

declare(strict_types=1);

class PermissionModel
{
    private int $id = 0;
    private string $cle = '';
    private string $libelle = '';
    private bool $reserveAdmin = false;

    public function __construct()
    {
    }

    public function getId(): int { return $this->id; }
    public function getCle(): string { return $this->cle; }
    public function getLibelle(): string { return $this->libelle; }
    public function isReserveAdmin(): bool { return $this->reserveAdmin; }

    public function setId(int $id): void { $this->id = $id; }
    public function setCle(string $cle): void { $this->cle = $cle; }
    public function setLibelle(string $libelle): void { $this->libelle = $libelle; }
    public function setReserveAdmin(bool $reserveAdmin): void { $this->reserveAdmin = $reserveAdmin; }

    public static function fromArray(array $row): self
    {
        $p = new self();
        $p->setId((int) ($row['id'] ?? 0));
        $p->setCle((string) ($row['cle'] ?? ''));
        $p->setLibelle((string) ($row['libelle'] ?? ''));
        $p->setReserveAdmin((bool) ($row['reserve_admin'] ?? false));
        return $p;
    }
}
