<?php
/**
 * Models/UserModel.php
 * Entité « utilisateur » (workshop, Partie 3.1) : attributs privés, getters/setters
 * et fromArray(). Aucune requête SQL ici : l'accès aux données est dans les contrôleurs.
 */

declare(strict_types=1);

class UserModel
{
    // ------------------------------------------------------------
    // ATTRIBUTES
    // ------------------------------------------------------------
    private int $id = 0;
    private int $roleId = 0;
    private string $roleNom = '';       // citoyen | agent | admin (issu de la jointure avec roles)
    private string $roleLibelle = '';
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private ?string $telephone = null;
    private string $password = '';
    private string $statut = 'actif';
    private string $createdAt = '';
    private string $updatedAt = '';

    public function __construct()
    {
    }

    // ------------------------------------------------------------
    // GETTERS
    // ------------------------------------------------------------
    public function getId(): int { return $this->id; }
    public function getRoleId(): int { return $this->roleId; }
    public function getRoleNom(): string { return $this->roleNom; }
    public function getRoleLibelle(): string { return $this->roleLibelle; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getPassword(): string { return $this->password; }
    public function getStatut(): string { return $this->statut; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    /** Nom complet affiché (Prénom Nom). */
    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    /** Initiales pour l'avatar (ex : « AT »). */
    public function getInitiales(): string
    {
        $p = mb_substr($this->prenom, 0, 1, 'UTF-8');
        $n = mb_substr($this->nom, 0, 1, 'UTF-8');
        return mb_strtoupper($p . $n, 'UTF-8');
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }

    // ------------------------------------------------------------
    // SETTERS
    // ------------------------------------------------------------
    public function setId(int $id): void { $this->id = $id; }
    public function setRoleId(int $roleId): void { $this->roleId = $roleId; }
    public function setRoleNom(string $roleNom): void { $this->roleNom = $roleNom; }
    public function setRoleLibelle(string $roleLibelle): void { $this->roleLibelle = $roleLibelle; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(string $updatedAt): void { $this->updatedAt = $updatedAt; }

    // ------------------------------------------------------------
    // CONVERT ARRAY TO OBJECT
    // ------------------------------------------------------------
    public static function fromArray(array $row): self
    {
        $user = new self();
        $user->setId((int) ($row['id'] ?? 0));
        $user->setRoleId((int) ($row['role_id'] ?? 0));
        $user->setRoleNom((string) ($row['role_nom'] ?? ''));
        $user->setRoleLibelle((string) ($row['role_libelle'] ?? ''));
        $user->setNom((string) ($row['nom'] ?? ''));
        $user->setPrenom((string) ($row['prenom'] ?? ''));
        $user->setEmail((string) ($row['email'] ?? ''));
        $user->setTelephone(isset($row['telephone']) && $row['telephone'] !== '' ? (string) $row['telephone'] : null);
        $user->setPassword((string) ($row['password'] ?? ''));
        $user->setStatut((string) ($row['statut'] ?? 'actif'));
        $user->setCreatedAt((string) ($row['created_at'] ?? ''));
        $user->setUpdatedAt((string) ($row['updated_at'] ?? ''));
        return $user;
    }
}
