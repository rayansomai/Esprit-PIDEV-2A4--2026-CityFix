<?php
/**
 * Models/RoleModel.php
 * Entité « rôle » : citoyen, agent ou admin (rôles prédéfinis).
 */

declare(strict_types=1);

class RoleModel
{
    private int $id = 0;
    private string $nom = '';
    private string $libelle = '';
    private string $description = '';
    private int $nbUsers = 0;          // nombre d'utilisateurs (rempli par une requête COUNT)

    public function __construct()
    {
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getLibelle(): string { return $this->libelle; }
    public function getDescription(): string { return $this->description; }
    public function getNbUsers(): int { return $this->nbUsers; }

    public function setId(int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setLibelle(string $libelle): void { $this->libelle = $libelle; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setNbUsers(int $nbUsers): void { $this->nbUsers = $nbUsers; }

    public static function fromArray(array $row): self
    {
        $role = new self();
        $role->setId((int) ($row['id'] ?? 0));
        $role->setNom((string) ($row['nom'] ?? ''));
        $role->setLibelle((string) ($row['libelle'] ?? ''));
        $role->setDescription((string) ($row['description'] ?? ''));
        $role->setNbUsers((int) ($row['nb_users'] ?? 0));
        return $role;
    }
}
