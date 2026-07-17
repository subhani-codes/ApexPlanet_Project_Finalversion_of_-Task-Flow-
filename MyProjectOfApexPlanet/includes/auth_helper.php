<?php
/**
 * ============================================================
 * TaskFlow Authentication & Security Helper
 * ============================================================
 * Handles:
 * - Session Management
 * - Login Protection
 * - Role Based Access Control
 * - Input Sanitization
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_start();
}

/*
|--------------------------------------------------------------------------
| Sanitize User Input
|--------------------------------------------------------------------------
*/

function sanitize_input($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Login Protection
|--------------------------------------------------------------------------
*/

function confirm_authenticated()
{
    if (!isset($_SESSION['user_id'])) {

        session_unset();
        session_destroy();

        header("Location: /myProjectOfApexPlanet/login.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Alias (optional)
|--------------------------------------------------------------------------
*/

function require_login()
{
    confirm_authenticated();
}

/*
|--------------------------------------------------------------------------
| Role Based Access Control
|--------------------------------------------------------------------------
*/

function enforce_permission(array $allowed_roles)
{
    confirm_authenticated();

    $role = $_SESSION['role'] ?? 'user';

    if (!in_array($role, $allowed_roles)) {

        http_response_code(403);

        require_once __DIR__ . '/../errors/403.php';

        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Single Role Protection
|--------------------------------------------------------------------------
*/

function require_role($role)
{
    enforce_permission([$role]);
}