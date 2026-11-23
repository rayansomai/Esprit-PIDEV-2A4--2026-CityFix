<?php
/**
 * Views/includes/form_helpers.php
 * Petites fonctions d'affichage des erreurs de formulaire (aucune logique métier) :
 * elles évitent de répéter le même HTML dans chaque vue.
 */

declare(strict_types=1);

if (!function_exists('invalid_class')) {
    /** Retourne « is-invalid » si le champ a une erreur. */
    function invalid_class(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }

    /** Attributs ARIA d'un champ : aria-invalid + lien vers son message d'erreur. */
    function aria_error(array $errors, string $field, string $id): string
    {
        return isset($errors[$field])
            ? 'aria-invalid="true" aria-describedby="' . e($id) . '-error"'
            : 'aria-describedby="' . e($id) . '-error"';
    }

    /** Classe CSS du badge d'un rôle. */
    function role_badge_class(string $roleNom): string
    {
        return ['citoyen' => 'cf-badge--citoyen', 'agent' => 'cf-badge--agent', 'admin' => 'cf-badge--admin'][$roleNom] ?? 'cf-badge--neutral';
    }

    /** Date au format français (jj/mm/aaaa, avec l'heure si demandé). */
    function fr_date(string $datetime, bool $withTime = false): string
    {
        $ts = strtotime($datetime);
        if ($ts === false) {
            return '';
        }
        return date($withTime ? 'd/m/Y à H:i' : 'd/m/Y', $ts);
    }

    /** Message d'erreur sous le champ (zone toujours présente pour la validation JavaScript). */
    function field_error(array $errors, string $field, string $id): string
    {
        return '<span class="cf-field-error" id="' . e($id) . '-error"' . (isset($errors[$field]) ? '' : ' hidden') . '>'
             . e($errors[$field] ?? '') . '</span>';
    }
}
