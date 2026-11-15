<?php

declare(strict_types=1);

namespace App\Library\Auth;

use App\Library\Database\Database;
use App\Library\Database\User;
use Illuminate\Database\UniqueConstraintViolationException;

final class Auth
{
  private const SESSION_KEY = 'user_id';

  /** Возвращает null, если email уже занят. */
  public static function register(string $email, string $password, string $name): ?User
  {
    Database::boot();
    $email = self::normalize($email);

    if (User::where('email', $email)->exists()) {
      return null;
    }

    try {
      $user = User::create([
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'name' => $name,
      ]);
    } catch (UniqueConstraintViolationException) {
      return null;
    }

    self::signIn($user);

    return $user;
  }

  /** Возвращает null при неверном email или пароле. */
  public static function login(string $email, string $password): ?User
  {
    Database::boot();
    $user = User::where('email', self::normalize($email))->first();

    if ($user === null || !password_verify($password, $user->password_hash)) {
      return null;
    }

    self::signIn($user);

    return $user;
  }

  public static function logout(): void
  {
    Session::start();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
      $cookie = session_get_cookie_params();
      setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
      ]);
    }

    session_destroy();
  }

  /** Возвращает пользователя из сессии или null. */
  public static function user(): ?User
  {
    Database::boot();
    Session::start();

    $id = $_SESSION[self::SESSION_KEY] ?? null;

    return $id === null ? null : User::find($id);
  }

  private static function signIn(User $user): void
  {
    Session::start();
    session_regenerate_id(true);
    $_SESSION[self::SESSION_KEY] = $user->user_id;
  }

  private static function normalize(string $email): string
  {
    return mb_strtolower(trim($email));
  }
}
